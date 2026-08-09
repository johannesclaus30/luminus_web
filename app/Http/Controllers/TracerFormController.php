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
            'phases.*.target_alumni_type' => 'nullable|string|in:all,college,shs',
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
                'admin_id'         => session('admin_id') ?? 1,
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
            'phases.*.target_alumni_type' => 'nullable|string|in:all,college,shs',
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
                'title'               => $phaseData['title'],
                'subtitle'            => $phaseData['subtitle'] ?? null,
                'icon'                => $phaseData['icon'] ?? 'fa-user',
                'color'               => $phaseData['color'] ?? '#3b82f6',
                'target_alumni_type'  => $phaseData['target_alumni_type'] ?? 'all',
                'order_priority'      => $phaseIdx,
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
     * Get phase completion statistics.
     */
    private function getPhaseStats($form, $totalResponses)
    {
        try {
            $phaseStats = [];
            foreach ($form->phases as $phase) {
                $phaseQuestionIds = TracerQuestion::whereHas('section', function ($q) use ($phase) {
                    $q->where('phase_id', $phase->id);
                })->pluck('id');
                
                $totalPhaseQuestions = $phaseQuestionIds->count();
                $completionRate = 0;
                
                if ($totalPhaseQuestions > 0 && $totalResponses > 0) {
                    $totalPossibleAnswers = $totalPhaseQuestions * $totalResponses;
                    $actualAnswers = \App\Models\TracerAnswer::whereIn('question_id', $phaseQuestionIds)
                        ->whereHas('response', function ($q) use ($form) {
                            $q->where('form_id', $form->id);
                        })->count();
                    
                    $completionRate = $totalPossibleAnswers > 0 
                        ? round(($actualAnswers / $totalPossibleAnswers) * 100, 1) : 0;
                }
                
                $phaseStats[] = [
                    'title'          => $phase->title,
                    'color'          => $phase->color,
                    'completionRate' => $completionRate,
                    'icon'           => $phase->icon,
                ];
            }
            return $phaseStats;
        } catch (\Throwable $e) {
            \Log::error('Failed to get phase stats: ' . $e->getMessage());
            return [];
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

            // NEW: Get phase, program, year, and top responder stats
            $phaseStats = $this->getPhaseStats($form, $totalResponses);
            $programStats = $this->getProgramStats($form);
            $yearStats = $this->getYearStats($form);
            $topResponders = $this->getTopResponders($form);

            return response()->json([
                'totalAlumni'        => $totalAlumni,
                'completedResponses' => $completedResponses,
                'inProgressResponses'=> $inProgressResponses,
                'totalQuestions'     => $totalQuestions,
                'phaseCount'         => $phaseCount,
                'sectionCount'       => $sectionCount,
                'responseRate'       => $totalAlumni > 0 ? round(($totalResponses / $totalAlumni) * 100, 1) : 0,
                'phaseStats'         => $phaseStats,
                'programStats'       => $programStats,
                'yearStats'          => $yearStats,
                'topResponders'      => $topResponders,
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

    /**
     * Get program distribution statistics.
     */
    private function getProgramStats($form)
    {
        try {
            $alumniIds = $form->responses()->pluck('alumni_id');
            
            $programData = \App\Models\Alumni::whereIn('id', $alumniIds)
                ->select('program', DB::raw('count(*) as count'))
                ->groupBy('program')
                ->orderByDesc('count')
                ->get();
            
            return $programData->map(function ($item) {
                return [
                    'program' => $item->program ?? 'Unknown',
                    'count' => $item->count,
                ];
            })->toArray();
        } catch (\Throwable $e) {
            \Log::error('Failed to get program stats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get graduation year distribution statistics.
     */
    private function getYearStats($form)
    {
        try {
            $alumniIds = $form->responses()->pluck('alumni_id');
            
            $yearData = \App\Models\Alumni::whereIn('id', $alumniIds)
                ->select(DB::raw("COALESCE(EXTRACT(YEAR FROM year_graduated)::text, 'Unknown') as year"), DB::raw('count(*) as count'))
                ->groupBy('year_graduated')
                ->orderBy('year')
                ->get();
            
            return $yearData->map(function ($item) {
                return [
                    'year' => $item->year ?? 'Unknown',
                    'count' => $item->count,
                ];
            })->toArray();
        } catch (\Throwable $e) {
            \Log::error('Failed to get year stats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top responders by completion rate.
     */
    private function getTopResponders($form)
    {
        try {
            $totalQuestions = TracerQuestion::whereHas('section.phase.form', function ($q) use ($form) {
                $q->where('id', $form->id);
            })->count();
            
            $topResponders = $form->responses()
                ->with(['alumni:id,first_name,last_name,program,year_graduated'])
                ->where(function ($q) {
                    $q->where('status', 'in_progress')
                    ->orWhere('status', 'completed');
                })
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get()
                ->map(function ($response) use ($totalQuestions) {
                    $answeredQuestionIds = $response->answers()
                        ->distinct('question_id')
                        ->pluck('question_id')
                        ->unique()
                        ->count();
                    
                    $completion = $totalQuestions > 0 ? round(($answeredQuestionIds / $totalQuestions) * 100) : 0;
                    
                    return [
                        'name'       => $response->alumni->first_name . ' ' . $response->alumni->last_name,
                        'program'    => $response->alumni->program ?? 'N/A',
                        'year'       => $response->alumni->year_graduated ? date('Y', strtotime($response->alumni->year_graduated)) : 'N/A',
                        'completion' => $completion,
                    ];
                })
                ->sortByDesc('completion')
                ->values()
                ->toArray();
            
            return $topResponders;
        } catch (\Throwable $e) {
            \Log::error('Failed to get top responders: ' . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════
    // NEW: Get Phases Filtered by Alumni Type
    // ═══════════════════════════════════════

    /**
     * Get phases filtered by alumni type for the mobile app.
     * Route: GET /admin/alumni_tracer/{formId}/phases-for-alumni?alumni_id={alumniId}
     */
    public function getPhasesForAlumni(Request $request, $formId)
    {
        try {
            $alumniId = $request->input('alumni_id');
            
            if (!$alumniId) {
                return response()->json([
                    'error' => 'alumni_id parameter is required'
                ], 400);
            }
            
            $alumni = \App\Models\Alumni::findOrFail($alumniId);
            $alumniType = $alumni->alumni_type ?? 'college'; // Default to college if not set
            
            $form = TracerForm::with([
                'phases' => function ($q) use ($alumniType) {
                    $q->where(function ($query) use ($alumniType) {
                        $query->where('target_alumni_type', 'all')
                              ->orWhere('target_alumni_type', $alumniType);
                    })->orderBy('order_priority');
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
            ])->findOrFail($formId);
            
            return response()->json([
                'form' => $form,
                'alumni_type' => $alumniType,
                'phases_count' => $form->phases->count(),
                'filtered_phases' => $form->phases->map(function ($phase) {
                    return [
                        'id' => $phase->id,
                        'title' => $phase->title,
                        'subtitle' => $phase->subtitle,
                        'icon' => $phase->icon,
                        'color' => $phase->color,
                        'target_alumni_type' => $phase->target_alumni_type,
                        'sections_count' => $phase->sections->count(),
                        'questions_count' => $phase->sections->sum(function ($section) {
                            return $section->questions->count();
                        }),
                    ];
                }),
            ]);
            
        } catch (\Throwable $e) {
            \Log::error('Failed to get phases for alumni: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load phases',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPhasesDirectly()
    {
        try {
            $phases = TracerPhase::with([
                'sections' => function ($q) {
                    $q->orderBy('order_priority');
                },
                'sections.questions' => function ($q) {
                    $q->orderBy('order_priority');
                },
                'sections.questions.options' => function ($q) {
                    $q->orderBy('order_priority');
                },
                'sections.questions.gridRows' => function ($q) {
                    $q->orderBy('order_priority');
                },
                'sections.questions.gridColumns' => function ($q) {
                    $q->orderBy('order_priority');
                },
            ])
            ->orderBy('order_priority')
            ->get();

            return response()->json($phases);
        } catch (\Throwable $e) {
            \Log::error('Failed to load phases directly: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load phases',
                'message' => $e->getMessage()
            ], 500);
        }
    }

   public function savePhasesDirectly(Request $request)
{
    try {
        \Log::info('savePhasesDirectly called');
        
        $validated = $request->validate([
            'phases' => 'required|array',
            'phases.*.id' => 'nullable|integer',
            'phases.*.title' => 'required|string|max:255',
            'phases.*.subtitle' => 'nullable|string|max:255',
            'phases.*.icon' => 'nullable|string|max:100',
            'phases.*.color' => 'nullable|string|max:20',
            'phases.*.target_alumni_type' => 'nullable|string|in:all,college,shs',
            'phases.*.sections' => 'nullable|array',
            'phases.*.sections.*.id' => 'nullable|integer',
            'phases.*.sections.*.title' => 'required|string|max:255',
            'phases.*.sections.*.description' => 'nullable|string',
            'phases.*.sections.*.questions' => 'nullable|array',
            'phases.*.sections.*.questions.*.id' => 'nullable|integer',
            'phases.*.sections.*.questions.*.question_text' => 'required|string',
            'phases.*.sections.*.questions.*.type' => 'required|string|in:short_answer,paragraph,multiple_choice,checkboxes,dropdown,file_upload,likert_scale,multiple_choice_grid',
            'phases.*.sections.*.questions.*.description' => 'nullable|string',
            'phases.*.sections.*.questions.*.placeholder' => 'nullable|string|max:255',
            'phases.*.sections.*.questions.*.is_required' => 'boolean',
            'phases.*.sections.*.questions.*.file_types' => 'nullable|array',
            'phases.*.sections.*.questions.*.max_file_size' => 'nullable|integer',
            'phases.*.sections.*.questions.*.options' => 'nullable|array',
            'phases.*.sections.*.questions.*.options.*.label' => 'required|string',
            'phases.*.sections.*.questions.*.grid_rows' => 'nullable|array',
            'phases.*.sections.*.questions.*.grid_rows.*.label' => 'required|string',
            'phases.*.sections.*.questions.*.grid_columns' => 'nullable|array',
            'phases.*.sections.*.questions.*.grid_columns.*.label' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            // Get existing phase IDs
            $existingPhaseIds = TracerPhase::pluck('id')->toArray();
            $newPhaseIds = [];

            foreach ($validated['phases'] as $phaseIdx => $phaseData) {
                // ✅ FIX: Check if phase exists properly
                $phase = null;
                
                if (isset($phaseData['id']) && $phaseData['id'] !== null && $phaseData['id'] > 0) {
                    $phase = TracerPhase::find($phaseData['id']);
                }
                
                // If phase doesn't exist or ID is null/0, create a new one
                if (!$phase) {
                    $phase = new TracerPhase();
                }

                $phase->title = $phaseData['title'];
                $phase->subtitle = $phaseData['subtitle'] ?? null;
                $phase->icon = $phaseData['icon'] ?? 'fa-user';
                $phase->color = $phaseData['color'] ?? '#3b82f6';
                $phase->target_alumni_type = $phaseData['target_alumni_type'] ?? 'all';
                $phase->order_priority = $phaseIdx;
                $phase->save();

                $newPhaseIds[] = $phase->id;

                // Handle sections (if any)
                if (!empty($phaseData['sections'])) {
                    $existingSectionIds = $phase->sections()->pluck('id')->toArray();
                    $newSectionIds = [];

                    foreach ($phaseData['sections'] as $sectionIdx => $sectionData) {
                        // ✅ FIX: Check if section exists properly
                        $section = null;
                        
                        if (isset($sectionData['id']) && $sectionData['id'] !== null && $sectionData['id'] > 0) {
                            $section = TracerSection::find($sectionData['id']);
                        }
                        
                        if (!$section) {
                            $section = new TracerSection();
                        }

                        $section->phase_id = $phase->id;
                        $section->title = $sectionData['title'];
                        $section->description = $sectionData['description'] ?? null;
                        $section->order_priority = $sectionIdx;
                        $section->save();

                        $newSectionIds[] = $section->id;

                        // Handle questions
                        if (!empty($sectionData['questions'])) {
                            $existingQuestionIds = $section->questions()->pluck('id')->toArray();
                            $newQuestionIds = [];

                            foreach ($sectionData['questions'] as $qIdx => $qData) {
                                // ✅ FIX: Check if question exists properly
                                $question = null;
                                
                                if (isset($qData['id']) && $qData['id'] !== null && $qData['id'] > 0) {
                                    $question = TracerQuestion::find($qData['id']);
                                }
                                
                                if (!$question) {
                                    $question = new TracerQuestion();
                                }

                                $question->section_id = $section->id;
                                $question->type = $qData['type'];
                                $question->question_text = $qData['question_text'];
                                $question->description = $qData['description'] ?? null;
                                $question->placeholder = $qData['placeholder'] ?? null;
                                $question->is_required = $qData['is_required'] ?? true;
                                $question->file_types = $qData['file_types'] ?? null;
                                $question->max_file_size = $qData['max_file_size'] ?? null;
                                $question->order_priority = $qIdx;
                                $question->save();

                                $newQuestionIds[] = $question->id;

                                // Handle options
                                if (!empty($qData['options'])) {
                                    $question->options()->delete();
                                    foreach ($qData['options'] as $optIdx => $optData) {
                                        $question->options()->create([
                                            'option_label' => is_array($optData) ? ($optData['label'] ?? '') : $optData,
                                            'order_priority' => $optIdx,
                                        ]);
                                    }
                                }

                                // Handle grid rows
                                if (!empty($qData['grid_rows'])) {
                                    $question->gridRows()->delete();
                                    foreach ($qData['grid_rows'] as $rowIdx => $rowData) {
                                        $question->gridRows()->create([
                                            'row_label' => is_array($rowData) ? ($rowData['label'] ?? '') : $rowData,
                                            'order_priority' => $rowIdx,
                                        ]);
                                    }
                                }

                                // Handle grid columns
                                if (!empty($qData['grid_columns'])) {
                                    $question->gridColumns()->delete();
                                    foreach ($qData['grid_columns'] as $colIdx => $colData) {
                                        $question->gridColumns()->create([
                                            'column_label' => is_array($colData) ? ($colData['label'] ?? '') : $colData,
                                            'order_priority' => $colIdx,
                                        ]);
                                    }
                                }
                            }

                            // Delete questions that were removed
                            $questionsToDelete = array_diff($existingQuestionIds, $newQuestionIds);
                            if (!empty($questionsToDelete)) {
                                TracerQuestion::whereIn('id', $questionsToDelete)->delete();
                            }
                        }
                    }

                    // Delete sections that were removed
                    $sectionsToDelete = array_diff($existingSectionIds, $newSectionIds);
                    if (!empty($sectionsToDelete)) {
                        TracerSection::whereIn('id', $sectionsToDelete)->delete();
                    }
                }
            }

            // Delete phases that were removed
            $phasesToDelete = array_diff($existingPhaseIds, $newPhaseIds);
            if (!empty($phasesToDelete)) {
                TracerPhase::whereIn('id', $phasesToDelete)->delete();
            }

            DB::commit();

            // Return all phases
            $allPhases = TracerPhase::with([
                'sections.questions.options',
                'sections.questions.gridRows',
                'sections.questions.gridColumns',
            ])->orderBy('order_priority')->get();

            return response()->json([
                'message' => 'Phases saved successfully.',
                'phases' => $allPhases
            ]);
            
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Failed to save phases directly: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Failed to save phases.',
                'error' => $e->getMessage()
            ], 500);
        }
    } catch (\Throwable $e) {
        \Log::error('Validation error: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'message' => 'Failed to save phases.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function getActiveFormId()
    {
        try {
            $form = TracerForm::first();
            
            if (!$form) {
                // Create a default form if none exists
                $form = TracerForm::create([
                    'admin_id' => session('admin_id') ?? 1,
                    'form_title' => 'Alumni Tracer',
                    'form_description' => 'Default tracer form',
                    'status' => 1, // ✅ Use 1 for ACTIVE (not 2)
                ]);
            }
            
            return response()->json([
                'form_id' => $form->id,
                'form_title' => $form->form_title,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to get active form ID: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to get form ID',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
    * Delete a single question directly.
    */
    public function deleteQuestionDirectly($questionId)
    {
        try {
            $question = TracerQuestion::findOrFail($questionId);
            $question->delete();
            
            return response()->json([
                'message' => 'Question deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to delete question: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete question.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a single section directly.
     */
    public function deleteSectionDirectly($sectionId)
    {
        try {
            $section = TracerSection::findOrFail($sectionId);
            $section->delete();
            
            return response()->json([
                'message' => 'Section deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to delete section: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete section.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a single phase directly.
     */
    public function deletePhaseDirectly($phaseId)
    {
        try {
            $phase = TracerPhase::findOrFail($phaseId);
            $phase->delete();
            
            return response()->json([
                'message' => 'Phase deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to delete phase: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete phase.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}