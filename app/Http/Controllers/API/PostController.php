<?php

namespace App\Http\Controllers;

use App\Models\ImagesPost;
use App\Models\DismissedNotification;
use App\Models\Comment;
use App\Models\Follower;
use App\Models\Repost;
use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    private function buildNotificationActor(array $source): array
    {
        return [
            'id' => $source['alumni']?->id,
            'first_name' => $source['alumni']?->first_name,
            'last_name' => $source['alumni']?->last_name,
            'alumni_photo' => $source['alumni']?->alumni_photo,
        ];
    }

    private function buildNotificationKey(string $type, int $id): string
    {
        return sprintf('%s-%d', $type, $id);
    }

    private function getDismissedNotificationKeys(int $alumniId)
    {
        return DismissedNotification::query()
            ->where('alumni_id', $alumniId)
            ->pluck('notification_key')
            ->all();
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

    private function storePostImages(Post $post, array $imageFiles): void
    {
        foreach ($imageFiles as $imageFile) {
            $imageExtension = $imageFile->getClientOriginalExtension() ?: $imageFile->extension() ?: 'jpg';
            $imageName = sprintf('post-%d-%s.%s', $post->id, uniqid('', true), $imageExtension);
            $storedPath = $imageFile->storeAs('post_images', $imageName, 'supabase');

            ImagesPost::create([
                'post_id' => $post->id,
                'image_path' => $storedPath,
            ]);
        }
    }

    private function replacePostImages(Post $post, array $imageFiles): void
    {
        $existingImagePaths = $post->images()->pluck('image_path')->filter()->all();

        if (!empty($existingImagePaths)) {
            Storage::disk('supabase')->delete($existingImagePaths);
        }

        $post->images()->delete();
        $this->storePostImages($post, $imageFiles);
    }

    private function buildPostFeedItem(Post $post): array
    {
        return [
            'id' => $post->id,
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
            'images' => $post->images->map(function (ImagesPost $image) {
                return [
                    'id' => $image->id,
                    'image_path' => $image->image_path,
                ];
            })->values(),
        ];
    }

    private function buildRepostFeedItem(Repost $repost, ?int $viewerId = null): ?array
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
            'comment_count' => $originalPost->comments_count ?? 0,
            'reaction_count' => $originalPost->reactions_count ?? 0,
            'repost_count' => $originalPost->reposts_count ?? 0,
            'my_reaction' => null,
            'my_repost' => false,
            'images' => $originalPost->images->map(function (ImagesPost $image) {
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

    private function buildAnnouncementFeedItem(object $announcement, array $images): array
    {
        return [
            'id' => $announcement->id,
            'feed_id' => sprintf('announcement-%d', $announcement->id),
            'feed_type' => 'announcement',
            'caption' => $announcement->announcement_description,
            'announcement_title' => $announcement->announcement_title,
            'announcement_description' => $announcement->announcement_description,
            'created_at' => $announcement->date_posted,
            'author' => [
                'id' => $announcement->admin_id,
                'first_name' => $announcement->admin_first_name,
                'middle_name' => $announcement->admin_middle_name,
                'last_name' => $announcement->admin_last_name,
                'alumni_photo' => $announcement->admin_photo,
            ],
            'comment_count' => 0,
            'reaction_count' => 0,
            'repost_count' => 0,
            'my_reaction' => null,
            'my_repost' => false,
            'images' => collect($images[$announcement->id] ?? [])->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_path' => $image->image_path,
                ];
            })->values(),
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

        if (
            str_starts_with($normalizedPath, 'post_images/')
            || str_starts_with($normalizedPath, 'announcement_images/')
            || str_starts_with($normalizedPath, 'announcements_images/')
            || str_starts_with($normalizedPath, 'announcements_images/')
        ) {
            $baseUrl = rtrim((string) config('filesystems.disks.supabase.url'), '/');
            $bucket = trim((string) config('filesystems.disks.supabase.bucket', 'luminus_assets'), '/');

            if ($baseUrl !== '' && $bucket !== '') {
                return sprintf('%s/%s/%s', $baseUrl, $bucket, ltrim($normalizedPath, '/'));
            }

            return rtrim($request->getSchemeAndHttpHost(), '/') . '/storage/' . ltrim($normalizedPath, '/');
        }

        return rtrim($request->getSchemeAndHttpHost(), '/') . '/storage/' . ltrim($normalizedPath, '/');
    }

    public function index(Request $request)
    {
        $currentAlumniId = $request->user()?->id;

        $posts = Post::with([
            'alumni:id,first_name,last_name,alumni_photo',
            'images:id,post_id,image_path',
        ])
            ->withCount(['reactions', 'comments', 'reposts'])
            ->where('moderation_status', 'approved')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function (Post $post) use ($currentAlumniId) {
                return $this->canViewerSeePost($post, $currentAlumniId)
                    ? $this->buildPostFeedItem($post)
                    : null;
            })
            ->filter()
            ->values();

        $reposts = Repost::with([
            'alumni:id,first_name,last_name,alumni_photo',
            'post.alumni:id,first_name,last_name,alumni_photo',
            'post.images:id,post_id,image_path',
            'post:id,alumni_id,caption,created_at,visibility,is_draft',
        ])
            ->where('moderation_status', 'approved')
            ->whereHas('post', function ($query) {
                $query->where('moderation_status', 'approved');
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (Repost $repost) => $this->buildRepostFeedItem($repost, $currentAlumniId))
            ->filter()
            ->values();

        $announcements = DB::table('announcements as announcement')
            ->leftJoin('admins as admin', 'admin.id', '=', 'announcement.admin_id')
            ->orderByDesc('announcement.date_posted')
            ->limit(50)
            ->get([
                'announcement.id',
                'announcement.admin_id',
                'announcement.title as announcement_title',
                'announcement.announcement_description',
                'announcement.date_posted',
                'admin.admin_first_name',
                'admin.admin_middle_name',
                'admin.admin_last_name',
                'admin.photo as admin_photo',
            ]);

        $announcementImages = DB::table('images_announcements')
            ->whereIn('announcement_id', $announcements->pluck('id')->all())
            ->orderBy('id')
            ->get()
            ->groupBy('announcement_id');

        $announcementFeedItems = $announcements
            ->map(fn (object $announcement) => $this->buildAnnouncementFeedItem($announcement, $announcementImages->all()))
            ->values();

        $feedItems = $posts
            ->concat($reposts)
            ->concat($announcementFeedItems)
            ->sortByDesc(function (array $item) {
                return $item['created_at'];
            })
            ->take(50)
            ->values();

        if ($currentAlumniId) {
            $reactableFeedItemIds = $feedItems
                ->filter(function (array $item) {
                    return ($item['feed_type'] ?? 'post') !== 'announcement';
                })
                ->pluck('id')
                ->unique()
                ->values();

            $currentReactionMap = Reaction::query()
                ->whereIn('post_id', $reactableFeedItemIds)
                ->where('alumni_id', $currentAlumniId)
                ->pluck('reaction', 'post_id');

            $currentRepostPostIds = Repost::query()
                ->whereIn('post_id', $reactableFeedItemIds)
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'caption' => 'nullable|string|max:10000',
            'visibility' => 'nullable|in:public,private,friends',
            'is_draft' => 'sometimes|boolean',
            'remove_image_ids' => 'sometimes|array|max:10',
            'remove_image_ids.*' => 'integer',
            'images' => 'sometimes|array|max:10',
            'images.*' => 'image|max:5120',
        ]);

        $isDraft = $request->boolean('is_draft');
        $visibility = $this->normalizeVisibility($validated['visibility'] ?? 'public');

        $hasImages = $request->hasFile('images') && !empty($request->file('images', []));
        $caption = isset($validated['caption']) ? trim($validated['caption']) : null;

        if ($caption === '') {
            $caption = null;
        }

        if (!$isDraft && $caption === null && !$hasImages) {
            return response()->json([
                'message' => 'Add text or media before publishing a post.',
            ], 422);
        }

        $post = DB::transaction(function () use ($request, $validated) {
            $caption = isset($validated['caption']) ? trim($validated['caption']) : null;
            $visibility = $this->normalizeVisibility($validated['visibility'] ?? 'public');
            $isDraft = $request->boolean('is_draft');

            $post = Post::create([
                'alumni_id' => $request->user()->id,
                'caption' => $caption,
                'visibility' => $visibility,
                'is_draft' => $isDraft,
                'moderation_status' => 'approved',
            ]);

            $this->storePostImages($post, $request->file('images', []));

            return $post->load('images');
        });

        $images = $post->images->map(function (ImagesPost $image) use ($request) {
            return [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'image_url' => $this->resolveImageUrl($request, $image->image_path),
            ];
        })->values();

        return response()->json([
            'message' => 'Post created successfully.',
            'post' => [
                'id' => $post->id,
                'caption' => $post->caption,
                'visibility' => $post->visibility ?? 'public',
                'is_draft' => (bool) $post->is_draft,
                'created_at' => $post->created_at,
                'alumni' => [
                    'id' => $post->alumni_id,
                    'first_name' => $request->user()->first_name,
                    'last_name' => $request->user()->last_name,
                    'alumni_photo' => $request->user()->alumni_photo,
                ],
                'comment_count' => 0,
                'reaction_count' => 0,
                'repost_count' => 0,
                'my_reaction' => null,
                'my_repost' => false,
                'images' => $images,
            ],
        ], 201);
    }

    public function update(Request $request, Post $post)
    {
        $currentAlumniId = $request->user()?->id;

        if (!$currentAlumniId || (int) $post->alumni_id !== $currentAlumniId) {
            return response()->json([
                'message' => 'You do not have permission to edit this post.',
            ], 403);
        }

        $validated = $request->validate([
            'caption' => 'nullable|string|max:10000',
            'visibility' => 'nullable|in:public,private,friends',
            'is_draft' => 'sometimes|boolean',
            'remove_image_ids' => 'sometimes|array|max:10',
            'remove_image_ids.*' => 'integer',
            'images' => 'sometimes|array|max:10',
            'images.*' => 'image|max:5120',
        ]);

        $caption = array_key_exists('caption', $validated) ? trim((string) $validated['caption']) : $post->caption;

        if ($caption === '') {
            $caption = null;
        }

        $visibility = $this->normalizeVisibility($validated['visibility'] ?? $post->visibility ?? 'public');
        $isDraft = $request->has('is_draft') ? $request->boolean('is_draft') : (bool) $post->is_draft;
        $removedImageIds = collect($validated['remove_image_ids'] ?? [])
            ->map(fn ($imageId) => (int) $imageId)
            ->filter(fn ($imageId) => $imageId > 0)
            ->unique()
            ->values()
            ->all();
        $hasImages = $request->hasFile('images') && !empty($request->file('images', []));
        $remainingExistingImageCount = $post->images()->when(!empty($removedImageIds), function ($query) use ($removedImageIds) {
            $query->whereNotIn('id', $removedImageIds);
        })->count();

        if (!$isDraft && $caption === null && !$hasImages && $remainingExistingImageCount === 0) {
            return response()->json([
                'message' => 'Add text or media before publishing a post.',
            ], 422);
        }

        DB::transaction(function () use ($post, $caption, $visibility, $isDraft, $request, $hasImages, $removedImageIds) {
            $post->update([
                'caption' => $caption,
                'visibility' => $visibility,
                'is_draft' => $isDraft,
            ]);

            if (!empty($removedImageIds)) {
                $imagesToRemove = $post->images()->whereIn('id', $removedImageIds)->get();
                $imagePaths = $imagesToRemove->pluck('image_path')->filter()->all();

                if (!empty($imagePaths)) {
                    Storage::disk('supabase')->delete($imagePaths);
                }

                if ($imagesToRemove->isNotEmpty()) {
                    $imagesToRemove->each->delete();
                }
            }

            if ($hasImages) {
                $this->storePostImages($post, $request->file('images', []));
            }
        });

        $post->load(['alumni:id,first_name,last_name,alumni_photo', 'images:id,post_id,image_path']);

        return response()->json([
            'message' => 'Post updated successfully.',
            'post' => [
                'id' => $post->id,
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
                'images' => $post->images->map(function (ImagesPost $image) use ($request) {
                    return [
                        'id' => $image->id,
                        'image_path' => $image->image_path,
                        'image_url' => $this->resolveImageUrl($request, $image->image_path),
                    ];
                })->values(),
            ],
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        $currentAlumniId = $request->user()?->id;

        if (!$currentAlumniId || (int) $post->alumni_id !== $currentAlumniId) {
            return response()->json([
                'message' => 'You do not have permission to delete this post.',
            ], 403);
        }

        DB::transaction(function () use ($post) {
            $imagePaths = $post->images()->pluck('image_path')->filter()->all();

            if (!empty($imagePaths)) {
                Storage::disk('supabase')->delete($imagePaths);
            }

            $post->images()->delete();
            $post->delete();
        });

        return response()->json([
            'message' => 'Post deleted successfully.',
        ]);
    }

    public function react(Request $request, Post $post)
    {
        if (!$this->canViewerSeePost($post, $request->user()?->id)) {
            return response()->json([
                'message' => 'This post is no longer available.',
            ], 403);
        }

        $validated = $request->validate([
            'reaction' => 'required|in:like',
        ]);

        $alumniId = $request->user()->id;
        $reactionType = $validated['reaction'];

        $savedReaction = DB::transaction(function () use ($alumniId, $post, $reactionType) {
            $existingReaction = Reaction::query()
                ->where('alumni_id', $alumniId)
                ->where('post_id', $post->id)
                ->first();

            if ($existingReaction && $existingReaction->reaction === $reactionType) {
                $existingReaction->delete();

                return null;
            }

            return Reaction::updateOrCreate(
                [
                    'alumni_id' => $alumniId,
                    'post_id' => $post->id,
                ],
                [
                    'reaction' => $reactionType,
                ]
            );
        });

        $reactionCount = Reaction::query()
            ->where('post_id', $post->id)
            ->count();

        return response()->json([
            'message' => $savedReaction ? 'Reaction saved.' : 'Reaction removed.',
            'reaction_count' => $reactionCount,
            'my_reaction' => $savedReaction?->reaction,
        ]);
    }

    public function comment(Request $request, Post $post)
    {
        if (!$this->canViewerSeePost($post, $request->user()?->id)) {
            return response()->json([
                'message' => 'This post is no longer available.',
            ], 403);
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:10000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        $parentComment = null;

        if (!empty($validated['parent_id'])) {
            $parentComment = Comment::query()
                ->where('id', $validated['parent_id'])
                ->where('post_id', $post->id)
                ->where('moderation_status', 'approved')
                ->first();

            if (!$parentComment) {
                return response()->json([
                    'message' => 'Reply target is not available.',
                ], 422);
            }
        }

        $comment = DB::transaction(function () use ($request, $post, $validated, $parentComment) {
            return Comment::create([
                'alumni_id' => $request->user()->id,
                'post_id' => $post->id,
                'parent_id' => $parentComment?->id,
                'comment' => trim($validated['comment']),
                'moderation_status' => 'approved',
            ]);
        });

        $comment->load([
            'alumni:id,first_name,last_name,alumni_photo',
            'parentComment.alumni:id,first_name,last_name,alumni_photo',
        ]);

        $commentCount = Comment::query()
            ->where('post_id', $post->id)
            ->count();

        return response()->json([
            'message' => 'Comment saved.',
            'comment' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'parent_id' => $comment->parent_id,
                'parent_comment' => $comment->parentComment ? [
                    'id' => $comment->parentComment->id,
                    'comment' => $comment->parentComment->comment,
                    'alumni' => [
                        'id' => $comment->parentComment?->alumni?->id,
                        'first_name' => $comment->parentComment?->alumni?->first_name,
                        'last_name' => $comment->parentComment?->alumni?->last_name,
                        'alumni_photo' => $comment->parentComment?->alumni?->alumni_photo,
                    ],
                ] : null,
                'created_at' => $comment->created_at,
                'comment_count' => $commentCount,
                'alumni' => [
                    'id' => $request->user()->id,
                    'first_name' => $request->user()->first_name,
                    'last_name' => $request->user()->last_name,
                    'alumni_photo' => $request->user()->alumni_photo,
                ],
            ],
        ], 201);
    }

    public function repost(Request $request, Post $post)
    {
        if (!$this->canViewerSeePost($post, $request->user()?->id)) {
            return response()->json([
                'message' => 'This post is no longer available.',
            ], 403);
        }

        $validated = $request->validate([
            'caption' => 'nullable|string|max:10000',
        ]);

        $alumniId = $request->user()->id;
        $caption = isset($validated['caption']) ? trim($validated['caption']) : null;

        if ($caption === '') {
            $caption = null;
        }

        $savedRepost = DB::transaction(function () use ($alumniId, $post, $caption) {
            $existingRepost = Repost::query()
                ->where('alumni_id', $alumniId)
                ->where('post_id', $post->id)
                ->first();

            if ($existingRepost) {
                $existingRepost->delete();

                return null;
            }

            return Repost::create([
                'alumni_id' => $alumniId,
                'post_id' => $post->id,
                'caption' => $caption,
                'moderation_status' => 'approved',
            ]);
        });

        $isReposting = (bool) $savedRepost;

        $repostCount = Repost::query()
            ->where('post_id', $post->id)
            ->count();

        $repostPayload = null;

        if ($savedRepost) {
            $savedRepost->load([
                'alumni:id,first_name,last_name,alumni_photo',
                'post.alumni:id,first_name,last_name,alumni_photo',
                'post.images:id,post_id,image_path',
            ]);

            $repostPayload = [
                'id' => $post->id,
                'feed_id' => sprintf('repost-%d', $savedRepost->id),
                'feed_type' => 'repost',
                'caption' => $savedRepost->caption,
                'created_at' => $savedRepost->created_at,
                'alumni' => [
                    'id' => $savedRepost->alumni?->id,
                    'first_name' => $savedRepost->alumni?->first_name,
                    'last_name' => $savedRepost->alumni?->last_name,
                    'alumni_photo' => $savedRepost->alumni?->alumni_photo,
                ],
                'comment_count' => Comment::query()->where('post_id', $post->id)->count(),
                'reaction_count' => Reaction::query()->where('post_id', $post->id)->count(),
                'repost_count' => $repostCount,
                'my_reaction' => Reaction::query()
                    ->where('post_id', $post->id)
                    ->where('alumni_id', $alumniId)
                    ->value('reaction'),
                'my_repost' => true,
                'images' => $savedRepost->post?->images->map(function (ImagesPost $image) use ($request) {
                    return [
                        'id' => $image->id,
                        'image_path' => $image->image_path,
                        'image_url' => $this->resolveImageUrl($request, $image->image_path),
                    ];
                })->values() ?? [],
                'original_post' => [
                    'id' => $savedRepost->post?->id,
                    'caption' => $savedRepost->post?->caption,
                    'created_at' => $savedRepost->post?->created_at,
                    'alumni' => [
                        'id' => $savedRepost->post?->alumni?->id,
                        'first_name' => $savedRepost->post?->alumni?->first_name,
                        'last_name' => $savedRepost->post?->alumni?->last_name,
                        'alumni_photo' => $savedRepost->post?->alumni?->alumni_photo,
                    ],
                ],
            ];
        }

        return response()->json([
            'message' => $isReposting ? 'Repost saved.' : 'Repost removed.',
            'repost_count' => $repostCount,
            'my_repost' => $isReposting,
            'repost' => $repostPayload,
        ]);
    }

    public function comments(Request $request, Post $post)
    {
        if (!$this->canViewerSeePost($post, $request->user()?->id)) {
            return response()->json([
                'comments' => [],
            ], 403);
        }

        $comments = Comment::with(['alumni:id,first_name,last_name,alumni_photo', 'parentComment.alumni:id,first_name,last_name,alumni_photo'])
            ->where('post_id', $post->id)
            ->where('moderation_status', 'approved')
            ->orderBy('created_at')
            ->get()
            ->map(function (Comment $comment) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'parent_id' => $comment->parent_id,
                    'parent_comment' => $comment->parentComment ? [
                        'id' => $comment->parentComment->id,
                        'comment' => $comment->parentComment->comment,
                        'alumni' => [
                            'id' => $comment->parentComment?->alumni?->id,
                            'first_name' => $comment->parentComment?->alumni?->first_name,
                            'last_name' => $comment->parentComment?->alumni?->last_name,
                            'alumni_photo' => $comment->parentComment?->alumni?->alumni_photo,
                        ],
                    ] : null,
                    'created_at' => $comment->created_at,
                    'alumni' => [
                        'id' => $comment->alumni?->id,
                        'first_name' => $comment->alumni?->first_name,
                        'last_name' => $comment->alumni?->last_name,
                        'alumni_photo' => $comment->alumni?->alumni_photo,
                    ],
                ];
            });

        return response()->json([
            'comments' => $comments,
        ]);
    }

    public function notifications(Request $request)
    {
        $currentAlumniId = $request->user()?->id;

        if (!$currentAlumniId) {
            return response()->json([
                'notifications' => [],
            ]);
        }

        $dismissedNotificationKeys = $this->getDismissedNotificationKeys($currentAlumniId);

        $ownedPostIds = Post::query()
            ->where('alumni_id', $currentAlumniId)
            ->pluck('id')
            ->all();

        $reactions = collect();
        $comments = collect();
        $reposts = collect();
        $announcements = collect();

        if (!empty($ownedPostIds)) {
            $reactions = Reaction::with(['alumni:id,first_name,last_name,alumni_photo', 'post:id,caption,alumni_id,created_at'])
                ->whereIn('post_id', $ownedPostIds)
                ->where('alumni_id', '!=', $currentAlumniId)
                ->whereHas('post', function ($query) {
                    $query->where('moderation_status', 'approved');
                })
                ->orderByDesc('created_at')
                ->get()
                ->map(function (Reaction $reaction) {
                    $post = $reaction->post;

                    if (!$post) {
                        return null;
                    }

                    return [
                        'id' => $this->buildNotificationKey('reaction', $reaction->id),
                        'type' => 'reaction',
                        'source_id' => $reaction->id,
                        'actor' => [
                            'id' => $reaction->alumni?->id,
                            'first_name' => $reaction->alumni?->first_name,
                            'last_name' => $reaction->alumni?->last_name,
                            'alumni_photo' => $reaction->alumni?->alumni_photo,
                        ],
                        'post' => [
                            'id' => $post->id,
                            'caption' => $post->caption,
                        ],
                        'detail' => null,
                        'created_at' => $reaction->created_at,
                    ];
                });

            $comments = Comment::with(['alumni:id,first_name,last_name,alumni_photo', 'post:id,caption,alumni_id,created_at'])
                ->whereIn('post_id', $ownedPostIds)
                ->where('alumni_id', '!=', $currentAlumniId)
                ->where('moderation_status', 'approved')
                ->whereHas('post', function ($query) {
                    $query->where('moderation_status', 'approved');
                })
                ->orderByDesc('created_at')
                ->get()
                ->map(function (Comment $comment) {
                    $post = $comment->post;

                    if (!$post) {
                        return null;
                    }

                    return [
                        'id' => $this->buildNotificationKey('comment', $comment->id),
                        'type' => 'comment',
                        'source_id' => $comment->id,
                        'actor' => [
                            'id' => $comment->alumni?->id,
                            'first_name' => $comment->alumni?->first_name,
                            'last_name' => $comment->alumni?->last_name,
                            'alumni_photo' => $comment->alumni?->alumni_photo,
                        ],
                        'post' => [
                            'id' => $post->id,
                            'caption' => $post->caption,
                        ],
                        'detail' => $comment->comment,
                        'created_at' => $comment->created_at,
                    ];
                });

            $reposts = Repost::with(['alumni:id,first_name,last_name,alumni_photo', 'post:id,caption,alumni_id,created_at'])
                ->whereIn('post_id', $ownedPostIds)
                ->where('alumni_id', '!=', $currentAlumniId)
                ->where('moderation_status', 'approved')
                ->whereHas('post', function ($query) {
                    $query->where('moderation_status', 'approved');
                })
                ->orderByDesc('created_at')
                ->get()
                ->map(function (Repost $repost) {
                    $post = $repost->post;

                    if (!$post) {
                        return null;
                    }

                    return [
                        'id' => $this->buildNotificationKey('repost', $repost->id),
                        'type' => 'repost',
                        'source_id' => $repost->id,
                        'actor' => [
                            'id' => $repost->alumni?->id,
                            'first_name' => $repost->alumni?->first_name,
                            'last_name' => $repost->alumni?->last_name,
                            'alumni_photo' => $repost->alumni?->alumni_photo,
                        ],
                        'post' => [
                            'id' => $post->id,
                            'caption' => $post->caption,
                        ],
                        'detail' => null,
                        'created_at' => $repost->created_at,
                    ];
                });
        }

        $announcements = DB::table('announcements as announcement')
            ->leftJoin('admins as admin', 'admin.id', '=', 'announcement.admin_id')
            ->orderByDesc('announcement.date_posted')
            ->limit(25)
            ->get([
                'announcement.id',
                'announcement.title as announcement_title',
                'announcement.announcement_description',
                'announcement.date_posted',
                'admin.admin_first_name',
                'admin.admin_middle_name',
                'admin.admin_last_name',
                'admin.photo as admin_photo',
            ])
            ->map(function ($announcement) {
                return [
                    'id' => $this->buildNotificationKey('announcement', (int) $announcement->id),
                    'type' => 'announcement',
                    'source_id' => (int) $announcement->id,
                    'actor' => [
                        'id' => (int) $announcement->id,
                        'first_name' => 'NU',
                        'last_name' => 'LIPA',
                        'alumni_photo' => $announcement->admin_photo,
                    ],
                    'post' => null,
                    'detail' => $announcement->announcement_description,
                    'announcement_title' => $announcement->announcement_title,
                    'created_at' => $announcement->date_posted,
                ];
            });

        $followNotifications = DB::table('followers')
            ->join('alumnis', 'followers.follower_alumni_id', '=', 'alumnis.id')
            ->where('followers.followed_alumni_id', $currentAlumniId)
            ->where('followers.status', Follower::STATUS_PENDING)
            ->orderByDesc('followers.created_at')
            ->select([
                'followers.id as source_id',
                'followers.created_at as created_at',
                'alumnis.id as actor_id',
                'alumnis.first_name',
                'alumnis.last_name',
                'alumnis.alumni_photo',
            ])
            ->get()
            ->map(function ($follow) {
                return [
                    'id' => $this->buildNotificationKey('follow', (int) $follow->source_id),
                    'type' => 'follow',
                    'source_id' => (int) $follow->source_id,
                    'actor' => [
                        'id' => (int) $follow->actor_id,
                        'first_name' => $follow->first_name,
                        'last_name' => $follow->last_name,
                        'alumni_photo' => $follow->alumni_photo,
                    ],
                    'post' => null,
                    'detail' => null,
                    'created_at' => $follow->created_at,
                ];
            });

        $notifications = collect()
            ->concat($reactions)
            ->concat($comments)
            ->concat($reposts)
            ->concat($announcements)
            ->concat($followNotifications)
            ->filter()
            ->reject(function (array $notification) use ($dismissedNotificationKeys) {
                return in_array($notification['id'], $dismissedNotificationKeys, true);
            })
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    public function dismissNotification(Request $request, string $notificationKey)
    {
        $currentAlumniId = $request->user()?->id;

        if (!$currentAlumniId) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (!preg_match('/^(reaction|comment|repost|follow|announcement)-\d+$/', $notificationKey)) {
            return response()->json([
                'message' => 'Invalid notification key.',
            ], 422);
        }

        DismissedNotification::updateOrCreate(
            [
                'alumni_id' => $currentAlumniId,
                'notification_key' => $notificationKey,
            ],
            []
        );

        return response()->json([
            'message' => 'Notification dismissed.',
        ]);
    }

    public function myPosts(Request $request)
    {
        $currentAlumniId = $request->user()?->id;

        if (!$currentAlumniId) {
            return response()->json([
                'posts' => [],
            ]);
        }

        $posts = Post::with([
            'alumni:id,first_name,last_name,alumni_photo',
            'images:id,post_id,image_path',
        ])
            ->withCount(['reactions', 'comments', 'reposts'])
            ->where('alumni_id', $currentAlumniId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Post $post) => $this->buildPostFeedItem($post));

        $reposts = Repost::with([
            'alumni:id,first_name,last_name,alumni_photo',
            'post.alumni:id,first_name,last_name,alumni_photo',
            'post.images:id,post_id,image_path',
            'post:id,alumni_id,caption,created_at,visibility,is_draft',
        ])
            ->where('alumni_id', $currentAlumniId)
            ->where('moderation_status', 'approved')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Repost $repost) => $this->buildRepostFeedItem($repost, $currentAlumniId))
            ->filter()
            ->values();

        $activityItems = $posts
            ->concat($reposts)
            ->sortByDesc(function (array $item) {
                return $item['created_at'];
            })
            ->values()
            ->map(function (array $item) use ($request) {
                $item['images'] = collect($item['images'])->map(function (array $image) use ($request) {
                    $image['image_url'] = $this->resolveImageUrl($request, $image['image_path']);

                    return $image;
                })->values();

                return $item;
            });

        return response()->json([
            'posts' => $activityItems,
        ]);
    }
}