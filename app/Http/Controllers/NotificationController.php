<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Event;
use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');

        $records = UserNotification::query()
            ->where('user_id', Auth::id())
            ->when(
                $type === 'community',
                fn ($query) => $query->whereIn('type', UserNotification::COMMUNITY_TYPES)
            )
            ->when(
                in_array($type, ['general', 'maintenance'], true),
                fn ($query) => $query->where('type', $type)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $ownedCommunities = collect();
        $joinedCommunities = collect();
        if ($request->user() && $type === 'community') {
            $userId = $request->user()->id;
            $unreadCounts = UserNotification::query()
                ->where('user_id', $userId)
                ->where('type', 'community_message')
                ->whereNull('read_at')
                ->selectRaw('related_id, count(*) as unread_chat_messages')
                ->groupBy('related_id')
                ->pluck('unread_chat_messages', 'related_id');

            $addUnreadCounts = static function ($communities) use ($unreadCounts) {
                return $communities->each(function (Community $community) use ($unreadCounts): void {
                    $community->setAttribute(
                        'unread_chat_messages',
                        (int) $unreadCounts->get($community->id, 0),
                    );
                });
            };

            $ownedCommunities = $addUnreadCounts(Community::query()
                ->where('owner_id', $userId)
                ->withCount(['messages as total_chat_messages'])
                ->orderBy('name')
                ->get());

            $joinedCommunities = $addUnreadCounts(Community::query()
                ->where('owner_id', '!=', $userId)
                ->whereHas('memberships', fn ($query) => $query
                    ->where('user_id', $userId)
                    ->where('status', 'approved'))
                ->withCount(['messages as total_chat_messages'])
                ->orderBy('name')
                ->get());
        }

        $title = 'Notifications';
        $columns = [
            'From' => 'sender_name',
            'Type' => 'type',
            'Title' => 'title',
            'Notification' => 'message',
            'Read at' => 'read_at',
            'Created' => 'created_at',
        ];

        return view('layouts.home.show', compact(
            'title', 'columns', 'records', 'ownedCommunities', 'joinedCommunities'
        ));
    }

    public function show(UserNotification $notification)
    {
        abort_unless((int) $notification->user_id === (int) Auth::id(), 404);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        if ($notification->type === 'admin_contact' && $notification->related_id !== null) {
            UserNotification::query()
                ->where('type', 'admin_contact')
                ->where('related_id', $notification->related_id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.contacts.show', $notification->related_id);
            }
        }

        if ($notification->type === 'event_join_request' && $notification->related_id !== null) {
            return redirect()->route('events.participants', $notification->related_id);
        }

        if ($notification->type === 'event_comment' && $notification->related_id !== null) {
            return redirect()->route('events.show', $notification->related_id);
        }

        // 招待・参加申請の結果のお知らせは、そのコミュニティのページを開く
        // （招待が取り消されて見られなくなっていたら、コミュニティの通知一覧へ）
        if (in_array($notification->type, ['community_invite', 'community_join_approved', 'community_join_declined'], true)) {
            $community = Community::find($notification->related_id);

            return $community && $community->isVisibleTo(Auth::user())
                ? redirect()->route('community.show', $community->id)
                : redirect()->route('notifications.index', ['type' => 'community']);
        }

        if (in_array($notification->type, ['community_message', 'community_join_request'], true)
            && $notification->related_id !== null) {
            return redirect()->route('notifications.index', [
                'type' => 'community',
                'community' => $notification->related_id,
            ]);
        }

        // コミュニティのイベントの中止のお知らせは、そのイベントの詳細ページで中止の理由を見せる
        if ($notification->type === 'event_cancelled') {
            $event = Event::find($notification->related_id);

            // コミュニティを抜けるなどして見られなくなったイベントなら、コミュニティの通知一覧へ
            return $event && $event->isVisibleTo(Auth::user())
                ? redirect()->route('events.show', $event->id)
                : redirect()->route('notifications.index', ['type' => 'community']);
        }

        return view('notifications.show', compact('notification'));
    }

    public function markRead(UserNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) Auth::id(), 404);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return back();
    }
}