<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Category;
use App\Models\Community;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Message;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

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
            'category' => Category::class,
            'community' => Community::class,
            'event' => Event::class,
        ]);

        View::composer('layouts.app', function ($view): void {
            $currentUserId = Auth::id();
            $storedOrder = json_decode((string) SiteSetting::get('header_category_order', ''), true);
            $categoryOrder = array_values(array_filter(is_array($storedOrder) ? $storedOrder : ['Events', 'Meet up', 'Sports', 'Exchange', 'Jobs']));
            $categoryOrder = $categoryOrder ?: ['Events', 'Meet up', 'Sports', 'Exchange', 'Jobs'];
            $storedCategories = Category::query()
                ->whereIn('name', $categoryOrder)
                ->with(['translations' => fn ($query) => $query->where('field', 'name')->where('target_language', app()->getLocale())])
                ->get()
                ->keyBy('name');
            $categories = collect($categoryOrder)->mapWithKeys(function (string $label) use ($storedCategories): array {
                $category = $storedCategories->get($label);

                return [$category?->id ?? $label => $category?->localized_name ?? $label];
            });
            $unreadMessageSenderIds = $currentUserId
                ? Message::query()
                    ->where('receiver_id', $currentUserId)
                    ->whereNull('read_at')
                    ->pluck('sender_id')
                    ->map(fn ($senderId): int => (int) $senderId)
                    ->all()
                : [];
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
                    ->map(function (Message $message) use ($currentUserId, $unreadMessageSenderIds): array {
                        $participant = (int) $message->sender_id === (int) $currentUserId
                            ? $message->receiver
                            : $message->sender;

                        return [
                            'participant' => $participant,
                            'last_message' => $message,
                            'has_unread' => $participant !== null
                                && in_array((int) $participant->id, $unreadMessageSenderIds, true),
                        ];
                    })
                    ->filter(fn (array $chat): bool => $chat['participant'] !== null)
                    ->values()
                : collect();

            $notifications = $currentUserId
                ? UserNotification::query()
                    ->where('user_id', $currentUserId)
                    ->whereNotIn('type', ['community_message', 'event_comment', 'admin_contact'])
                    ->with('related')
                    ->latest()
                    ->limit(5)
                    ->get()
                : collect();
            $unreadNotificationCount = $currentUserId
                ? UserNotification::query()
                    ->where('user_id', $currentUserId)
                    ->whereNull('read_at')
                    ->whereNotIn('type', ['community_message', 'event_comment', 'admin_contact'])
                    ->count()
                : 0;
            $unreadMessagesCount = $currentUserId
                ? Message::query()
                    ->where('receiver_id', $currentUserId)
                    ->whereNull('read_at')
                    ->count()
                    + UserNotification::query()
                        ->where('user_id', $currentUserId)
                        ->where('type', 'event_comment')
                        ->whereNull('read_at')
                        ->count()
                    + ($currentUserId && Auth::user()?->role === 'admin'
                        ? UserNotification::query()
                            ->where('user_id', $currentUserId)
                            ->where('type', 'admin_contact')
                            ->whereNull('read_at')
                            ->count()
                        : 0)
                : 0;
            $eventCommentNotifications = $currentUserId
                ? UserNotification::query()
                    ->where('user_id', $currentUserId)
                    ->where('type', 'event_comment')
                    ->with('related')
                    ->latest()
                    ->limit(5)
                    ->get()
                : collect();
            $adminContactNotifications = $currentUserId && Auth::user()?->role === 'admin'
                ? UserNotification::query()
                    ->where('user_id', $currentUserId)
                    ->where('type', 'admin_contact')
                    ->with('related')
                    ->latest()
                    ->limit(5)
                    ->get()
                : collect();
            $communityNotificationCount = $currentUserId
                ? UserNotification::query()
                    ->where('user_id', $currentUserId)
                    ->whereNull('read_at')
                    ->whereIn('type', ['community_message', 'community_join_request', 'community_join_approved'])
                    ->count()
                : 0;
            $communityNotifications = $currentUserId
                ? UserNotification::query()
                    ->where('user_id', $currentUserId)
                    ->whereIn('type', ['community_message', 'community_join_request', 'community_join_approved'])
                    ->with('related')
                    ->latest()
                    ->limit(5)
                    ->get()
                : collect();
            $hasMyEvents = $currentUserId
                ? Event::query()->where('owner_id', $currentUserId)->exists()
                : false;
            $hasMyCommunities = $currentUserId
                ? Community::query()
                    ->where('owner_id', $currentUserId)
                    ->orWhereHas('memberships', fn ($query) => $query
                        ->where('user_id', $currentUserId)
                        ->where('status', 'approved'))
                    ->exists()
                : false;
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
                'categories' => $categories,
                'chats' => $chats,
                'followedMessageRecipients' => $followedMessageRecipients,
                'otherMessageRecipients' => $otherMessageRecipients,
                'notifications' => $notifications,
                'unreadNotificationCount' => $unreadNotificationCount,
                'unreadMessagesCount' => $unreadMessagesCount,
                'eventCommentNotifications' => $eventCommentNotifications,
                'adminContactNotifications' => $adminContactNotifications,
                'communityNotificationCount' => $communityNotificationCount,
                'communityNotifications' => $communityNotifications,
                'hasMyEvents' => $hasMyEvents,
                'hasMyCommunities' => $hasMyCommunities,
                'siteSettings' => SiteSetting::query()->pluck('value', 'key'),
            ]);
        });

        // Event Page
        View::composer('events.partials.sidebar', function ($view) {
            $hasMyEvents = Auth::check()
                && Event::where('owner_id', Auth::id())->whereNull('community_id')->exists();

            $view->with('hasMyEvents', $hasMyEvents);
        });

        // Choose either Bootstrap 5 or Bootstrap 4 depending on your frontend
        Paginator::useBootstrapFour(); 
        // or Paginator::useBootstrapFour();

        // headerを含む共通コンポーネント（例: layouts.app や partials.header）にデータを渡す
        View::composer(['layouts.app', 'partials.header'], function ($view) {
        
        $userId = Auth::id();
        $sessionId = session()->getId();

        // 1. ログインユーザーまたはゲストの「よく見るカテゴリーID」を取得（直近30日間）
        $frequentCategoryIds = DB::table('category_views')
            ->select('category_id')
            ->where(function($query) use ($userId, $sessionId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('category_id')
            ->orderByRaw('COUNT(*) DESC')
            ->take(3) // ショートカット用の上位3件
            ->pluck('category_id');

        // 2. 該当カテゴリーのモデルを取得（データがない場合はデフォルトのカテゴリーを入れる）
        if ($frequentCategoryIds->isNotEmpty()) {
            // よく見る順に並べる（MySQL 専用の FIELD() を使わず、テスト用の SQLite でも動くようにする）
            $personalizedCategories = Category::whereIn('id', $frequentCategoryIds)
                ->with(['translations' => fn ($query) => $query
                    ->where('field', 'name')
                    ->where('target_language', app()->getLocale())])
                ->get()
                ->sortBy(fn (Category $category) => $frequentCategoryIds->search($category->id))
                ->values();
        } else {
            // 閲覧履歴がない場合は、全体の「人気カテゴリー」をデフォルト表示
            $popularIds = DB::table('category_views')
                ->select('category_id')
                ->groupBy('category_id')
                ->orderByRaw('COUNT(*) DESC')
                ->take(3)
                ->pluck('category_id');
                
            $personalizedCategories = Category::whereIn('id', $popularIds)
                ->with(['translations' => fn ($query) => $query
                    ->where('field', 'name')
                    ->where('target_language', app()->getLocale())])
                ->get();
        }

        // ビューに配列として渡す
        $view->with('headerShortcuts', $personalizedCategories);
        });
    }
}
