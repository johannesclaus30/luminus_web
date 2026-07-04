<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TracerForm;
use App\Models\TracerPhase;
use App\Models\TracerSection;
use App\Models\TracerQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TracerFormController extends Controller
{
    /**
     * Show the tracer admin page.
     */
    public function index()
    {
        return view('admin_alumni_tracer');
    }

    /**
     * Get all forms with full nested structure.
     */
    public function list()
    {
        try {
            $forms = TracerForm::with($this->nestedEagerLoad())
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

    /**
     * Get deleted forms only.
     */
    public function deleted()
    {
        try {
            $forms = TracerForm::with($this->nestedEagerLoad())
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

    /**
     * Get a single form with full nested structure.
     */
    public function show($id)
    {
        try {
            $form = TracerForm::with($this->nestedEagerLoad())
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

    /**
     * Create a new tracer form with phases, sections, and questions.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_title'       => 'required|string|max:255',
            'form_description' => 'nullable|string',
            'status'           => 'integer|in:0,1,2,3',
            'phases'           => 'nullable|array',
            'phases.*.title'    => 'required|string|max:255',
            'phases.*.subtitle' => 'nullable|string|max:255',
            'phases.*.icon'     => 'nullable|string|max:100',
            'phases.*.color'    => 'nullable|string|max:20',
            'phases.*.sections' => 'nullable|array',
            'phases.*.sections.*.title'       => 'required_with:phases.*.sections|string|max:255',
            'phases.*.sections.*.description' => 'nullable|string',
            'phases.*.sections.*.questions'   => 'nullable|array',
            'phases.*.sections.*.questions.*.question_text' => 'required_with:phases.*.sections.*.questions|string',
            'phases.*.sections.*.questions.*.type' => 'required_with:phases.*.sections.*.questions|string|in:short_answer,paragraph,multiple_choice,checkboxes,dropdown,file_upload,likert_scale,multiple_choice_grid',
            'phases.*.sections.*.questions.*.description'  => 'nullable|string',
            'phases.*.sections.*.questions.*.placeholder'  => 'nullable|string|max:255',
            'phases.*.sections.*.questions.*.is_required'  => 'boolean',
            'phases.*.sections.*.questions.*.file_types'   => 'nullable|array',
            'phases.*.sections.*.questions.*.max_file_size' => 'nullable|integer',
            'phases.*.sections.*.questions.*.options'      => 'nullable|array',
            'phases.*.sections.*.questions.*.options.*.label' => 'required|string',
            'phases.*.sections.*.questions.*.grid_rows'    => 'nullable|array',
            'phases.*.sections.*.questions.*.grid_rows.*.label' => 'required|string',
            'phases.*.sections.*.questions.*.grid_columns' => 'nullable|array',
            'phases.*.sections.*.questions.*.grid_columns.*.label' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $form = TracerForm::create([
                'admin_id'         => session('admin_id') ?? 1,  // ✅ FIXED
                'form_title'       => $validated['form_title'],
                'form_description' => $validated['form_description'] ?? null,
                'status'           => $validated['status'] ?? TracerForm::STATUS_DRAFT,
            ]);

            if (!empty($validated['phases'])) {
                $this->savePhases($form, $validated['phases']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Tracer form created successfully.',
                'form'    => $form->load($this->nestedEagerLoad())
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Failed to create tracer form: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create tracer form.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing form (replace all phases/sections/questions).
     */
    public function update(Request $request, $id)
    {
        $form = TracerForm::findOrFail($id);

        $validated = $request->validate([
            'form_title'       => 'required|string|max:255',
            'form_description' => 'nullable|string',
            'status'           => 'integer|in:0,1,2,3',
            'phases'           => 'nullable|array',
            'phases.*.title'    => 'required|string|max:255',
            'phases.*.subtitle' => 'nullable|string|max:255',
            'phases.*.icon'     => 'nullable|string|max:100',
            'phases.*.color'    => 'nullable|string|max:20',
            'phases.*.sections' => 'nullable|array',
            'phases.*.sections.*.title'       => 'required|string|max:255',
            'phases.*.sections.*.description' => 'nullable|string',
            'phases.*.sections.*.questions'   => 'nullable|array',
            'phases.*.sections.*.questions.*.question_text' => 'required|string',
            'phases.*.sections.*.questions.*.type' => 'required|string|in:short_answer,paragraph,multiple_choice,checkboxes,dropdown,file_upload,likert_scale,multiple_choice_grid',
            'phases.*.sections.*.questions.*.description'  => 'nullable|string',
            'phases.*.sections.*.questions.*.placeholder'  => 'nullable|string|max:255',
            'phases.*.sections.*.questions.*.is_required'  => 'boolean',
            'phases.*.sections.*.questions.*.file_types'   => 'nullable|array',
            'phases.*.sections.*.questions.*.max_file_size' => 'nullable|integer',
            'phases.*.sections.*.questions.*.options'      => 'nullable|array',
            'phases.*.sections.*.questions.*.options.*.label' => 'required|string',
            'phases.*.sections.*.questions.*.grid_rows'    => 'nullable|array',
            'phases.*.sections.*.questions.*.grid_rows.*.label' => 'required|string',
            'phases.*.sections.*.questions.*.grid_columns' => 'nullable|array',
            'phases.*.sections.*.questions.*.grid_columns.*.label' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $form->update([
                'form_title'       => $validated['form_title'],
                'form_description' => $validated['form_description'] ?? null,
                'status'           => $validated['status'] ?? $form->status,
            ]);

            // Delete old structure (ON DELETE CASCADE handles nested)
            $form->phases()->delete();

            // Save new structure
            if (!empty($validated['phases'])) {
                $this->savePhases($form, $validated['phases']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Tracer form updated successfully.',
                'form'    => $form->load($this->nestedEagerLoad())
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Failed to update tracer form: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to update tracer form.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Soft delete a form.
     */
    public function destroy($id)
    {
        try {
            $form = TracerForm::findOrFail($id);

            // Delete form header image if exists (for backward compatibility)
            if ($form->form_header ?? null) {
                $this->deleteFormHeader($form->form_header);
            }

            $form->update(['status' => TracerForm::STATUS_DELETED]);

            return response()->json([
                'message' => 'Tracer form deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to delete tracer form: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete tracer form.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore a deleted form.
     */
    public function restore($id)
    {
        try {
            $form = TracerForm::findOrFail($id);

            if ($form->status !== TracerForm::STATUS_DELETED) {
                return response()->json(['message' => 'Form is not deleted.'], 400);
            }

            $form->update(['status' => TracerForm::STATUS_ACTIVE]);

            return response()->json([
                'message' => 'Tracer form restored successfully.',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to restore tracer form: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to restore tracer form.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle form status.
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $form = TracerForm::findOrFail($id);
            $newStatus = $request->integer('status');

            if (!in_array($newStatus, [
                TracerForm::STATUS_DELETED,
                TracerForm::STATUS_ACTIVE,
                TracerForm::STATUS_DRAFT,
                TracerForm::STATUS_CLOSED
            ])) {
                return response()->json(['message' => 'Invalid status.'], 400);
            }

            $form->update(['status' => $newStatus]);

            return response()->json(['message' => 'Status updated successfully.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to update status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════

    /**
     * Eager load array for nested relationships.
     */
    private function nestedEagerLoad(): array
    {
        return [
            'phases' => function ($q) {
                $q->orderBy('order_priority');
            },
            'phases.sections' => function ($q) {
                $q->orderBy('order_priority');
            },
            'phases.sections.questions' => function ($q) {
                $q->orderBy('order_priority');
            },
            'phases.sections.questions.options' => function ($q) {
                $q->orderBy('order_priority');
            },
            'phases.sections.questions.gridRows' => function ($q) {
                $q->orderBy('order_priority');
            },
            'phases.sections.questions.gridColumns' => function ($q) {
                $q->orderBy('order_priority');
            },
        ];
    }

    /**
     * Save phases from request data.
     */
    private function savePhases(TracerForm $form, array $phases)
    {
        foreach ($phases as $phaseIdx => $phaseData) {
            $phase = $form->phases()->create([
                'title'          => $phaseData['title'],
                'subtitle'       => $phaseData['subtitle'] ?? null,
                'icon'           => $phaseData['icon'] ?? 'fa-user',
                'color'          => $phaseData['color'] ?? '#3b82f6',
                'order_priority' => $phaseIdx,
            ]);

            if (!empty($phaseData['sections'])) {
                $this->saveSections($phase, $phaseData['sections']);
            }
        }
    }

    /**
     * Save sections from request data.
     */
    private function saveSections(TracerPhase $phase, array $sections)
    {
        foreach ($sections as $sectionIdx => $sectionData) {
            $section = $phase->sections()->create([
                'title'          => $sectionData['title'],
                'description'    => $sectionData['description'] ?? null,
                'order_priority' => $sectionIdx,
            ]);

            if (!empty($sectionData['questions'])) {
                $this->saveQuestions($section, $sectionData['questions']);
            }
        }
    }

    /**
     * Save questions from request data.
     */
    private function saveQuestions(TracerSection $section, array $questions)
    {
        foreach ($questions as $qIdx => $qData) {
            $question = $section->questions()->create([
                'type'           => $qData['type'],
                'question_text'  => $qData['question_text'],
                'description'    => $qData['description'] ?? null,
                'placeholder'    => $qData['placeholder'] ?? null,
                'is_required'    => $qData['is_required'] ?? true,
                'order_priority' => $qIdx,
                'file_types'     => $qData['file_types'] ?? null,
                'max_file_size'  => $qData['max_file_size'] ?? null,
            ]);

            // Save simple options (multiple_choice, checkboxes, dropdown)
            if (!empty($qData['options'])) {
                foreach ($qData['options'] as $optIdx => $optionData) {
                    $question->options()->create([
                        'option_label'   => is_array($optionData) ? ($optionData['label'] ?? '') : $optionData,
                        'order_priority' => $optIdx,
                    ]);
                }
            }

            // Save grid rows (likert_scale, multiple_choice_grid)
            if (!empty($qData['grid_rows'])) {
                foreach ($qData['grid_rows'] as $rowIdx => $rowData) {
                    $question->gridRows()->create([
                        'row_label'      => is_array($rowData) ? ($rowData['label'] ?? '') : $rowData,
                        'order_priority' => $rowIdx,
                    ]);
                }
            }

            // Save grid columns (likert_scale, multiple_choice_grid)
            if (!empty($qData['grid_columns'])) {
                foreach ($qData['grid_columns'] as $colIdx => $colData) {
                    $question->gridColumns()->create([
                        'column_label'   => is_array($colData) ? ($colData['label'] ?? '') : $colData,
                        'order_priority' => $colIdx,
                    ]);
                }
            }
        }
    }

    /**
     * Delete form header image from storage.
     */
    private function deleteFormHeader(string $imageUrl)
    {
        try {
            $path = parse_url($imageUrl, PHP_URL_PATH);
            if (preg_match('/luminus_assets\/(.+)$/', $path, $matches)) {
                \Storage::disk('supabase_admin')->delete($matches[1]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to delete form header: ' . $e->getMessage());
        }
    }
}