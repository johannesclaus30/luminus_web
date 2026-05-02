<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TracerForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TracerFormController extends Controller
{
    public function index()
    {
        return view('admin_alumni_tracer');
    }

    public function list()
    {
        try {
            // Get all non-deleted forms (status != 0)
            $forms = TracerForm::with(['questions.options'])
                ->where('status', '!=', TracerForm::STATUS_DELETED)
                ->orderByDesc('created_at')
                ->get();

            return response()->json($forms);
        } catch (\Throwable $e) {
            \Log::error('Failed to load tracer forms: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load forms',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleted()
    {
        try {
            // Get only deleted forms (status = 0)
            $forms = TracerForm::with(['questions.options'])
                ->where('status', TracerForm::STATUS_DELETED)
                ->orderByDesc('updated_at')
                ->get();

            return response()->json($forms);
        } catch (\Throwable $e) {
            \Log::error('Failed to load deleted tracer forms: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load deleted forms',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $form = TracerForm::with(['questions.options'])
                ->where('status', '!=', TracerForm::STATUS_DELETED)
                ->findOrFail($id);

            return response()->json($form);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Form not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_title' => 'required|string|max:255',
            'form_description' => 'nullable|string',
            'form_header' => 'nullable|string',
            'status' => 'integer|in:1,2,3', // 1=active, 2=draft, 3=closed
            'questions' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $form = TracerForm::create([
                'admin_id' => auth()->id() ?? 1,
                'form_title' => $validated['form_title'],
                'form_description' => $validated['form_description'] ?? null,
                'form_header' => $validated['form_header'] ?? null,
                'status' => $validated['status'] ?? TracerForm::STATUS_ACTIVE,
            ]);

            if (!empty($validated['questions'])) {
                $this->saveQuestions($form, $validated['questions']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Tracer form saved successfully.',
                'form' => $form->load('questions.options')
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Failed to save tracer form: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to save tracer form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $form = TracerForm::findOrFail($id);

        $validated = $request->validate([
            'form_title' => 'required|string|max:255',
            'form_description' => 'nullable|string',
            'form_header' => 'nullable|string',
            'status' => 'integer|in:1,2,3',
            'questions' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $form->update([
                'form_title' => $validated['form_title'],
                'form_description' => $validated['form_description'] ?? null,
                'form_header' => $validated['form_header'] ?? null,
                'status' => $validated['status'] ?? $form->status,
            ]);

            // Delete existing questions and options
            foreach ($form->questions as $question) {
                $question->options()->delete();
            }
            $form->questions()->delete();

            // Save new questions
            if (!empty($validated['questions'])) {
                $this->saveQuestions($form, $validated['questions']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Tracer form updated successfully.',
                'form' => $form->load('questions.options')
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Failed to update tracer form: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to update tracer form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $form = TracerForm::findOrFail($id);
            
            // Set status to deleted (0)
            $form->update(['status' => TracerForm::STATUS_DELETED]);

            return response()->json([
                'message' => 'Tracer form deleted successfully.',
                'form' => $form->load('questions.options'),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to delete tracer form: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to delete tracer form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $form = TracerForm::findOrFail($id);
            
            // Only restore if actually deleted
            if ($form->status !== TracerForm::STATUS_DELETED) {
                return response()->json([
                    'message' => 'Form is not deleted.',
                    'form' => $form->load('questions.options'),
                ], 400);
            }
            
            // Restore by setting status back to active (1)
            $form->update(['status' => TracerForm::STATUS_ACTIVE]);

            return response()->json([
                'message' => 'Tracer form restored successfully.',
                'form' => $form->load('questions.options'),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to restore tracer form: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to restore tracer form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        try {
            $form = TracerForm::findOrFail($id);
            $newStatus = $request->integer('status');
            
            // Validate status
            if (!in_array($newStatus, [TracerForm::STATUS_ACTIVE, TracerForm::STATUS_CLOSED, TracerForm::STATUS_DRAFT])) {
                return response()->json(['message' => 'Invalid status.'], 400);
            }
            
            $form->update(['status' => $newStatus]);

            return response()->json(['message' => 'Status updated successfully.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to update status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function saveQuestions(TracerForm $form, array $questions)
    {
        foreach ($questions as $index => $questionData) {
            $question = $form->questions()->create([
                'type' => $questionData['type'] ?? 'text',
                'question_text' => $questionData['question_text'] ?? '',
                'description' => $questionData['subtitle'] ?? null,
                'is_required' => $questionData['required'] ?? false,
                'order_priority' => $index,
                'settings' => [
                    'scale_points' => $questionData['scale_points'] ?? null,
                    'scale_labels' => $questionData['scale_labels'] ?? null,
                    'other_enabled' => $questionData['other_enabled'] ?? false,
                    'display_type' => $questionData['display_type'] ?? 'list',
                    'statements' => $questionData['statements'] ?? [],
                ],
            ]);

            if (!empty($questionData['options']) && is_array($questionData['options'])) {
                foreach ($questionData['options'] as $optIndex => $optionData) {
                    $question->options()->create([
                        'option_label' => $optionData['label'] ?? '',
                        'option_value' => $optionData['label'] ?? '',
                    ]);
                }
            }
        }
    }
}