<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

// コミュニティ内チャット（オーナーと承認済みメンバーだけが読める・送れる）
class CommunityChatController extends Controller
{
    /**
     * メッセージ一覧（after を付けると、その ID より新しいものだけ返す）
     * since を付けると、その時刻以降に削除されたメッセージの ID と、編集されたメッセージも返す
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
        $updatedMessages = $request->filled('since') && $since && $afterId > 0
            ? $community->messages()
                ->with(['user:id,name,username', 'user.profile'])
                ->where('id', '<=', $afterId)
                ->where('updated_at', '>=', $since)
                ->whereColumn('updated_at', '>', 'created_at')
                ->get()
            : collect();

        return response()->json([
            'messages' => $messages->map(fn (CommunityMessage $message) => $this->serialize($message, $request->user()->id, $community->owner_id)),
            'updated_messages' => $updatedMessages->map(fn (CommunityMessage $message) => $this->serialize($message, $request->user()->id, $community->owner_id)),
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

        if ($request->expectsJson()) {
            $message->load(['user:id,name,username', 'user.profile']);

            return response()->json(['message' => $this->serialize($message, $request->user()->id, $community->owner_id)], 201);
        }

        return redirect()->to(route('community.show', $community->id) . '#chat');
    }

    /**
     * メッセージ編集（送った本人だけ。オーナーでも他の人の発言は書き換えられない）
     */
    public function update(Request $request, $id, $messageId)
    {
        $community = Community::findOrFail($id);
        abort_unless($community->hasMember($request->user()), 403);
        $message = $community->messages()->findOrFail($messageId);
        abort_unless((int) $message->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message->update(['message' => trim($validated['message'])]);

        if ($request->expectsJson()) {
            $message->load(['user:id,name,username', 'user.profile']);

            return response()->json(['message' => $this->serialize($message, $request->user()->id, $community->owner_id)]);
        }

        return redirect()->to(route('community.show', $community->id) . '#chat');
    }

    /**
     * 未読メッセージの数（Chat タブのバッジ用）
     */
    public function unread(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        abort_unless($community->hasMember($request->user()), 403);

        return response()->json([
            'unread' => $community->unreadMessagesCountFor($request->user()),
            'senders' => $community->unreadSenderNamesFor($request->user()),
        ]);
    }

    /**
     * 指定したメッセージまで既読にする（既読の位置が戻ることはない）
     */
    public function markRead(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        abort_unless($community->hasMember($request->user()), 403);

        $validated = $request->validate([
            'last_read_id' => 'required|integer|min:0',
        ]);

        // 存在しない大きな ID で今後の未読が隠れないように、最新のメッセージまでに抑える
        $lastReadId = min($validated['last_read_id'], (int) $community->messages()->max('id'));
        $userId = $request->user()->id;

        $community->chatReads()->firstOrCreate(['user_id' => $userId]);
        $community->chatReads()
            ->where('user_id', $userId)
            ->where('last_read_message_id', '<', $lastReadId)
            ->update(['last_read_message_id' => $lastReadId]);

        return response()->json(['unread' => $community->unreadMessagesCountFor($request->user())]);
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
            'is_edited' => (bool) $message->updated_at?->gt($message->created_at),
            'can_edit' => (int) $message->user_id === $currentUserId,
            'can_delete' => (int) $message->user_id === $currentUserId || $ownerId === $currentUserId,
            'update_url' => route('community.chat.update', [$message->community_id, $message->id]),
            'delete_url' => route('community.chat.destroy', [$message->community_id, $message->id]),
            'sender' => [
                'id' => (int) $message->user_id,
                'name' => $message->senderName(),
                'profile_url' => $user ? route('profile.show', $user->id) : null,
                'initial' => strtoupper(substr($user?->name ?? '?', 0, 1)),
                'avatar' => $avatar,
            ],
        ];
    }
}
