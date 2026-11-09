<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMessage;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

// コミュニティ内チャット（オーナーと承認済みメンバーだけが読める・送れる）
class CommunityChatController extends Controller
{
    public function communities(Request $request)
    {
        $userId = (int) $request->user()->id;
        $unreadMessageCounts = UserNotification::query()
            ->where('user_id', $userId)
            ->where('type', 'community_message')
            ->whereNull('read_at')
            ->get(['related_id'])
            ->countBy('related_id');
        $ownedCommunities = Community::query()
            ->where('owner_id', $userId)
            ->orderBy('name')
            ->get()
            ->map(fn (Community $community) => $this->withChatData(
                $community,
                $userId,
                (int) $unreadMessageCounts->get($community->id, 0),
            ));
        $joinedCommunities = Community::query()
            ->where('owner_id', '!=', $userId)
            ->whereHas('memberships', fn ($query) => $query
                ->where('user_id', $userId)
                ->where('status', 'approved'))
            ->orderBy('name')
            ->get()
            ->map(fn (Community $community) => $this->withChatData(
                $community,
                $userId,
                (int) $unreadMessageCounts->get($community->id, 0),
            ));

        return view('community.chats', compact('ownedCommunities', 'joinedCommunities'));
    }

    /**
     * メッセージ一覧（after を付けると、その ID より新しいものだけ返す）
     * since を付けると、その時刻以降に削除されたメッセージの ID も返す
     */
    public function index(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        abort_unless($community->hasMember($request->user()), 403);

        $syncedAt = now();
        $since = rescue(fn () => Carbon::parse($request->query('since')), null, false);
        $deletedIds = $request->filled('since') && $since
            ? $community->messages()->onlyTrashed()->where('deleted_at', '>=', $since)->pluck('id')
            : collect();

        $afterId = (int) $request->query('after', 0);
        $messages = $community->messages()
            ->with(['user:id,name,username', 'user.profile'])
            ->when($afterId > 0, fn ($query) => $query->where('id', '>', $afterId))
            ->latest('id')
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'messages' => $messages->map(fn (CommunityMessage $message) => $this->serialize($message, $request->user()->id, $community->owner_id)),
            'deleted_ids' => $deletedIds,
            'synced_at' => $syncedAt->toIso8601String(),
        ]);
    }

    /**
     * メッセージ送信
     */
    public function store(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        abort_unless($community->hasMember($request->user()), 403);

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message = $community->messages()->create([
            'user_id' => $request->user()->id,
            'message' => trim($validated['message']),
        ]);

        $recipientIds = $community->memberships()
            ->where('status', 'approved')
            ->pluck('user_id')
            ->push($community->owner_id)
            ->unique()
            ->reject(fn ($userId) => (int) $userId === (int) $request->user()->id);

        foreach ($recipientIds as $recipientId) {
            UserNotification::create([
                'user_id' => $recipientId,
                'sender_name' => $request->user()->username ?: $request->user()->name,
                'type' => 'community_message',
                'title' => 'New community message',
                'message' => "There is a new message in '{$community->name}'.",
                'related_type' => Community::class,
                'related_id' => $community->id,
            ]);
        }

        if ($request->expectsJson()) {
            $message->load(['user:id,name,username', 'user.profile']);

            return response()->json(['message' => $this->serialize($message, $request->user()->id, $community->owner_id)], 201);
        }

        return redirect()->to(route('community.show', $community->id) . '#chat');
    }

    /**
     * メッセージ削除（送った本人かコミュニティのオーナーだけ）
     */
    public function destroy(Request $request, $id, $messageId)
    {
        $community = Community::findOrFail($id);
        $message = $community->messages()->findOrFail($messageId);
        abort_unless(in_array($request->user()->id, [$message->user_id, $community->owner_id], true), 403);

        $message->delete();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => $message->id]);
        }

        return redirect()->to(route('community.show', $community->id) . '#chat');
    }

    private function serialize(CommunityMessage $message, int $currentUserId, ?int $ownerId = null): array
    {
        $user = $message->user;
        $avatar = $user?->profile?->avatar;
        if ($avatar && ! str_starts_with($avatar, 'http://') && ! str_starts_with($avatar, 'https://')) {
            $avatar = asset('storage/' . $avatar);
        }

        return [
            'id' => $message->id,
            'body' => $message->message,
            'datetime' => $message->created_at?->toIso8601String(),
            'is_mine' => (int) $message->user_id === $currentUserId,
            'can_delete' => (int) $message->user_id === $currentUserId || $ownerId === $currentUserId,
            'delete_url' => route('community.chat.destroy', [$message->community_id, $message->id]),
            'sender' => [
                'id' => (int) $message->user_id,
                'name' => $user?->username ?: $user?->name ?: 'Unknown user',
                'initial' => strtoupper(substr($user?->name ?? '?', 0, 1)),
                'avatar' => $avatar,
            ],
        ];
    }

    private function withChatData(Community $community, int $userId, int $unreadCount): Community
    {
        $community->setRelation('latestMessages', $community->messages()
            ->with(['user:id,name,username'])
            ->latest('id')
            ->limit(5)
            ->get()
            ->reverse()
            ->values());
        $community->setRelation('unreadMessages', $unreadCount > 0
            ? $community->messages()
                ->where('user_id', '!=', $userId)
                ->with(['user:id,name,username'])
                ->latest('id')
                ->limit($unreadCount)
                ->get()
                ->reverse()
                ->values()
            : collect());

        return $community;
    }
}
