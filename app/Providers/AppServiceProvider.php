<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Community;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Message;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'comment' => Comment::class,
            'community' => Community::class,
            'event' => Event::class,
        ]);

        View::composer('layouts.app', function ($view): void {
            $currentUserId = Auth::id();
            $chats = $currentUserId
                ? Message::with(['sender:id,username,name', 'sender.profile', 'receiver:id,username,name', 'receiver.profile'])
                    ->where(function ($query) use ($currentUserId): void {
                        $query->where('sender_id', $currentUserId)
                            ->orWhere('receiver_id', $currentUserId);
                    })
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->get()
                    ->unique(fn (Message $message) => (int) $message->sender_id === (int) $currentUserId
                        ? $message->receiver_id
                        : $message->sender_id)
                    ->map(function (Message $message) use ($currentUserId): array {
                        return [
                            'participant' => (int) $message->sender_id === (int) $currentUserId
                                ? $message->receiver
                                : $message->sender,
                            'last_message' => $message,
                        ];
                    })
                    ->filter(fn (array $chat): bool => $chat['participant'] !== null)
                    ->values()
                : collect();

            $notifications = $currentUserId
                ? UserNotification::query()
                    ->where('user_id', $currentUserId)
                    ->latest()
                    ->limit(5)
                    ->get()
                : collect();
            $unreadNotificationCount = $currentUserId
                ? UserNotification::query()
                    ->where('user_id', $currentUserId)
                    ->whereNull('read_at')
                    ->count()
                : 0;
            $unreadMessagesCount = $currentUserId
                ? Message::query()
                    ->where('receiver_id', $currentUserId)
                    ->whereNull('read_at')
                    ->count()
                : 0;
            $followedRecipientIds = $currentUserId
                ? Follow::query()->where('follower_id', $currentUserId)->pluck('following_id')->all()
                : [];
            $messageRecipients = $currentUserId
                ? User::query()
                    ->with('profile')
                    ->where('id', '!=', $currentUserId)
                    ->orderByRaw(
                        'CASE WHEN id IN (SELECT following_id FROM follows WHERE follower_id = ?) THEN 0 ELSE 1 END',
                        [$currentUserId]
                    )
                    ->inRandomOrder()
                    ->get(['id', 'name', 'username'])
                : collect();
            $followedMessageRecipients = $messageRecipients->whereIn('id', $followedRecipientIds)->values();
            $otherMessageRecipients = $messageRecipients->whereNotIn('id', $followedRecipientIds)->values();

            $view->with([
                'chats' => $chats,
                'followedMessageRecipients' => $followedMessageRecipients,
                'otherMessageRecipients' => $otherMessageRecipients,
                'notifications' => $notifications,
                'unreadNotificationCount' => $unreadNotificationCount,
                'unreadMessagesCount' => $unreadMessagesCount,
            ]);
        });

        // Event Page
        View::composer('events.partials.sidebar', function ($view) {
            $hasMyEvents = Auth::check()
                && Event::where('owner_id', Auth::id())->exists();

            $view->with('hasMyEvents', $hasMyEvents);
        });

        // Choose either Bootstrap 5 or Bootstrap 4 depending on your frontend
        Paginator::useBootstrapFour(); 
        // or Paginator::useBootstrapFour();
    }
}
