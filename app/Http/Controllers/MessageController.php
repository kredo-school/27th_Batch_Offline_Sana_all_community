<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $currentUserId = (int) Auth::id();
        $blockedUserIds = UserBlock::query()
            ->active()
            ->where('blocked_by', $currentUserId)
            ->pluck('user_id')
            ->all();
        $latestMessageIds = Message::query()
            ->where(function ($query) use ($currentUserId): void {
                $query->where('sender_id', $currentUserId)
                    ->orWhere('receiver_id', $currentUserId);
            })
            ->selectRaw('MAX(id)')
            ->groupByRaw('CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END', [$currentUserId]);

        $records = Message::query()
            ->with(['sender', 'receiver'])
            ->whereIn('id', $latestMessageIds)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $title = 'Messages';
        $columns = [
            'From' => 'sender.username',
            'To' => 'receiver.username',
            'Message' => 'message',
            'Sent' => 'created_at',
        ];

        return view('show', compact('title', 'columns', 'records', 'blockedUserIds'));
    }

    public function conversation(User $user)
    {
        $currentUserId = (int) Auth::id();
        abort_if($user->id === $currentUserId, 404);
        $canSend = ! UserBlock::blocksEitherWay($currentUserId, (int) $user->id);

        $messages = $this->conversationMessages($currentUserId, $user)
            ->with(['sender:id,username,name', 'sender.profile'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'sender_id', 'receiver_id', 'message', 'created_at']);
        Message::query()
            ->where('sender_id', $user->id)
            ->where('receiver_id', $currentUserId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $unreadCount = Message::query()
            ->where('receiver_id', $currentUserId)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'participant' => [
                'name' => $user->username ?: $user->name,
            ],
            'can_send' => $canSend,
            'messages' => $messages
                ->map(fn (Message $message): array => $this->serializeMessage($message, $currentUserId))
                ->values(),
        ]);
    }

    public function send(Request $request, User $user)
    {
        $currentUserId = (int) Auth::id();
        abort_if($user->id === $currentUserId, 404);
        abort_if(UserBlock::blocksEitherWay($currentUserId, (int) $user->id), 403, 'Messaging is unavailable because one of you blocked the other.');

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = Message::create([
            'sender_id' => $currentUserId,
            'receiver_id' => $user->id,
            'message' => trim($validated['message']),
        ])->load(['sender:id,username,name', 'sender.profile']);

        return response()->json([
            'message' => $this->serializeMessage($message, $currentUserId),
        ], 201);
    }

    public function block(User $user)
    {
        abort_if((int) $user->id === (int) Auth::id(), 404);

        UserBlock::block((int) $user->id, (int) Auth::id());

        return back()->with('status', 'User blocked. Messages between you are disabled.');
    }

    public function unblock(User $user)
    {
        abort_if((int) $user->id === (int) Auth::id(), 404);

        UserBlock::unblock((int) $user->id, (int) Auth::id());

        return back()->with('status', 'User unblocked.');
    }

    public function destroy(Message $message)
    {
        abort_unless(
            (int) $message->sender_id === (int) Auth::id()
                || (int) $message->receiver_id === (int) Auth::id(),
            403
        );

        $message->delete();

        return back()->with('status', 'Message deleted.');
    }

    private function serializeMessage(Message $message, int $currentUserId): array
    {
        $sender = $message->sender;

        return [
            'body' => $message->message,
            'sent_at' => $message->created_at?->format('M j, Y g:i A'),
            'datetime' => $message->created_at?->toIso8601String(),
            'is_mine' => (int) $message->sender_id === $currentUserId,
            'sender' => [
                'name' => $sender?->name,
                'username' => $sender?->username ?: $sender?->name,
                'avatar' => $sender?->profile?->avatar,
            ],
        ];
    }

    private function conversationMessages(int $currentUserId, User $user): Builder
    {
        return Message::query()->where(function ($query) use ($currentUserId, $user): void {
            $query->where(function ($query) use ($currentUserId, $user): void {
                $query->where('sender_id', $currentUserId)
                    ->where('receiver_id', $user->id);
            })->orWhere(function ($query) use ($currentUserId, $user): void {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', $currentUserId);
            });
        });
    }
}
