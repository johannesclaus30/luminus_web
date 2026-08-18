<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\Event;
use App\Models\TracerResponse;
use App\Models\TracerForm;
use App\Models\Announcement;
use App\Models\Post;
use App\Models\Comment;
use App\Models\PostReport;
use App\Models\CommentReport;
use App\Models\EventRegistration;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with all analytics.
     */
    public function index()
    {
        // =========================================
        // 1. MODERATION DATA (Priority-based)
        // =========================================
        
        $reportedPosts = DB::table('post_reports')
        ->select(
            'post_reports.post_id as id',
            DB::raw('COUNT(post_reports.id) as report_count'),
            DB::raw('STRING_AGG(DISTINCT post_reports.reason, \', \') as report_reasons'),
            DB::raw('MAX(post_reports.created_at) as last_reported_at'),
            'posts.caption',
            'posts.alumni_id',
            'posts.created_at as post_created_at',
            'posts.moderation_status',
            'posts.is_hidden',
            'alumnis.first_name',
            'alumnis.last_name'
        )
        ->leftJoin('posts', 'post_reports.post_id', '=', 'posts.id')
        ->leftJoin('alumnis', 'posts.alumni_id', '=', 'alumnis.id')
        ->whereNotNull('post_reports.post_id')
        ->groupBy(
            'post_reports.post_id',
            'posts.caption',
            'posts.alumni_id',
            'posts.created_at',
            'posts.moderation_status',
            'posts.is_hidden',
            'alumnis.first_name',
            'alumnis.last_name'
        )
        ->havingRaw('COUNT(post_reports.id) > 0')
        ->orderByRaw('COUNT(post_reports.id) DESC, MAX(post_reports.created_at) DESC')
        ->get()
        ->map(function($item) {
            return (object) [
                'id' => $item->id,
                'caption' => $item->caption ?? 'Post deleted or not found',
                'alumni_id' => $item->alumni_id,
                'alumni' => $item->alumni_id ? (object) [
                    'first_name' => $item->first_name ?? 'Unknown',
                    'last_name' => $item->last_name ?? ''
                ] : null,
                'created_at' => $item->post_created_at ? Carbon::parse($item->post_created_at) : null, // ✅ Convert to Carbon
                'moderation_status' => $item->moderation_status ?? 'unknown',
                'is_hidden' => $item->is_hidden ?? false,
                'report_count' => (int)$item->report_count,
                'report_reasons' => $item->report_reasons,
                'last_reported_at' => $item->last_reported_at,
            ];
        });

            $reportedComments = DB::table('comment_reports')
        ->select(
            'comment_reports.comment_id as id',
            DB::raw('COUNT(comment_reports.id) as report_count'),
            DB::raw('STRING_AGG(DISTINCT comment_reports.reason, \', \') as report_reasons'),
            DB::raw('MAX(comment_reports.created_at) as last_reported_at'),
            'comments.comment',
            'comments.alumni_id',
            'comments.created_at as comment_created_at',
            'alumnis.first_name',
            'alumnis.last_name',
            'posts.caption as post_caption'
        )
        ->leftJoin('comments', 'comment_reports.comment_id', '=', 'comments.id')
        ->leftJoin('alumnis', 'comments.alumni_id', '=', 'alumnis.id')
        ->leftJoin('posts', 'comments.post_id', '=', 'posts.id')
        ->whereNotNull('comment_reports.comment_id')
        ->groupBy(
            'comment_reports.comment_id',
            'comments.comment',
            'comments.alumni_id',
            'comments.created_at',
            'alumnis.first_name',
            'alumnis.last_name',
            'posts.caption'
        )
        ->havingRaw('COUNT(comment_reports.id) > 0')
        ->orderByRaw('COUNT(comment_reports.id) DESC, MAX(comment_reports.created_at) DESC')
        ->get()
        ->map(function($item) {
            return (object) [
                'id' => $item->id,
                'comment' => $item->comment ?? 'Comment deleted or not found',
                'alumni_id' => $item->alumni_id,
                'alumni' => $item->alumni_id ? (object) [
                    'first_name' => $item->first_name ?? 'Unknown',
                    'last_name' => $item->last_name ?? ''
                ] : null,
                'created_at' => $item->comment_created_at ? Carbon::parse($item->comment_created_at) : null, // ✅ Convert to Carbon
                'post_caption' => $item->post_caption,
                'report_count' => (int)$item->report_count,
                'report_reasons' => $item->report_reasons,
                'last_reported_at' => $item->last_reported_at,
            ];
        });

        // Total pending reports count
        $totalReports = PostReport::count() + CommentReport::count();

        // =========================================
        // FREQUENT VIOLATORS - MOVED HERE (BEFORE return view)
        // =========================================

        // Get alumni with the most reports (combined post + comment reports)
        $frequentViolators = DB::table('alumnis')
            ->select(
                'alumnis.id',
                'alumnis.first_name',
                'alumnis.last_name',
                'alumnis.email',
                'alumnis.alumni_photo',
                'alumnis.account_status',
                'alumnis.created_at',
                DB::raw('COUNT(DISTINCT post_reports.id) as post_reports'),
                DB::raw('COUNT(DISTINCT comment_reports.id) as comment_reports'),
                DB::raw('(COUNT(DISTINCT post_reports.id) + COUNT(DISTINCT comment_reports.id)) as total_reports'),
                DB::raw('STRING_AGG(DISTINCT post_reports.reason, \', \') as report_reasons')
            )
            ->leftJoin('posts', 'alumnis.id', '=', 'posts.alumni_id')
            ->leftJoin('post_reports', 'posts.id', '=', 'post_reports.post_id')
            ->leftJoin('comments', 'alumnis.id', '=', 'comments.alumni_id')
            ->leftJoin('comment_reports', 'comments.id', '=', 'comment_reports.comment_id')
            ->where(function($query) {
                $query->whereNotNull('post_reports.id')
                    ->orWhereNotNull('comment_reports.id');
            })
            ->groupBy(
                'alumnis.id',
                'alumnis.first_name',
                'alumnis.last_name',
                'alumnis.email',
                'alumnis.alumni_photo',
                'alumnis.account_status',
                'alumnis.created_at'
            )
            ->havingRaw('(COUNT(DISTINCT post_reports.id) + COUNT(DISTINCT comment_reports.id)) > 0')
            ->orderByRaw('(COUNT(DISTINCT post_reports.id) + COUNT(DISTINCT comment_reports.id)) DESC')
            ->limit(10)
            ->get()
            ->map(function($item) {
                // Convert created_at to Carbon instance
                $item->created_at = $item->created_at ? Carbon::parse($item->created_at) : null;
                return $item;
            });

        // =========================================
        // 2. USER ANALYTICS
        // =========================================

        $verifiedAlumniCount = Alumni::where('verification_status', 'verified')->count();
        $pendingVerificationCount = Alumni::where('verification_status', 'pending')->count();

        // User growth (last 12 months)
        $userGrowth = Alumni::select(
                DB::raw('EXTRACT(YEAR FROM created_at) as year'),
                DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(fn($item) => [
                'month' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                'count' => (int)$item->count
            ]);

        // Alumni by program
        $alumniByProgram = Alumni::selectRaw('program, COUNT(*) as count')
            ->where('verification_status', 'verified')
            ->whereNotNull('program')
            ->where('program', '!=', '')
            ->groupBy('program')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Alumni by year graduated
        $alumniByYear = Alumni::selectRaw('EXTRACT(YEAR FROM year_graduated) as year, COUNT(*) as count')
            ->where('verification_status', 'verified')
            ->whereNotNull('year_graduated')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->limit(15)
            ->get()
            ->map(fn($item) => [
                'year' => (int)$item->year,
                'count' => (int)$item->count
            ]);

        // Alumni by type (SHS vs College)
        $alumniByType = Alumni::selectRaw('alumni_type, COUNT(*) as count')
            ->where('verification_status', 'verified')
            ->whereNotNull('alumni_type')
            ->groupBy('alumni_type')
            ->get();

        // Alumni by region (from addresses)
        $alumniByRegion = Address::selectRaw('region, COUNT(DISTINCT alumni_id) as count')
            ->groupBy('region')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // =========================================
        // 3. TRACER ANALYTICS
        // =========================================

        $totalTracerResponses = TracerResponse::count();
        $completedTracerResponses = TracerResponse::where('status', 'completed')->count();
        $inProgressTracerResponses = TracerResponse::where('status', 'in_progress')->count();

        $alumniWithTracer = TracerResponse::where('status', 'completed')
            ->distinct('alumni_id')
            ->count('alumni_id');
        $tracerCompletionRate = $verifiedAlumniCount > 0 
            ? round(($alumniWithTracer / $verifiedAlumniCount) * 100, 1) 
            : 0;

        $tracerByForm = TracerForm::select(
                'tracer_forms.form_title',
                DB::raw('COUNT(tracer_responses.id) as response_count')
            )
            ->leftJoin('tracer_responses', 'tracer_forms.id', '=', 'tracer_responses.form_id')
            ->where('tracer_forms.status', 1)
            ->groupBy('tracer_forms.id', 'tracer_forms.form_title')
            ->orderBy('response_count', 'desc')
            ->get();

        $tracerOverTime = TracerResponse::select(
                DB::raw('EXTRACT(YEAR FROM created_at) as year'),
                DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(fn($item) => [
                'month' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                'count' => (int)$item->count
            ]);

        // =========================================
        // 4. EVENT ANALYTICS
        // =========================================

        $totalEvents = Event::count();
        $activeEvents = Event::where('status', 1)
            ->where('end_date', '>=', now()->toDateString())
            ->count();
        $upcomingEvents = Event::where('status', 1)
            ->where('start_date', '>=', now()->toDateString())
            ->count();
        $totalRegistrations = EventRegistration::count();

        $topEvents = Event::select(
                'events.title',
                DB::raw('COUNT(event_registrations.id) as registration_count')
            )
            ->leftJoin('event_registrations', 'events.id', '=', 'event_registrations.event_id')
            ->where('events.status', 1)
            ->groupBy('events.id', 'events.title')
            ->orderBy('registration_count', 'desc')
            ->limit(5)
            ->get();

        $registrationsOverTime = EventRegistration::select(
                DB::raw('EXTRACT(YEAR FROM created_at) as year'),
                DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(fn($item) => [
                'month' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                'count' => (int)$item->count
            ]);

        // =========================================
        // 5. RECENT ACTIVITIES
        // =========================================

        $recentAnnouncements = Announcement::where('status', 1)
            ->latest('created_at')
            ->take(3)
            ->get();

        $recentTracerForms = TracerForm::where('status', 1)
            ->latest('created_at')
            ->take(5)
            ->get();

        // =========================================
        // 6. CHART DATA
        // =========================================

        $chartData = [
            'user_growth' => [
                'labels' => $userGrowth->pluck('month')->values()->toArray(),
                'data' => $userGrowth->pluck('count')->values()->toArray(),
            ],
            'alumni_by_program' => [
                'labels' => $alumniByProgram->pluck('program')->filter()->values()->toArray(),
                'data' => $alumniByProgram->pluck('count')->filter()->values()->toArray(),
            ],
            'alumni_by_year' => [
                'labels' => $alumniByYear->pluck('year')->filter()->values()->toArray(),
                'data' => $alumniByYear->pluck('count')->filter()->values()->toArray(),
            ],
            'tracer_over_time' => [
                'labels' => $tracerOverTime->pluck('month')->values()->toArray(),
                'data' => $tracerOverTime->pluck('count')->values()->toArray(),
            ],
            'tracer_by_form' => [
                'labels' => $tracerByForm->pluck('form_title')->filter()->values()->toArray(),
                'data' => $tracerByForm->pluck('response_count')->filter()->values()->toArray(),
            ],
            'registrations_over_time' => [
                'labels' => $registrationsOverTime->pluck('month')->values()->toArray(),
                'data' => $registrationsOverTime->pluck('count')->values()->toArray(),
            ],
            'top_events' => [
                'labels' => $topEvents->pluck('title')->filter()->values()->toArray(),
                'data' => $topEvents->pluck('registration_count')->filter()->values()->toArray(),
            ],
            'alumni_by_type' => [
                'labels' => $alumniByType->pluck('alumni_type')->filter()->values()->toArray(),
                'data' => $alumniByType->pluck('count')->filter()->values()->toArray(),
            ],
            'alumni_by_region' => [
                'labels' => $alumniByRegion->pluck('region')->filter()->values()->toArray(),
                'data' => $alumniByRegion->pluck('count')->filter()->values()->toArray(),
            ],
        ];

      // In your controller's index method, replace the alumniLocations section with this:

// Get all alumni locations with deduplication (one marker per alumni)
$alumniLocations = DB::table('alumnis')
    ->select(
        'alumnis.id',
        'alumnis.first_name',
        'alumnis.last_name',
        'alumnis.alumni_photo',
        'addresses.latitude',
        'addresses.longitude',
        'addresses.address_type',
        'addresses.region',
        'addresses.province',
        'addresses.municipality',
        'addresses.barangay'
    )
    ->join('addresses', 'alumnis.id', '=', 'addresses.alumni_id')
    ->whereNotNull('addresses.latitude')
    ->whereNotNull('addresses.longitude')
    ->where('addresses.latitude', '!=', 0)
    ->where('addresses.longitude', '!=', 0)
    ->where('alumnis.verification_status', 'verified')
    ->where('alumnis.account_status', 1)
    ->whereBetween('addresses.latitude', [-90, 90])
    ->whereBetween('addresses.longitude', [-180, 180])
    // Remove duplicate addresses for the same alumni (keep the first one)
    ->orderBy('alumnis.id')
    ->orderBy('addresses.id')
    ->get()
    // Group by alumni_id to ensure one marker per alumni
    ->groupBy('id')
    ->map(function($group) {
        $first = $group->first();
        return (object) [
            'id' => (int)$first->id,
            'first_name' => $first->first_name,
            'last_name' => $first->last_name,
            'alumni_photo' => $first->alumni_photo,
            'latitude' => (float)$first->latitude,
            'longitude' => (float)$first->longitude,
            'address_type' => $first->address_type,
            'region' => $first->region,
            'province' => $first->province,
            'municipality' => $first->municipality,
            'barangay' => $first->barangay,
        ];
    })
    ->values(); // Reset array keys

// Debug: Log the number of alumni locations found
\Log::info('Alumni locations count: ' . $alumniLocations->count());

// Add detailed debug for each location
$debugLocations = [];
foreach ($alumniLocations as $location) {
    $debugLocations[] = [
        'id' => $location->id,
        'name' => $location->first_name . ' ' . $location->last_name,
        'latitude' => $location->latitude,
        'longitude' => $location->longitude,
    ];
}
\Log::info('Location details:', $debugLocations);

if ($alumniLocations->count() === 0) {
    // Debug: Check if there are any addresses with coordinates
    $totalAddresses = DB::table('addresses')->count();
    $addressesWithCoords = DB::table('addresses')
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->where('latitude', '!=', 0)
        ->where('longitude', '!=', 0)
        ->count();
    
    $verifiedAlumniWithCoords = DB::table('addresses')
        ->join('alumnis', 'addresses.alumni_id', '=', 'alumnis.id')
        ->whereNotNull('addresses.latitude')
        ->whereNotNull('addresses.longitude')
        ->where('addresses.latitude', '!=', 0)
        ->where('addresses.longitude', '!=', 0)
        ->where('alumnis.verification_status', 'verified')
        ->where('alumnis.account_status', 1)
        ->distinct('alumnis.id')
        ->count();
    
    \Log::info("Total addresses: {$totalAddresses}");
    \Log::info("Addresses with coordinates: {$addressesWithCoords}");
    \Log::info("Verified alumni with coordinates: {$verifiedAlumniWithCoords}");
    
    // Check if there are any addresses with valid coordinates range
    $validRangeCount = DB::table('addresses')
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->where('latitude', '!=', 0)
        ->where('longitude', '!=', 0)
        ->whereBetween('latitude', [-90, 90])
        ->whereBetween('longitude', [-180, 180])
        ->count();
    
    \Log::info("Addresses with valid coordinate range: {$validRangeCount}");
}

        return view('admin_dashboard', compact(
            'reportedPosts',
            'reportedComments',
            'totalReports',
            'verifiedAlumniCount',
            'pendingVerificationCount',
            'alumniByType',
            'alumniByRegion',
            'userGrowth',
            'totalTracerResponses',
            'completedTracerResponses',
            'inProgressTracerResponses',
            'tracerCompletionRate',
            'tracerByForm',
            'tracerOverTime',
            'totalEvents',
            'activeEvents',
            'upcomingEvents',
            'totalRegistrations',
            'topEvents',
            'registrationsOverTime',
            'recentAnnouncements',
            'recentTracerForms',
            'chartData',
            'alumniByProgram',
            'alumniByYear',
            'frequentViolators',
            'alumniLocations'
        ));
    }


    // ============================================
    // MODERATION ACTION METHODS
    // ============================================

    /**
     * Moderate a post (approve, hide, or delete)
     */
    public function moderatePost(Request $request)
    {
        $request->validate([
        'id' => 'required|exists:posts,id',
        'action' => 'required|in:approve,hide,delete'
    ]);

    $post = Post::find($request->id);

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        try {
            switch ($request->action) {
                case 'approve':
                    $post->moderation_status = 'approved';
                    $post->is_hidden = false;
                    PostReport::where('post_id', $post->id)->delete();
                    break;

                case 'hide':
                    $post->is_hidden = true;
                    $post->moderation_status = 'pending';
                    break;

                case 'delete':
                    $post->delete();
                    PostReport::where('post_id', $request->id)->delete();
                    return response()->json([
                        'success' => true,
                        'message' => 'Post deleted successfully'
                    ]);
            }

            $post->save();

            return response()->json([
                'success' => true,
                'message' => 'Post moderated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Moderate a comment (approve or delete)
     */
    public function moderateComment(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:comments,id',
            'action' => 'required|in:approve,delete'
        ]);

        $comment = Comment::find($request->id);

        if (!$comment) {
            return response()->json(['success' => false, 'message' => 'Comment not found'], 404);
        }

        try {
            switch ($request->action) {
                case 'approve':
                    $comment->moderation_status = 'approved';
                    CommentReport::where('comment_id', $comment->id)->delete();
                    break;

                case 'delete':
                    $comment->delete();
                    CommentReport::where('comment_id', $request->id)->delete();
                    return response()->json([
                        'success' => true,
                        'message' => 'Comment deleted successfully'
                    ]);
            }

            $comment->save();

            return response()->json([
                'success' => true,
                'message' => 'Comment moderated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restrict/Suspend an alumni user
     */
    public function restrictUser(Request $request)
    {
        $request->validate([
            'alumni_id' => 'required|exists:alumnis,id'
        ]);

        $alumni = Alumni::find($request->alumni_id);

        if (!$alumni) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        try {
            $alumni->account_status = $alumni->account_status == 1 ? 0 : 1;
            $alumni->save();

            PostReport::where('reporter_id', $alumni->id)->delete();
            CommentReport::where('reporter_id', $alumni->id)->delete();

            $status = $alumni->account_status == 1 ? 'restored' : 'restricted';

            return response()->json([
                'success' => true,
                'message' => "User {$status} successfully",
                'status' => $alumni->account_status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View a single post (for admin review) - Redirects to the dedicated view page
     */
    public function viewPost($id)
    {
        // Redirect to the dedicated view post page
        return redirect()->route('admin.posts.view', $id);
    }

    /**
     * View a single comment (for admin review)
     */
    public function viewComment($id)
    {
        $comment = Comment::with(['alumni', 'post'])
            ->find($id);

        if (!$comment) {
            abort(404, 'Comment not found');
        }

        $reports = CommentReport::with('reporter')
            ->where('comment_id', $id)
            ->get();

        if (request()->wantsJson()) {
            return response()->json([
                'comment' => $comment,
                'reports' => $reports,
                'report_count' => $reports->count()
            ]);
        }

        return view('admin.comments.view', compact('comment', 'reports'));
    }

    /**
     * Get all reports for a specific post (AJAX)
     */
    public function getPostReports($id)
    {
        $reports = PostReport::with('reporter')
            ->where('post_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'reports' => $reports,
            'count' => $reports->count()
        ]);
    }

    /**
     * Get all reports for a specific comment (AJAX)
     */
    public function getCommentReports($id)
    {
        $reports = CommentReport::with('reporter')
            ->where('comment_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'reports' => $reports,
            'count' => $reports->count()
        ]);
    }

    /**
     * Bulk moderate multiple posts
     */
    public function bulkModeratePosts(Request $request)
    {
        $request->validate([
            'post_ids' => 'required|array',
            'post_ids.*' => 'exists:posts,id',
            'action' => 'required|in:approve,hide,delete'
        ]);

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($request->post_ids as $postId) {
            try {
                $post = Post::find($postId);
                if (!$post) continue;

                switch ($request->action) {
                    case 'approve':
                        $post->moderation_status = 'approved';
                        $post->is_hidden = false;
                        PostReport::where('post_id', $postId)->delete();
                        break;
                    case 'hide':
                        $post->is_hidden = true;
                        $post->moderation_status = 'pending';
                        break;
                    case 'delete':
                        $post->delete();
                        PostReport::where('post_id', $postId)->delete();
                        break;
                }

                if ($request->action !== 'delete') {
                    $post->save();
                }
                $results['success']++;

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Post ID {$postId}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => "Processed {$results['success']} posts successfully, {$results['failed']} failed"
        ]);
    }

    /**
     * Bulk moderate multiple comments
     */
    public function bulkModerateComments(Request $request)
    {
        $request->validate([
            'comment_ids' => 'required|array',
            'comment_ids.*' => 'exists:comments,id',
            'action' => 'required|in:approve,delete'
        ]);

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($request->comment_ids as $commentId) {
            try {
                $comment = Comment::find($commentId);
                if (!$comment) continue;

                switch ($request->action) {
                    case 'approve':
                        $comment->moderation_status = 'approved';
                        CommentReport::where('comment_id', $commentId)->delete();
                        break;
                    case 'delete':
                        $comment->delete();
                        CommentReport::where('comment_id', $commentId)->delete();
                        break;
                }

                if ($request->action !== 'delete') {
                    $comment->save();
                }
                $results['success']++;

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Comment ID {$commentId}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => "Processed {$results['success']} comments successfully, {$results['failed']} failed"
        ]);
    }

    public function fetchCoordinates($barangay = null, $municipality = null, $province = null, $region = null, $zipCode = null)
    {
        $baseUrl = 'https://nominatim.openstreetmap.org/search';

        // Build progressive search queries from most specific to broad
        $attempts = [
            // 1. Street / Barangay + Municipality + Province + Region
            implode(', ', array_filter([$barangay, $municipality, $province, $region, $zipCode])),

            // 2. Municipality/City + Province/State
            implode(', ', array_filter([$municipality, $province])),

            // 3. Municipality/City + Region/Country
            implode(', ', array_filter([$municipality, $region])),

            // 4. Province/State or Region alone
            implode(', ', array_filter([$province, $region]))
        ];

        // Remove duplicates and empty strings
        $attempts = array_unique(array_filter($attempts));

        foreach ($attempts as $query) {
            $cleanQuery = trim($query);
            if (empty($cleanQuery)) continue;

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'LumiNUs-Alumni-Portal/1.0 (contact@luminus.edu.ph)'
                ])->timeout(5)->get($baseUrl, [
                    'q' => $cleanQuery,
                    'format' => 'json',
                    'limit' => 1,
                    // 'countrycodes' parameter removed to allow global searches
                ]);

                $data = $response->json();

                if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                    return [
                        'latitude' => (float)$data[0]['lat'],
                        'longitude' => (float)$data[0]['lon']
                    ];
                }
            } catch (\Exception $e) {
                \Log::warning("Geocoding failed for query '{$cleanQuery}': " . $e->getMessage());
            }
        }

        return [
            'latitude' => 0,
            'longitude' => 0
        ];
    }

    /**
     * Display a single post for moderation preview
     */
    public function viewPostPage($id)
    {
        $post = \App\Models\Post::with([
            'alumni',
            'images',
            'comments.alumni',
            'reactions'
        ])->findOrFail($id);
        
        // Get report count and reasons (if any)
        $post->report_count = $post->reports()->count();
        $post->report_reasons = $post->reports()->pluck('reason')->implode(', ');
        $post->reported_at = $post->reports()->first()?->created_at;
        
        return view('view-post', compact('post'));
    }
}