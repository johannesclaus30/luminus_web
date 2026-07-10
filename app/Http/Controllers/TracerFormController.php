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

    /**
     * Get dashboard statistics for a form.
     */
    public function dashboardStats($formId)
    {
        try {
            $form = TracerForm::withCount(['responses', 'responses as completed_count' => function ($q) {
                $q->where('status', 'completed');
            }])->findOrFail($formId);

            $totalAlumni = \App\Models\Alumni::count();
            $totalResponses = $form->responses_count;
            $completedResponses = $form->completed_count;
            $inProgressResponses = $totalResponses - $completedResponses;

            // Count total questions
            $totalQuestions = TracerQuestion::whereHas('section.phase.form', function ($q) use ($formId) {
                $q->where('id', $formId);
            })->count();

            // Get phase count
            $phaseCount = $form->phases()->count();
            $sectionCount = TracerSection::whereHas('phase.form', function ($q) use ($formId) {
                $q->where('id', $formId);
            })->count();

            return response()->json([
                'totalAlumni' => $totalAlumni,
                'completedResponses' => $completedResponses,
                'inProgressResponses' => $inProgressResponses,
                'totalQuestions' => $totalQuestions,
                'phaseCount' => $phaseCount,
                'sectionCount' => $sectionCount,
                'responseRate' => $totalAlumni > 0 ? round(($totalResponses / $totalAlumni) * 100, 1) : 0,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get recent submissions for dashboard.
     */
    public function recentSubmissions($formId)
    {
        try {
            $submissions = \App\Models\TracerResponse::with(['alumni:id,first_name,last_name,program,year_graduated'])
                ->where('form_id', $formId)
                ->orderByDesc('updated_at')
                ->limit(10)
                ->get()
                ->map(function ($response) {
                    // Calculate completion percentage
                    $totalQuestions = TracerQuestion::whereHas('section.phase.form', function ($q) use ($response) {
                        $q->where('id', $response->form_id);
                    })->count();
                    
                    $answeredQuestions = $response->answers()->count();
                    $completion = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100) : 0;

                    return [
                        'id' => $response->id,
                        'alumni_name' => $response->alumni->first_name . ' ' . $response->alumni->last_name,
                        'program' => $response->alumni->program ?? 'N/A',
                        'year_graduated' => $response->alumni->year_graduated ? date('Y', strtotime($response->alumni->year_graduated)) : 'N/A',
                        'completion' => $completion,
                        'status' => $response->status,
                        'submitted_at' => $response->submitted_at ? $response->submitted_at->format('M d, Y') : $response->created_at->format('M d, Y'),
                    ];
                });

            return response()->json($submissions);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get analytics data for a specific question.
     */
    public function questionAnalytics($questionId)
    {
        try {
            $question = TracerQuestion::with(['options', 'gridRows', 'gridColumns'])->findOrFail($questionId);
            
            $data = [
                'question' => $question,
                'type' => $question->type,
            ];

            if (in_array($question->type, ['multiple_choice', 'dropdown'])) {
                // Get answer counts per option
                $optionCounts = [];
                foreach ($question->options as $option) {
                    $count = \App\Models\TracerAnswerSelection::whereHas('answer', function ($q) use ($questionId) {
                        $q->where('question_id', $questionId);
                    })->where('option_id', $option->id)->count();
                    
                    $optionCounts[] = [
                        'label' => $option->option_label,
                        'count' => $count,
                    ];
                }
                $data['options'] = $optionCounts;
                
            } elseif ($question->type === 'checkboxes') {
                // Count each selected option
                $optionCounts = [];
                foreach ($question->options as $option) {
                    $count = \App\Models\TracerAnswerSelection::whereHas('answer', function ($q) use ($questionId) {
                        $q->where('question_id', $questionId);
                    })->where('option_id', $option->id)->count();
                    
                    $optionCounts[] = [
                        'label' => $option->option_label,
                        'count' => $count,
                    ];
                }
                $data['options'] = $optionCounts;
                
            } elseif (in_array($question->type, ['likert_scale', 'multiple_choice_grid'])) {
                // Get row and column data
                $rowData = [];
                foreach ($question->gridRows as $row) {
                    $columnCounts = [];
                    foreach ($question->gridColumns as $column) {
                        $count = \App\Models\TracerAnswerSelection::whereHas('answer', function ($q) use ($questionId, $row) {
                            $q->where('question_id', $questionId)
                            ->where('grid_row_id', $row->id);
                        })->where('grid_column_id', $column->id)->count();
                        
                        $columnCounts[] = [
                            'column_label' => $column->column_label,
                            'count' => $count,
                        ];
                    }
                    $rowData[] = [
                        'row_label' => $row->row_label,
                        'columns' => $columnCounts,
                    ];
                }
                $data['gridData'] = $rowData;
                
            } elseif (in_array($question->type, ['short_answer', 'paragraph'])) {
                // Get response count
                $responseCount = \App\Models\TracerAnswer::where('question_id', $questionId)
                    ->whereNotNull('answer_value')
                    ->count();
                $data['responseCount'] = $responseCount;
            }

            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get analytics KPIs.
     */
    public function analyticsKPIs($formId)
    {
        try {
            $form = TracerForm::findOrFail($formId);
            $totalAlumni = \App\Models\Alumni::count();
            $totalResponses = $form->responses()->count();
            $completedResponses = $form->responses()->where('status', 'completed')->count();
            
            $responseRate = $totalAlumni > 0 ? round(($totalResponses / $totalAlumni) * 100, 1) : 0;
            
            // Calculate average completion
            $avgCompletion = 0;
            if ($totalResponses > 0) {
                $totalQuestions = TracerQuestion::whereHas('section.phase.form', function ($q) use ($formId) {
                    $q->where('id', $formId);
                })->count();
                
                if ($totalQuestions > 0) {
                    $responses = $form->responses()->withCount('answers')->get();
                    $avgCompletion = round($responses->avg(function ($r) use ($totalQuestions) {
                        return ($r->answers_count / $totalQuestions) * 100;
                    }), 1);
                }
            }
            
            // Average time to complete (in minutes)
            $avgTime = null;
            if ($completedResponses > 0) {
                $avgTime = $form->responses()
                    ->where('status', 'completed')
                    ->whereNotNull('submitted_at')
                    ->get()
                    ->avg(function ($r) {
                        return $r->created_at->diffInMinutes($r->submitted_at);
                    });
                $avgTime = round($avgTime, 1);
            }

            return response()->json([
                'responseRate' => $responseRate,
                'avgCompletion' => $avgCompletion,
                'totalResponses' => $totalResponses,
                'avgTimeToComplete' => $avgTime,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
 * Get alumni who haven't completed the tracer form.
 */
public function getIncompleteAlumni($formId)
{
    try {
        $form = TracerForm::findOrFail($formId);
        
        // Get all alumni IDs who have completed responses
        $completedAlumniIds = $form->responses()
            ->where('status', 'completed')
            ->pluck('alumni_id');
        
        // Get alumni who haven't completed (or haven't started) the tracer
        // Note: Your table is 'alumnis' (with 's') - make sure your model reflects this
        $incompleteAlumni = \App\Models\Alumni::whereNotIn('id', $completedAlumniIds)
            ->select('id', 'first_name', 'last_name', 'email', 'program', 'year_graduated')
            ->orderBy('last_name')
            ->get()
            ->map(function ($alumni) use ($form) {
                // Check if they have an in-progress response
                $response = $form->responses()
                    ->where('alumni_id', $alumni->id)
                    ->where('status', 'in_progress')
                    ->first();
                
                // Calculate completion if response exists
                $completion = 0;
                $lastActivity = null;
                
                if ($response) {
                    $totalQuestions = TracerQuestion::whereHas('section.phase.form', function ($q) use ($form) {
                        $q->where('id', $form->id);
                    })->count();
                    
                    $answeredQuestions = $response->answers()->count();
                    $completion = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100) : 0;
                    $lastActivity = $response->updated_at ? $response->updated_at->diffForHumans() : null;
                }
                
                return [
                    'id' => $alumni->id,
                    'name' => $alumni->first_name . ' ' . $alumni->last_name,
                    'email' => $alumni->email,
                    'program' => $alumni->program ?? 'N/A',
                    'year_graduated' => $alumni->year_graduated ? date('Y', strtotime($alumni->year_graduated)) : 'N/A',
                    'has_started' => !is_null($response),
                    'completion' => $completion,
                    'last_activity' => $lastActivity,
                ];
            });
        
        // Get total stats
        $totalAlumni = \App\Models\Alumni::count();
        $completedCount = $form->responses()->where('status', 'completed')->count();
        $inProgressCount = $form->responses()->where('status', 'in_progress')->count();
        $notStartedCount = $totalAlumni - $completedCount - $inProgressCount;
        
        return response()->json([
            'alumni' => $incompleteAlumni,
            'stats' => [
                'total_alumni' => $totalAlumni,
                'completed' => $completedCount,
                'in_progress' => $inProgressCount,
                'not_started' => $notStartedCount,
            ],
            'form_title' => $form->form_title,
        ]);
        
    } catch (\Throwable $e) {
        \Log::error('Failed to get incomplete alumni: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to load alumni data',
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
 * Send reminder email to specific alumni.
 */
public function sendReminder(Request $request, $formId, $alumniId)
{
    try {
        $form = TracerForm::findOrFail($formId);
        $alumni = \App\Models\Alumni::findOrFail($alumniId);
        
        // Check if alumni has already completed
        $existingResponse = $form->responses()
            ->where('alumni_id', $alumniId)
            ->where('status', 'completed')
            ->first();
            
        if ($existingResponse) {
            return response()->json([
                'error' => 'This alumni has already completed the tracer.'
            ], 400);
        }
        
        // Send email using Laravel's mail system
        \Mail::to($alumni->email)->send(new \App\Mail\TracerReminderMail($alumni, $form));
        
        return response()->json([
            'message' => 'Reminder sent successfully to ' . $alumni->first_name . ' ' . $alumni->last_name,
        ]);
        
    } catch (\Throwable $e) {
        \Log::error('Failed to send reminder: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to send reminder',
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
 * Send reminder emails to all incomplete alumni.
 */
public function sendReminderToAll(Request $request, $formId)
{
    $request->validate([
        'alumni_ids' => 'required|array',
        'alumni_ids.*' => 'integer',
    ]);
    
    try {
        $form = TracerForm::findOrFail($formId);
        $sentCount = 0;
        $failedEmails = [];
        
        foreach ($request->alumni_ids as $alumniId) {
            try {
                $alumni = \App\Models\Alumni::findOrFail($alumniId);
                
                // Check if completed
                $existingResponse = $form->responses()
                    ->where('alumni_id', $alumniId)
                    ->where('status', 'completed')
                    ->first();
                    
                if ($existingResponse) {
                    continue;
                }
                
                // Send email
                \Mail::to($alumni->email)->send(new \App\Mail\TracerReminderMail($alumni, $form));
                
                $sentCount++;
                
            } catch (\Exception $e) {
                $alumniName = isset($alumni) ? ($alumni->first_name . ' ' . $alumni->last_name) : "Alumni ID: $alumniId";
                $failedEmails[] = $alumniName;
                \Log::error("Failed to send reminder to alumni $alumniId: " . $e->getMessage());
            }
        }
        
        $message = "Successfully sent {$sentCount} reminders.";
        if (count($failedEmails) > 0) {
            $message .= " Failed: " . implode(', ', $failedEmails);
        }
        
        return response()->json([
            'message' => $message,
            'sent_count' => $sentCount,
            'failed_count' => count($failedEmails),
        ]);
        
    } catch (\Throwable $e) {
        \Log::error('Failed to send bulk reminders: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to send reminders',
            'message' => $e->getMessage()
        ], 500);
    }
}



}