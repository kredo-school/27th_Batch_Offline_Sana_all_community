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
                $type !== 'community',
                fn ($query) => $query->where('type', '!=', 'community_message')
            )
            ->when(
                $type === 'community',
                fn ($query) => $query->where(function ($query): void {
                    $query->communityFilter()
                        ->orWhere(function ($query): void {
                            $query->where('related_type', Community::class)
                                ->whereNotNull('related_id');
                        })
                        ->orWhere(function ($query): void {
                            $query->where('related_type', Event::class)
                                ->whereIn(
                                    'related_id',
                                        Event::withTrashed()->select('id')->whereNotNull('community_id'),
                                );
                        });
                })
            )
            ->when(
                in_array($type, ['general', 'maintenance'], true),
                fn ($query) => $query->where('type', $type)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Keep canonical titles for view logic; Blade translations handle display.
        $title = $type === 'community' ? 'Community' : 'Notifications';
        $columns = [
            __('From') => 'sender_name',
            __('Type') => 'type',
            __('Title') => 'title',
            __('Notification') => 'message',
            __('Read at') => 'read_at',
            __('Created') => 'created_at',
        ];

        return view('layouts.home.show', compact('title', 'columns', 'records'));
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
            return $this->eventNotificationRedirect((int) $notification->related_id, true);
        }

        if ($notification->type === 'help_application' && $notification->related_id !== null) {
            return redirect()->route('helps.applications.index', $notification->related_id);
        }

        if (in_array($notification->type, [
            'help_application_accepted',
            'help_application_rejected',
        ], true) && $notification->related_id !== null) {
            return redirect()->route('helps.show', $notification->related_id);
        }

        if (in_array($notification->type, ['event_joined', 'participant_cancelled'], true)
            && $notification->related_id !== null) {
            return $this->eventNotificationRedirect((int) $notification->related_id, true);
        }

        if (in_array($notification->type, [
            'event_comment',
            'event_updated',
            'event_join_approved',
            'event_join_rejected',
        ], true) && $notification->related_id !== null) {
            return $this->eventNotificationRedirect((int) $notification->related_id);
        }

        // 招待・参加申請の結果のお知らせは、そのコミュニティのページを開く
        // （招待が取り消されて見られなくなっていたら、コミュニティの通知一覧へ）
        if (in_array($notification->type, ['community_invite', 'community_join_approved', 'community_join_declined', 'community_moderator_added', 'community_ownership_transferred', 'community_approval'], true)) {
            $community = Community::find($notification->related_id);

            return $community && $community->isVisibleTo(Auth::user())
                ? redirect()->route('community.show', $community->id)
                : redirect()->route('notifications.index', ['type' => 'community']);
        }

        if ($notification->type === 'community_message' && $notification->related_id !== null) {
            return redirect()->to(route('community.show', $notification->related_id).'#chat');
        }

        if ($notification->type === 'community_join_request' && $notification->related_id !== null) {
            return redirect()->route('community.show', $notification->related_id);
        }

        // コミュニティのイベントの中止のお知らせは、そのイベントの詳細ページで中止の理由を見せる
        if ($notification->type === 'event_cancelled') {
            return $this->eventNotificationRedirect((int) $notification->related_id);
        }

        return view('notifications.show', compact('notification'));
    }

    private function eventNotificationRedirect(int $eventId, bool $participants = false): RedirectResponse
    {
        $event = Event::withTrashed()->find($eventId);
        if ($event && $event->isVisibleTo(Auth::user())) {
            return $participants
                ? redirect()->route('events.participants', $event)
                : redirect()->route('events.show', $event);
        }

        return $event?->community_id
            ? redirect()->route('notifications.index', ['type' => 'community'])
            : redirect()->route('notifications.index');
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