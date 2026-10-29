<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\EventReview;
use App\Models\Message;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContentController extends Controller
{
    public function categories(Request $request)
    {
        $query = Category::query();

        if ($search = trim((string) $request->query('search'))) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $this->listing($query, 'Categories', [
            'Name' => 'name',
            'Created' => 'created_at',
        ], [
            'latest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'category_asc' => ['name', 'asc'],
            'category_desc' => ['name', 'desc'],
        ]);
    }

    public function notifications(Request $request)
    {
        $query = UserNotification::query()->with('user');

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        return $this->listing($query, 'Notifications', [
            'User' => 'user.name',
            'Type' => 'type',
            'Title' => 'title',
            'Message' => 'message',
            'Read at' => 'read_at',
            'Created' => 'created_at',
        ], [
            'latest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'notification_asc' => ['title', 'asc'],
            'notification_desc' => ['title', 'desc'],
        ]);
    }

    public function messages(Request $request)
    {
        $blockedUserIds = UserBlock::query()
            ->active()
            ->where('blocked_by', Auth::id())
            ->pluck('user_id')
            ->all();
        $latestIds = Message::query()
            ->selectRaw('MAX(id)')
            ->groupByRaw('CASE WHEN sender_id < receiver_id THEN sender_id ELSE receiver_id END, CASE WHEN sender_id < receiver_id THEN receiver_id ELSE sender_id END');
        $query = Message::query()
            ->with(['sender', 'receiver'])
            ->addSelect([
                'messages_count' => Message::query()
                    ->from('messages as m')
                    ->selectRaw('count(*)')
                    ->whereNull('m.deleted_at')
                    ->whereRaw('((m.sender_id = messages.sender_id AND m.receiver_id = messages.receiver_id) OR (m.sender_id = messages.receiver_id AND m.receiver_id = messages.sender_id))'),
            ])
            ->whereIn('id', $latestIds);

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                    ->orWhereHas('sender', function (Builder $senderQuery) use ($search) {
                        $senderQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    })
                    ->orWhereHas('receiver', function (Builder $receiverQuery) use ($search) {
                        $receiverQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        $sortOptions = [
            'latest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'message_asc' => ['message', 'asc'],
            'message_desc' => ['message', 'desc'],
        ];
        [$column, $direction] = $sortOptions[$request->query('sort')] ?? $sortOptions['latest'];

        $records = $query->orderBy($column, $direction)
            ->paginate(10)
            ->withQueryString();
        $title = 'Messages';
        $columns = [
            'From' => 'sender.username',
            'To' => 'receiver.username',
            'Message' => 'message',
            'Messages' => 'messages_count',
            'Sent' => 'created_at',
        ];

        return view('admin.sections.index', compact('title', 'columns', 'records', 'blockedUserIds'));
    }

    public function showMessage(Message $message)
    {
        $message->load(['sender', 'receiver']);
        $participantIds = [(int) $message->sender_id, (int) $message->receiver_id];
        $conversation = Message::query()
            ->with('sender')
            ->whereIn('sender_id', $participantIds)
            ->whereIn('receiver_id', $participantIds)
            ->oldest()
            ->oldest('id')
            ->get();

        return view('admin.messages.show', compact('message', 'conversation'));
    }

    public function blockMessageUser(User $user)
    {
        abort_if((int) $user->id === (int) Auth::id(), 404);

        UserBlock::block((int) $user->id, (int) Auth::id());

        return back()->with('status', 'User blocked. Messages between this user and the admin are disabled.');
    }

    public function unblockMessageUser(User $user)
    {
        abort_if((int) $user->id === (int) Auth::id(), 404);

        UserBlock::unblock((int) $user->id, (int) Auth::id());

        return back()->with('status', 'User unblocked.');
    }

    public function destroyMessage(Message $message)
    {
        $message->delete();

        return back()->with('status', 'Message deleted.');
    }

    public function comments(Request $request)
    {
        $query = Comment::query()->with(['user', 'event']);

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    })
                    ->orWhereHas('event', function (Builder $eventQuery) use ($search) {
                        $eventQuery->where('title', 'like', "%{$search}%");
                    });
            });
        }

        return $this->listing($query, 'Comments', [
            'User' => 'user.name',
            'Event' => 'event.title',
            'Comment' => 'content',
            'Created' => 'created_at',
        ], [
            'latest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'comment_asc' => ['content', 'asc'],
            'comment_desc' => ['content', 'desc'],
        ]);
    }

    public function editComment(Comment $comment)
    {
        $comment->load(['user', 'event']);

        return view('admin.comments.edit', compact('comment'));
    }

    public function showComment(Comment $comment)
    {
        $comment->load(['user', 'event']);

        return view('admin.comments.show', compact('comment'));
    }

    public function updateComment(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'language' => 'required|string|max:5',
        ]);

        $comment->update($validated);

        return redirect()->route('admin.comments')->with('status', 'Comment updated.');
    }

    public function destroyComment(Comment $comment)
    {
        $comment->delete();

        return redirect()->route('admin.comments')->with('status', 'Comment deleted.');
    }

    public function reviews(Request $request)
    {
        $query = EventReview::query()->with(['user', 'event']);

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    })
                    ->orWhereHas('event', function (Builder $eventQuery) use ($search) {
                        $eventQuery->where('title', 'like', "%{$search}%");
                    });
            });
        }

        return $this->listing($query, 'Reviews', [
            'User' => 'user.name',
            'Event' => 'event.title',
            'Rating' => 'rating',
            'Review' => 'comment',
            'Created' => 'created_at',
        ], [
            'latest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'review_asc' => ['comment', 'asc'],
            'review_desc' => ['comment', 'desc'],
        ]);
    }

    public function showReview(EventReview $review)
    {
        $review->load(['user', 'event']);

        return view('admin.reviews.show', compact('review'));
    }

    private function listing(Builder $query, string $title, array $columns, array $sortOptions = [])
    {
        if ($sortOptions !== []) {
            [$column, $direction] = $sortOptions[request('sort')] ?? reset($sortOptions);
            $query->orderBy($column, $direction);
        } else {
            $query->latest();
        }

        $records = $query->paginate(10)->withQueryString();

        return view('admin.sections.index', compact('title', 'columns', 'records'));
    }
}
