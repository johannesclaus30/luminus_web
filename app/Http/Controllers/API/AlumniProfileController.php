<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\Reaction;
use App\Models\Follower;
use App\Models\Repost;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlumniProfileController extends Controller
{
    private const PHOTO_UPLOAD_COOLDOWN_SECONDS = 60;
    private const FOLLOW_STATUS_PENDING = Follower::STATUS_PENDING;
    private const FOLLOW_STATUS_CONNECTED = Follower::STATUS_CONNECTED;

    private function resolveConnectionStatus(Alumni $alumni, ?int $viewerId): string
    {
        if (!$viewerId || $viewerId === $alumni->id) {
            return 'none';
        }

        $outgoingStatus = DB::table('followers')
            ->where('follower_alumni_id', $viewerId)
            ->where('followed_alumni_id', $alumni->id)
            ->value('status');

        if ($outgoingStatus !== null) {
            return (int) $outgoingStatus === self::FOLLOW_STATUS_CONNECTED ? 'connected' : 'pending';
        }

        $incomingStatus = DB::table('followers')
            ->where('follower_alumni_id', $alumni->id)
            ->where('followed_alumni_id', $viewerId)
            ->value('status');

        if ($incomingStatus !== null) {
            return (int) $incomingStatus === self::FOLLOW_STATUS_CONNECTED ? 'connected' : 'pending';
        }

        return 'none';
    }

    private function resolveStorageUrl(Request $request, string $path): string
    {
        $normalizedPath = ltrim(trim($path), '/');

        $publicBaseUrl = rtrim((string) config('filesystems.disks.s3.url', ''), '/');
        $bucketName = trim((string) config('filesystems.disks.s3.bucket', ''), '/');

        if ($publicBaseUrl !== '' && $bucketName !== '') {
            return $publicBaseUrl . '/' . $bucketName . '/' . $normalizedPath;
        }

        return rtrim($request->getSchemeAndHttpHost(), '/') . '/' . $normalizedPath;
    }

    private function normalizeVisibility(?string $visibility): string
    {
        $normalizedVisibility = strtolower(trim((string) $visibility));

        return in_array($normalizedVisibility, ['public', 'private', 'friends'], true)
            ? $normalizedVisibility
            : 'public';
    }

    private function alumniAreConnected(int $firstAlumniId, int $secondAlumniId): bool
    {
        return DB::table('followers')
            ->where('follower_alumni_id', $firstAlumniId)
            ->where('followed_alumni_id', $secondAlumniId)
            ->where('status', Follower::STATUS_CONNECTED)
            ->exists()
            || DB::table('followers')
                ->where('follower_alumni_id', $secondAlumniId)
                ->where('followed_alumni_id', $firstAlumniId)
                ->where('status', Follower::STATUS_CONNECTED)
                ->exists();
    }

    private function canViewerSeePost(Post $post, ?int $viewerId): bool
    {
        if ($viewerId && $viewerId === (int) $post->alumni_id) {
            return true;
        }

        if ($post->is_draft) {
            return false;
        }

        return match ($this->normalizeVisibility($post->visibility ?? 'public')) {
            'public' => true,
            'private' => false,
            'friends' => $viewerId ? $this->alumniAreConnected((int) $post->alumni_id, $viewerId) : false,
            default => true,
        };
    }

    private function photoUploadCooldownResponse(Alumni $alumni)
    {
        $updatedAt = $alumni->updated_at;

        if (!$updatedAt) {
            return null;
        }

        $elapsedSeconds = $updatedAt->diffInSeconds(now());

        if ($elapsedSeconds >= self::PHOTO_UPLOAD_COOLDOWN_SECONDS) {
            return null;
        }

        $retryAfter = self::PHOTO_UPLOAD_COOLDOWN_SECONDS - $elapsedSeconds;

        return response()->json([
            'message' => 'Too many attempts. Please try again later.',
            'retry_after' => $retryAfter,
        ], 429);
    }

    private function extractStoragePath(?string $value): ?string
    {
        $trimmedValue = trim((string) $value);

        if ($trimmedValue === '') {
            return null;
        }

        $parsedPath = parse_url($trimmedValue, PHP_URL_PATH);
        $path = ltrim((string) ($parsedPath ?: $trimmedValue), '/');
        $bucketName = trim((string) config('filesystems.disks.s3.bucket', ''), '/');

        if ($bucketName !== '' && str_starts_with($path, $bucketName . '/')) {
            $path = substr($path, strlen($bucketName) + 1);
        }

        return $path !== '' ? $path : null;
    }

    private function buildAlumniPhotoPath(Alumni $alumni, string $extension): string
    {
        $normalizedExtension = strtolower(trim($extension));
        $normalizedExtension = preg_replace('/[^a-z0-9]+/', '', $normalizedExtension) ?: 'jpg';

        return sprintf('alumni_photos/%d/profile.%s', $alumni->id, $normalizedExtension);
    }

    private function alumniWithStats($alumni, ?int $viewerId = null): array
    {
        $payload = $alumni->toArray();
        $payload['posts_count'] = Post::query()
            ->where('alumni_id', $alumni->id)
            ->where('moderation_status', 'approved')
            ->count();
        $payload['connections_count'] = DB::table('followers')
            ->where(function ($query) use ($alumni) {
                $query->where('follower_alumni_id', $alumni->id)
                    ->orWhere('followed_alumni_id', $alumni->id);
            })
            ->where('status', Follower::STATUS_CONNECTED)
            ->count();
        $payload['connection_status'] = $this->resolveConnectionStatus($alumni, $viewerId);

        // Add work experiences
        $payload['work_experiences'] = DB::table('alumni_employments')
            ->where('alumni_id', $alumni->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => (int) $emp->id,
                    'title' => $emp->job_title,
                    'subtitle' => $emp->company,
                    'period' => $this->buildPeriodString($emp->start_date, $emp->end_date),
                    'startYear' => $emp->start_date ? (int) date('Y', strtotime($emp->start_date)) : null,
                    'endYear' => $emp->end_date ? (int) date('Y', strtotime($emp->end_date)) : null,
                    'location' => $emp->location,
                    'description' => $emp->career_description,
                ];
            })
            ->values()
            ->toArray();

        return $payload;
    }

    private function buildPeriodString($startDate, $endDate)
    {
        if (!$startDate && !$endDate) {
            return '';
        }
        $start = $startDate ? date('Y', strtotime($startDate)) : 'Present';
        $end = $endDate ? date('Y', strtotime($endDate)) : 'Present';
        return "{$start} - {$end}";
    }

    private function buildPostPayload(Post $post): array
    {
        return [
            'id' => $post->id,
            'feed_id' => sprintf('post-%d', $post->id),
            'feed_type' => 'post',
            'caption' => $post->caption,
            'visibility' => $post->visibility ?? 'public',
            'is_draft' => (bool) $post->is_draft,
            'created_at' => $post->created_at,
            'alumni' => [
                'id' => $post->alumni?->id,
                'first_name' => $post->alumni?->first_name,
                'last_name' => $post->alumni?->last_name,
                'alumni_photo' => $post->alumni?->alumni_photo,
            ],
            'comment_count' => $post->comments_count ?? 0,
            'reaction_count' => $post->reactions_count ?? 0,
            'repost_count' => $post->reposts_count ?? 0,
            'my_reaction' => null,
            'my_repost' => false,
            'images' => $post->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_path' => $image->image_path,
                ];
            })->values(),
        ];
    }

    private function buildRepostPayload(Repost $repost, ?int $viewerId = null): ?array
    {
        $originalPost = $repost->post;

        if (!$originalPost || !$this->canViewerSeePost($originalPost, $viewerId)) {
            return null;
        }

        return [
            'id' => $originalPost->id,
            'feed_id' => sprintf('repost-%d', $repost->id),
            'feed_type' => 'repost',
            'caption' => $repost->caption,
            'created_at' => $repost->created_at,
            'alumni' => [
                'id' => $repost->alumni?->id,
                'first_name' => $repost->alumni?->first_name,
                'last_name' => $repost->alumni?->last_name,
                'alumni_photo' => $repost->alumni?->alumni_photo,
            ],
            'comment_count' => $originalPost->comments()->count(),
            'reaction_count' => $originalPost->reactions()->count(),
            'repost_count' => $originalPost->reposts()->count(),
            'my_reaction' => null,
            'my_repost' => false,
            'images' => $originalPost->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_path' => $image->image_path,
                ];
            })->values(),
            'original_post' => [
                'id' => $originalPost->id,
                'caption' => $originalPost->caption,
                'visibility' => $originalPost->visibility ?? 'public',
                'is_draft' => (bool) $originalPost->is_draft,
                'created_at' => $originalPost->created_at,
                'alumni' => [
                    'id' => $originalPost->alumni?->id,
                    'first_name' => $originalPost->alumni?->first_name,
                    'last_name' => $originalPost->alumni?->last_name,
                    'alumni_photo' => $originalPost->alumni?->alumni_photo,
                ],
            ],
        ];
    }

    private function resolveImageUrl(Request $request, string $imagePath): string
    {
        $trimmedPath = trim($imagePath);

        if ($trimmedPath === '') {
            return $trimmedPath;
        }

        if (preg_match('/^https?:\/\//i', $trimmedPath)) {
            if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?/i', $trimmedPath)) {
                $parsedPath = parse_url($trimmedPath, PHP_URL_PATH) ?: '';
                $parsedQuery = parse_url($trimmedPath, PHP_URL_QUERY);

                $resolvedUrl = rtrim($request->getSchemeAndHttpHost(), '/') . '/' . ltrim($parsedPath, '/');

                if ($parsedQuery) {
                    $resolvedUrl .= '?' . $parsedQuery;
                }

                return $resolvedUrl;
            }

            return $trimmedPath;
        }

        $normalizedPath = ltrim($trimmedPath, '/');

        if (str_starts_with($normalizedPath, 'storage/')) {
            $normalizedPath = substr($normalizedPath, strlen('storage/'));
        }

        return rtrim($request->getSchemeAndHttpHost(), '/') . '/storage/' . ltrim($normalizedPath, '/');
    }

    public function show(Request $request)
    {
        $alumni = $request->user();

        return response()->json([
            'alumni' => $this->alumniWithStats($alumni, $alumni?->id),
        ]);
    }

    public function view(Request $request, Alumni $alumni)
    {
        $viewerId = $request->user()?->id;

        return response()->json([
            'alumni' => $this->alumniWithStats($alumni, $viewerId),
        ]);
    }

    public function follow(Request $request, Alumni $alumni)
    {
        $follower = $request->user();

        if (!$follower) {
            return response()->json([
                'message' => 'No active session found.',
            ], 401);
        }

        if ($follower->id === $alumni->id) {
            return response()->json([
                'message' => 'You cannot follow your own profile.',
            ], 422);
        }

        $existingFollow = DB::table('followers')
            ->where('follower_alumni_id', $follower->id)
            ->where('followed_alumni_id', $alumni->id)
            ->first();

        if ($existingFollow) {
            return response()->json([
                'message' => (int) $existingFollow->status === self::FOLLOW_STATUS_CONNECTED
                    ? 'You are already connected.'
                    : 'A follow request is already pending.',
                'followed' => false,
            ]);
        }

        $inserted = DB::table('followers')->insert([
            'follower_alumni_id' => $follower->id,
            'followed_alumni_id' => $alumni->id,
            'status' => self::FOLLOW_STATUS_PENDING,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => $inserted ? 'Follow request sent.' : 'Unable to send follow request.',
            'followed' => (bool) $inserted,
        ]);
    }

    public function unfollow(Request $request, Alumni $alumni)
    {
        $currentAlumni = $request->user();

        if (!$currentAlumni) {
            return response()->json([
                'message' => 'No active session found.',
            ], 401);
        }

        if ($currentAlumni->id === $alumni->id) {
            return response()->json([
                'message' => 'You cannot remove your own profile.',
            ], 422);
        }

        $deleted = DB::table('followers')
            ->where(function ($query) use ($currentAlumni, $alumni) {
                $query->where(function ($pair) use ($currentAlumni, $alumni) {
                    $pair->where('follower_alumni_id', $currentAlumni->id)
                        ->where('followed_alumni_id', $alumni->id);
                })->orWhere(function ($pair) use ($currentAlumni, $alumni) {
                    $pair->where('follower_alumni_id', $alumni->id)
                        ->where('followed_alumni_id', $currentAlumni->id);
                });
            })
            ->delete();

        if (!$deleted) {
            return response()->json([
                'message' => 'Connection not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Connection removed.',
            'removed' => true,
        ]);
    }

    public function acceptFollowRequest(Request $request, int $followRequestId)
    {
        $currentAlumniId = $request->user()?->id;

        if (!$currentAlumniId) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $followRequest = DB::table('followers')
            ->where('id', $followRequestId)
            ->where('followed_alumni_id', $currentAlumniId)
            ->first();

        if (!$followRequest) {
            return response()->json([
                'message' => 'Follow request not found.',
            ], 404);
        }

        if ((int) $followRequest->status === self::FOLLOW_STATUS_CONNECTED) {
            return response()->json([
                'message' => 'Follow request is already accepted.',
            ]);
        }

        DB::table('followers')
            ->where('id', $followRequestId)
            ->update([
                'status' => self::FOLLOW_STATUS_CONNECTED,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Follow request accepted.',
        ]);
    }

    public function declineFollowRequest(Request $request, int $followRequestId)
    {
        $currentAlumniId = $request->user()?->id;

        if (!$currentAlumniId) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $deleted = DB::table('followers')
            ->where('id', $followRequestId)
            ->where('followed_alumni_id', $currentAlumniId)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'message' => 'Follow request not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Follow request deleted.',
        ]);
    }

    public function contacts(Request $request)
    {
        $currentAlumniId = $request->user()?->id;

        if (!$currentAlumniId) {
            return response()->json([
                'contacts' => [],
            ]);
        }

        $contacts = DB::table('followers')
            ->join('alumnis', function ($join) use ($currentAlumniId) {
                $join->on('followers.follower_alumni_id', '=', 'alumnis.id')
                    ->where('followers.followed_alumni_id', '=', $currentAlumniId)
                    ->where('followers.status', '=', Follower::STATUS_CONNECTED);
            })
            ->select([
                'followers.id as connection_id',
                'alumnis.id as id',
                'alumnis.first_name',
                'alumnis.last_name',
                'alumnis.alumni_photo',
                'followers.created_at',
                DB::raw("(SELECT messages.is_read FROM messages WHERE messages.sender_id = alumnis.id AND messages.receiver_id = {$currentAlumniId} ORDER BY messages.created_at DESC LIMIT 1) as is_read"),
            ])
            ->union(
                DB::table('followers')
                    ->join('alumnis', function ($join) use ($currentAlumniId) {
                        $join->on('followers.followed_alumni_id', '=', 'alumnis.id')
                            ->where('followers.follower_alumni_id', '=', $currentAlumniId)
                            ->where('followers.status', '=', Follower::STATUS_CONNECTED);
                    })
                    ->select([
                        'followers.id as connection_id',
                        'alumnis.id as id',
                        'alumnis.first_name',
                        'alumnis.last_name',
                        'alumnis.alumni_photo',
                        'followers.created_at',
                        DB::raw("(SELECT messages.is_read FROM messages WHERE messages.sender_id = alumnis.id AND messages.receiver_id = {$currentAlumniId} ORDER BY messages.created_at DESC LIMIT 1) as is_read"),
                    ])
            )
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($contact) use ($request) {
                $contactPhoto = $contact->alumni_photo
                    ? $this->resolveStorageUrl($request, (string) $contact->alumni_photo)
                    : null;

                return [
                    'id' => (int) $contact->id,
                    'connection_id' => (int) $contact->connection_id,
                    'first_name' => $contact->first_name,
                    'last_name' => $contact->last_name,
                    'alumni_photo' => $contactPhoto,
                    'is_read' => is_null($contact->is_read) ? null : (bool) $contact->is_read,
                    'created_at' => $contact->created_at,
                ];
            })
            ->values();

        return response()->json([
            'contacts' => $contacts,
        ]);
    }

    public function update(Request $request)
    {
        $alumni = $request->user();

        $validated = $request->validate([
            'first_name' => 'sometimes|filled|string|max:255',
            'middle_name' => 'sometimes|nullable|string|max:255',
            'last_name' => 'sometimes|filled|string|max:255',
            'phone_number' => 'sometimes|nullable|string|max:50',
            'email' => 'sometimes|filled|string|email|max:255|unique:alumnis,email,' . $alumni->id,
            'date_of_birth' => 'sometimes|filled|date',
            'sex' => 'sometimes|filled|string|max:50',
            'alumni_photo' => 'sometimes|nullable|string|max:2048',
            'alumni_bio' => 'sometimes|nullable|string|max:10000',
        ]);

        $alumni->update($validated);

        $freshAlumni = $alumni->fresh();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'alumni' => $this->alumniWithStats($freshAlumni),
        ]);
    }

    public function uploadPhoto(Request $request)
    {
        $alumni = $request->user();

        if ($cooldownResponse = $this->photoUploadCooldownResponse($alumni)) {
            return $cooldownResponse;
        }

        $validated = $request->validate([
            'photo' => 'required|image|max:5120', // max 5MB
        ]);

        $file = $request->file('photo');
        $disk = Storage::disk('supabase');
        $path = $this->buildAlumniPhotoPath($alumni, $file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');

        $oldPath = $this->extractStoragePath($alumni->alumni_photo);
        if ($oldPath && $oldPath !== $path && $disk->exists($oldPath)) {
            $disk->delete($oldPath);
        }

        $storedPath = $disk->putFileAs(dirname($path), $file, basename($path));

        if (!$storedPath) {
            return response()->json([
                'message' => 'Unable to store uploaded photo.',
            ], 500);
        }

        $alumni->alumni_photo = $storedPath;
        $alumni->updated_at = now();
        $alumni->save();

        $freshAlumni = $alumni->fresh();

        return response()->json([
            'message' => 'Photo uploaded',
            'url' => $alumni->alumni_photo,
            'path' => $storedPath,
            'alumni' => $this->alumniWithStats($freshAlumni),
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->query('q', '');
        $currentAlumniId = $request->user()?->id;

        if (strlen(trim($query)) < 2) {
            $results = Alumni::query()
                ->where(function ($queryBuilder) use ($currentAlumniId) {
                    if ($currentAlumniId) {
                        $queryBuilder->where('id', '!=', $currentAlumniId);
                    }
                })
                ->limit(20)
                ->get()
                ->map(function ($alumni) use ($currentAlumniId) {
                    return [
                        'id' => $alumni->id,
                        'first_name' => $alumni->first_name,
                        'middle_name' => $alumni->middle_name,
                        'last_name' => $alumni->last_name,
                        'alumni_photo' => $alumni->alumni_photo,
                        'connection_status' => $this->resolveConnectionStatus($alumni, $currentAlumniId),
                    ];
                })
                ->toArray();

            return response()->json([
                'results' => $results,
            ]);
        }

        $searchTerm = "%{$query}%";

        $results = Alumni::query()
            ->when($currentAlumniId, function ($queryBuilder) use ($currentAlumniId) {
                $queryBuilder->where('id', '!=', $currentAlumniId);
            })
            ->where(function ($queryBuilder) use ($searchTerm) {
                $queryBuilder->whereRaw('LOWER(first_name) like LOWER(?)', [$searchTerm])
                    ->orWhereRaw('LOWER(middle_name) like LOWER(?)', [$searchTerm])
                    ->orWhereRaw('LOWER(last_name) like LOWER(?)', [$searchTerm]);
            })
            ->limit(20)
            ->get()
            ->map(function ($alumni) use ($currentAlumniId) {
                return [
                    'id' => $alumni->id,
                    'first_name' => $alumni->first_name,
                    'middle_name' => $alumni->middle_name,
                    'last_name' => $alumni->last_name,
                    'alumni_photo' => $alumni->alumni_photo,
                    'connection_status' => $this->resolveConnectionStatus($alumni, $currentAlumniId),
                ];
            })
            ->toArray();

        return response()->json([
            'results' => $results,
        ]);
    }

    public function posts(Request $request, Alumni $alumni)
    {
        $currentAlumniId = $request->user()?->id;

        $posts = Post::with([
            'alumni:id,first_name,last_name,alumni_photo',
            'images:id,post_id,image_path',
        ])
            ->withCount(['reactions', 'comments', 'reposts'])
            ->where('alumni_id', $alumni->id)
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn (Post $post) => $this->canViewerSeePost($post, $currentAlumniId))
            ->map(fn (Post $post) => $this->buildPostPayload($post));

        $reposts = Repost::with([
            'alumni:id,first_name,last_name,alumni_photo',
            'post.alumni:id,first_name,last_name,alumni_photo',
            'post.images:id,post_id,image_path',
            'post:id,alumni_id,caption,created_at,visibility,is_draft',
        ])
            ->where('alumni_id', $alumni->id)
            ->where('moderation_status', 'approved')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Repost $repost) => $this->buildRepostPayload($repost, $currentAlumniId))
            ->filter()
            ->values();

        $feedItems = $posts
            ->concat($reposts)
            ->sortByDesc(function (array $item) {
                return $item['created_at'];
            })
            ->take(50)
            ->values();

        if ($currentAlumniId) {
            $currentReactionMap = Reaction::query()
                ->whereIn('post_id', $feedItems->pluck('id')->unique()->values())
                ->where('alumni_id', $currentAlumniId)
                ->pluck('reaction', 'post_id');

            $currentRepostPostIds = Repost::query()
                ->whereIn('post_id', $feedItems->pluck('id')->unique()->values())
                ->where('alumni_id', $currentAlumniId)
                ->pluck('post_id')
                ->map(function ($postId) {
                    return (int) $postId;
                })
                ->all();

            $feedItems = $feedItems->map(function (array $item) use ($currentReactionMap, $currentRepostPostIds) {
                $item['my_reaction'] = $currentReactionMap->get($item['id']);
                $item['my_repost'] = in_array((int) $item['id'], $currentRepostPostIds, true);

                return $item;
            });
        }

        $feedItems = $feedItems->map(function (array $item) use ($request) {
            $item['images'] = collect($item['images'])->map(function (array $image) use ($request) {
                $image['image_url'] = $this->resolveImageUrl($request, $image['image_path']);

                return $image;
            })->values();

            return $item;
        });

        return response()->json([
            'posts' => $feedItems,
        ]);
    }

    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:5120', 
            'alumni_id' => 'required|exists:alumnis,id'
        ]);

        if ($request->hasFile('photo')) {
            $alumni = Alumni::find($request->alumni_id);

            if (!$alumni) {
                return response()->json(['message' => 'Alumni not found'], 404);
            }

            if ($cooldownResponse = $this->photoUploadCooldownResponse($alumni)) {
                return $cooldownResponse;
            }

            $disk = Storage::disk('supabase');
            $file = $request->file('photo');
            $path = $this->buildAlumniPhotoPath($alumni, $file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');

            $oldPath = $this->extractStoragePath($alumni->alumni_photo);
            if ($oldPath && $oldPath !== $path && $disk->exists($oldPath)) {
                $disk->delete($oldPath);
            }

            $storedPath = $disk->putFileAs(dirname($path), $file, basename($path));

            if (!$storedPath) {
                return response()->json(['message' => 'Unable to store uploaded photo.'], 500);
            }

            if ($oldPath && $oldPath !== $path && $disk->exists($oldPath)) {
                $disk->delete($oldPath);
            }

            $alumni->alumni_photo = $storedPath; 
            $alumni->updated_at = now();
            $alumni->save();

            return response()->json([
                'message' => 'Profile photo successfully updated!',
                'path' => $storedPath,
                'url' => $alumni->alumni_photo
            ]);
        }

        return response()->json(['message' => 'No file provided'], 400);
    }
}