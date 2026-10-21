<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $currentUserId = (int) Auth::id();
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

        return view('show', compact('title', 'columns', 'records'));
    }

    public function conversation(User $user)
    {
        $currentUserId = (int) Auth::id();
        abort_if($user->id === $currentUserId, 404);

        $messages = $this->conversationMessages($currentUserId, $user)
            ->with(['sender:id,username,name', 'sender.profile'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'sender_id', 'receiver_id', 'message', 'created_at']);
        abort_if($messages->isEmpty(), 404);

        return response()->json([
            'participant' => [
                'name' => $user->username ?: $user->name,
            ],
            'messages' => $messages
                ->map(fn (Message $message): array => $this->serializeMessage($message, $currentUserId))
                ->values(),
        ]);
    }

    public function send(Request $request, User $user)
    {
        $currentUserId = (int) Auth::id();
        abort_if($user->id === $currentUserId, 404);
        abort_unless($this->conversationMessages($currentUserId, $user)->exists(), 404);

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
