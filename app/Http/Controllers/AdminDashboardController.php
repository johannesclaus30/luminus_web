<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\Event;
use App\Models\TracerResponse;
use App\Models\TracerForm;
use App\Models\Announcement;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. Verified Alumni Count
        $verifiedAlumniCount = Alumni::where('verification_status', 'verified')->count();

        // 2. Active Events
        $activeEventsCount = Event::where('status', 1)
            ->where('end_date', '>=', now()->toDateString())
            ->count();

        // 3. Total Tracer Responses
        $totalTracerResponses = TracerResponse::count();

        // 4. Event Locations for Map (PostgreSQL-compatible)
        $eventLocations = Event::select(
                'events.title', 'events.start_date', 'events.end_date',
                'venues.latitude', 'venues.longitude', 'venues.name as venue_name'
            )
            ->join('venues', 'events.venue_id', '=', 'venues.id')
            ->where('events.status', 1)
            ->get()
            ->map(fn($evt) => [
                'title'       => $evt->title,
                'start_date'  => $evt->start_date->format('M d, Y'),
                'end_date'    => $evt->end_date->format('M d, Y'),
                'latitude'    => $evt->latitude,
                'longitude'   => $evt->longitude,
                'venue_name'  => $evt->venue_name
            ]);

        // 5. Recent Announcements
        $recentAnnouncements = Announcement::where('status', 1)
            ->latest('created_at')
            ->take(3)
            ->get();

        // 6. Recent Tracer Forms
        $recentTracerForms = TracerForm::where('status', 1)
            ->latest('created_at')
            ->take(5)
            ->get();

        // 7. Upcoming Events with Registration Count (PostgreSQL-compatible)
        $upcomingEvents = Event::select('events.*', DB::raw('COUNT(event_registrations.id) as registration_count'))
            ->leftJoin('event_registrations', 'events.id', '=', 'event_registrations.event_id')
            ->where('events.status', 1)
            ->where('events.start_date', '>=', now()->toDateString())
            ->groupBy('events.id')
            ->orderBy('events.start_date', 'asc')
            ->take(5)
            ->get();

        // 8. Alumni by Year Graduated - POSTGRESQL VERSION ✅
        $alumniByYear = Alumni::selectRaw('EXTRACT(YEAR FROM year_graduated) as year, COUNT(*) as count')
            ->where('verification_status', 'verified')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'year' => (int)$item->year,
                'count' => (int)$item->count
            ]);

        // 9. Alumni by Program - POSTGRESQL VERSION ✅
        $alumniByProgram = Alumni::selectRaw('program, COUNT(*) as count')
            ->where('verification_status', 'verified')
            ->whereNotNull('program')
            ->where('program', '!=', '')
            ->groupBy('program')
            ->orderBy('count', 'desc')
            ->limit(8)
            ->get();

        // Prepare chart data (ensure arrays are clean)
        $chartData = [
            'years' => $alumniByYear->pluck('year')->filter()->values()->toArray(),
            'years_count' => $alumniByYear->pluck('count')->filter()->values()->toArray(),
            'programs' => $alumniByProgram->pluck('program')->filter()->values()->toArray(),
            'programs_count' => $alumniByProgram->pluck('count')->filter()->values()->toArray(),
        ];

        return view('admin_dashboard', compact(
            'verifiedAlumniCount',
            'activeEventsCount',
            'totalTracerResponses',
            'eventLocations',
            'recentAnnouncements',
            'recentTracerForms',
            'upcomingEvents',
            'chartData'
        ));
    }
}