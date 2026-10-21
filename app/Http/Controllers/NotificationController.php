<?php

namespace App\Http\Controllers;

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
                in_array($type, ['general', 'maintenance', 'community'], true),
                fn ($query) => $query->where('type', $type)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $title = 'Notifications';
        $columns = [
            'From' => 'sender_name',
            'Type' => 'type',
            'Title' => 'title',
            'Notification' => 'message',
            'Read at' => 'read_at',
            'Created' => 'created_at',
        ];

        return view('show', compact('title', 'columns', 'records'));
    }

    public function show(UserNotification $notification)
    {
        abort_unless((int) $notification->user_id === (int) Auth::id(), 404);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
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