<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\GroupChat;
use App\Models\GroupChatMember;
use App\Models\GroupMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GroupChatController extends Controller
{
    public function typingStatus(Request $request, GroupChat $groupChat)
    {
        $userId = (int) $request->user()->id;
        $this->ensureMembership($userId, $groupChat);

        $typingUsers = $this->getTypingUsersForGroup($groupChat, $userId);

        return response()->json([
            'is_typing' => !empty($typingUsers),
            'typing_users' => $typingUsers,
        ]);
    }

    public function setTypingStatus(Request $request, GroupChat $groupChat)
    {
        $validated = $request->validate([
            'is_typing' => ['sometimes', 'boolean'],
        ]);

        $userId = (int) $request->user()->id;
        $this->ensureMembership($userId, $groupChat);

        $isTyping = $validated['is_typing'] ?? true;
        $typingKey = $this->groupTypingKey($groupChat->id, $userId);

        if ($isTyping) {
            Cache::put($typingKey, true, now()->addSeconds(6));
        } else {
            Cache::forget($typingKey);
        }

        return response()->json([
            'status' => 'success',
            'is_typing' => $isTyping,
        ]);
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $groupChats = GroupChat::with([
            'members',
            'latestMessage.sender',
        ])
            ->whereHas('members', function ($query) use ($userId) {
                $query->where('alumnis.id', $userId);
            })
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (GroupChat $groupChat) use ($userId) {
                return $this->formatGroupChat($groupChat, $userId);
            })
            ->values();

        return response()->json([
            'group_chats' => $groupChats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'member_ids' => ['required', 'array', 'min:2'],
            'member_ids.*' => ['integer', 'exists:alumnis,id'],
            'avatar_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $userId = $request->user()->id;
        $memberIds = collect($validated['member_ids'])
            ->push($userId)
            ->map(fn ($memberId) => (int) $memberId)
            ->unique()
            ->values();

        $groupChat = DB::transaction(function () use ($validated, $userId, $memberIds) {
            $groupChat = GroupChat::create([
                'name' => $validated['name'],
                'avatar_url' => $validated['avatar_url'] ?? null,
                'created_by' => $userId,
            ]);

            $membersPayload = $memberIds->mapWithKeys(function (int $memberId) use ($userId) {
                return [$memberId => [
                    'role' => $memberId === $userId ? 'admin' : 'alumni',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]];
            })->all();

            $groupChat->members()->attach($membersPayload);

            return $groupChat;
        });

        $groupChat->load(['members', 'latestMessage.sender']);

        return response()->json([
            'group' => $this->formatGroupChat($groupChat, $userId),
        ], 201);
    }

    public function messages(Request $request, GroupChat $groupChat)
    {
        $this->ensureMembership($request->user()->id, $groupChat);

        $messages = $groupChat->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, GroupChat $groupChat)
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $userId = $request->user()->id;
        $this->ensureMembership($userId, $groupChat);

        $message = GroupMessage::create([
            'group_chat_id' => $groupChat->id,
            'sender_id' => $userId,
            'content' => $validated['content'],
            'reactions' => [],
        ]);

        $groupChat->touch();

        $message->load('sender');

        return response()->json($message, 201);
    }

    public function markAsRead(Request $request, GroupChat $groupChat)
    {
        $userId = $request->user()->id;
        $this->ensureMembership($userId, $groupChat);

        $latestMessageId = $groupChat->messages()->max('id');

        GroupChatMember::where('group_chat_id', $groupChat->id)
            ->where('alumni_id', $userId)
            ->update([
                'last_read_message_id' => $latestMessageId,
                'updated_at' => now(),
            ]);

        return response()->json(['status' => 'success']);
    }

    public function react(Request $request, GroupChat $groupChat, GroupMessage $message)
    {
        $validated = $request->validate([
            'reaction' => ['required', 'string', 'max:8'],
        ]);

        $userId = $request->user()->id;
        $this->ensureMembership($userId, $groupChat);
        $this->ensureMessageBelongsToGroup($groupChat, $message);

        $reactions = $message->reactions ?? [];
        $reaction = $validated['reaction'];
        $reactions[$reaction] = ($reactions[$reaction] ?? 0) + 1;

        $message->update([
            'reactions' => $reactions,
        ]);

        $message->load('sender');

        return response()->json($message);
    }

    public function destroy(Request $request, GroupChat $groupChat, GroupMessage $message)
    {
        $userId = $request->user()->id;
        $this->ensureMembership($userId, $groupChat);
        $this->ensureMessageBelongsToGroup($groupChat, $message);

        if ((int) $message->sender_id !== (int) $userId) {
            abort(403, 'You can only delete your own messages.');
        }

        $message->delete();

        return response()->json(['status' => 'deleted']);
    }

    private function formatGroupChat(GroupChat $groupChat, int $userId): array
    {
        $memberRecord = GroupChatMember::where('group_chat_id', $groupChat->id)
            ->where('alumni_id', $userId)
            ->first();

        $latestMessage = $groupChat->latestMessage;
        $lastReadMessageId = $memberRecord?->last_read_message_id;

        $unreadCount = $groupChat->messages()
            ->when($lastReadMessageId, function ($query, $value) {
                $query->where('id', '>', $value);
            })
            ->where('sender_id', '!=', $userId)
            ->count();

        return [
            'id' => $groupChat->id,
            'name' => $groupChat->name,
            'avatar_url' => $groupChat->avatar_url,
            'created_by' => $groupChat->created_by,
            'created_at' => $groupChat->created_at,
            'updated_at' => $groupChat->updated_at,
            'unread_count' => $unreadCount,
            'latest_message' => $latestMessage,
            'members' => $groupChat->members->map(function (Alumni $member) {
                return [
                    'id' => $member->id,
                    'first_name' => $member->first_name,
                    'last_name' => $member->last_name,
                    'alumni_photo' => $member->alumni_photo,
                    'role' => $member->pivot?->role ?? 'alumni',
                ];
            })->values(),
        ];
    }

    private function ensureMembership(int $userId, GroupChat $groupChat): void
    {
        $isMember = GroupChatMember::where('group_chat_id', $groupChat->id)
            ->where('alumni_id', $userId)
            ->exists();

        if (!$isMember) {
            abort(403, 'You are not a member of this group chat.');
        }
    }

    private function ensureMessageBelongsToGroup(GroupChat $groupChat, GroupMessage $message): void
    {
        if ((int) $message->group_chat_id !== (int) $groupChat->id) {
            abort(404);
        }
    }

    private function getTypingUsersForGroup(GroupChat $groupChat, int $currentUserId): array
    {
        return $groupChat->members
            ->filter(function (Alumni $member) use ($groupChat, $currentUserId) {
                return (int) $member->id !== $currentUserId
                    && Cache::has($this->groupTypingKey($groupChat->id, (int) $member->id));
            })
            ->map(function (Alumni $member) {
                return [
                    'id' => $member->id,
                    'first_name' => $member->first_name,
                    'last_name' => $member->last_name,
                    'alumni_photo' => $member->alumni_photo,
                ];
            })
            ->values()
            ->all();
    }

    private function groupTypingKey(int $groupChatId, int $userId): string
    {
        return sprintf('chat_typing:group:%d:%d', $groupChatId, $userId);
    }
}