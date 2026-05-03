<?php

namespace App\Http\Controllers;

use App\Models\AlumniEmployment;
use Illuminate\Http\Request;

class AlumniEmploymentController extends Controller
{
    /**
     * Store a new employment record for the authenticated alumni.
     */
    public function store(Request $request)
    {
        $alumni = $request->user();

        if (!$alumni) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $employment = AlumniEmployment::create([
            'alumni_id' => $alumni->id,
            'job_title' => $validated['title'],
            'company' => $validated['subtitle'],
            'location' => $validated['location'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'career_description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Employment record created successfully.',
            'employment' => $this->formatEmployment($employment),
        ], 201);
    }

    /**
     * Update an employment record.
     */
    public function update(Request $request, AlumniEmployment $employment)
    {
        $alumni = $request->user();

        if (!$alumni || $employment->alumni_id !== $alumni->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'subtitle' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date',
            'location' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $updateData = [];
        if (isset($validated['title'])) {
            $updateData['job_title'] = $validated['title'];
        }
        if (isset($validated['subtitle'])) {
            $updateData['company'] = $validated['subtitle'];
                if (isset($validated['start_date'])) {
                    $updateData['start_date'] = $validated['start_date'];
                }
                if (isset($validated['end_date'])) {
                    $updateData['end_date'] = $validated['end_date'];
                }
        }
        if (isset($validated['location'])) {
            $updateData['location'] = $validated['location'];
        }
        if (isset($validated['description'])) {
            $updateData['career_description'] = $validated['description'];
        }

        $employment->update($updateData);

        return response()->json([
            'message' => 'Employment record updated successfully.',
            'employment' => $this->formatEmployment($employment),
        ]);
    }

    /**
     * Delete an employment record.
     */
    public function destroy(Request $request, AlumniEmployment $employment)
    {
        $alumni = $request->user();

        if (!$alumni || $employment->alumni_id !== $alumni->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $employment->delete();

        return response()->json([
            'message' => 'Employment record deleted successfully.',
        ]);
    }

    /**
     * Format employment record for API response (map back to mobile app fields).
     */
    private function formatEmployment(AlumniEmployment $employment)
    {
        return [
            'id' => $employment->id,
            'title' => $employment->job_title,
            'subtitle' => $employment->company,
            'period' => $this->buildPeriodString($employment->start_date, $employment->end_date),
            'startYear' => $employment->start_date ? (int) date('Y', strtotime($employment->start_date)) : null,
            'endYear' => $employment->end_date ? (int) date('Y', strtotime($employment->end_date)) : null,
            'location' => $employment->location,
            'description' => $employment->career_description,
        ];


    }

    /**
     * Build period string from start and end dates.
     */
    private function buildPeriodString($startDate, $endDate)
    {
        if (!$startDate && !$endDate) {
            return '';
        }

        $start = $startDate ? date('Y', strtotime($startDate)) : 'Present';
        $end = $endDate ? date('Y', strtotime($endDate)) : 'Present';

        return "{$start} - {$end}";
    }
}
