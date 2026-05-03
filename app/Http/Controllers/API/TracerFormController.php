<?php

namespace App\Http\Controllers;

use App\Models\TracerForms;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TracerFormController extends Controller
{
    public function index(): JsonResponse
    {
        $forms = TracerForms::query()
            ->select([
                'id',
                'admin_id',
                'form_title',
                'form_header',
                'form_description',
                'status',
                'created_at',
                'updated_at',
            ])
            ->with([
                'tracerQuestions' => function ($query) {
                    $query
                        ->select([
                            'id',
                            'form_id',
                            'type',
                            'question_text',
                            'description',
                            'is_required',
                            'order_priority',
                            'settings',
                        ])
                        ->orderBy('order_priority')
                        ->with([
                            'answerOptions' => function ($optionQuery) {
                                $optionQuery->select([
                                    'id',
                                    'tq_id',
                                    'option_label',
                                    'option_value',
                                ]);
                            },
                        ]);
                },
            ])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'tracer_forms' => $forms,
        ]);
    }

    public function submit(Request $request, TracerForms $form): JsonResponse
    {
        $validated = $request->validate([
            'answers' => ['required', 'array'],
        ]);

        $alumni = $request->user();
        if (!$alumni) {
            throw ValidationException::withMessages([
                'auth' => ['You must be logged in to submit this form.'],
            ]);
        }

        $questions = $form->tracerQuestions()
            ->select([
                'id',
                'type',
                'is_required',
            ])
            ->with([
                'answerOptions' => function ($query) {
                    $query->select(['id', 'tq_id', 'option_value', 'option_label']);
                },
            ])
            ->get();

        $answersByQuestionId = Arr::get($validated, 'answers', []);

        $missingRequired = [];
        foreach ($questions as $question) {
            if (!$question->is_required) {
                continue;
            }

            $qid = (string) $question->id;
            if (!array_key_exists($qid, $answersByQuestionId)) {
                $missingRequired[] = $qid;
                continue;
            }

            $value = $answersByQuestionId[$qid];
            $isEmptyString = is_string($value) && trim($value) === '';
            $isEmptyArray = is_array($value) && count($value) === 0;

            if ($value === null || $isEmptyString || $isEmptyArray) {
                $missingRequired[] = $qid;
            }
        }

        if (count($missingRequired) > 0) {
            throw ValidationException::withMessages([
                'answers' => ['Required questions are missing answers.'],
                'question_ids' => $missingRequired,
            ]);
        }

        DB::transaction(function () use ($alumni, $form, $questions, $answersByQuestionId) {
            $now = now();
            $responseId = DB::table('tracer_responses')->insertGetId([
                'alumni_id' => $alumni->id,
                'form_id' => $form->id,
                'submitted_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($questions as $question) {
                $qid = (string) $question->id;
                if (!array_key_exists($qid, $answersByQuestionId)) {
                    continue;
                }

                $rawValue = $answersByQuestionId[$qid];
                $normalized = is_array($rawValue)
                    ? json_encode(array_values($rawValue), JSON_UNESCAPED_UNICODE)
                    : (string) $rawValue;

                if ($normalized === '' || $normalized === '[]') {
                    continue;
                }

                DB::table('tracer_answers')->insert([
                    'tracer_response_id' => $responseId,
                    'tq_id' => $question->id,
                    'answer_value' => $normalized,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        return response()->json([
            'message' => 'Tracer form submitted successfully.',
        ], 201);
    }

    public function userResponse(Request $request, TracerForms $form): JsonResponse
    {
        $alumni = $request->user();
        if (!$alumni) {
            return response()->json(['response' => null]);
        }

        $response = DB::table('tracer_responses')
            ->where('alumni_id', $alumni->id)
            ->where('form_id', $form->id)
            ->select(['id', 'submitted_at'])
            ->first();

        return response()->json([
            'response' => $response,
        ]);
    }
}
