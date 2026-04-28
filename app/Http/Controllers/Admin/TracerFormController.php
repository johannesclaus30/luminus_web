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
        $forms = TracerForm::with(['questions.options'])
            ->orderByDesc('id')
            ->get();

        return response()->json($forms);
    }

    public function deleted()
    {
        $forms = TracerForm::onlyTrashed()
            ->with(['questions.options'])
            ->orderByDesc('deleted_at')
            ->get();

        return response()->json($forms);
    }

    public function show($id)
    {
        $form = TracerForm::with(['questions.options'])
            ->findOrFail($id);

        return response()->json($form);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_title' => 'required|string|max:255',
            'form_description' => 'nullable|string',
            'form_header' => 'nullable|string',
            'is_active' => 'boolean',
            'questions' => 'array',
        ]);

        DB::beginTransaction();

        try {
            $form = TracerForm::create([
                'admin_id' => auth()->id() ?? 1,
                'form_title' => $validated['form_title'],
                'form_description' => $validated['form_description'] ?? null,
                'form_header' => $validated['form_header'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $this->saveQuestions($form, $request->input('questions', []));

            DB::commit();

            return response()->json([
                'message' => 'Tracer form saved successfully.',
                'form' => $form->load('questions.options')
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

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
            'is_active' => 'boolean',
            'questions' => 'array',
        ]);

        DB::beginTransaction();

        try {
            $form->update([
                'form_title' => $validated['form_title'],
                'form_description' => $validated['form_description'] ?? null,
                'form_header' => $validated['form_header'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $form->questions()->delete();
            $this->saveQuestions($form, $request->input('questions', []));

            DB::commit();

            return response()->json([
                'message' => 'Tracer form updated successfully.',
                'form' => $form->load('questions.options')
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update tracer form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $form = TracerForm::findOrFail($id);
        $form->delete();

        return response()->json(['message' => 'Tracer form deleted successfully.']);
    }

    public function restore($id)
    {
        $form = TracerForm::onlyTrashed()->findOrFail($id);
        $form->restore();

        return response()->json([
            'message' => 'Tracer form restored successfully.',
            'form' => $form->load('questions.options'),
        ]);
    }

    public function toggleStatus(Request $request, $id)
    {
        $form = TracerForm::findOrFail($id);
        $form->is_active = $request->boolean('is_active');
        $form->save();

        return response()->json(['message' => 'Status updated successfully.']);
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
