<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');

        // A send to a whole role creates one row per user; list it once.
        $firstOfBroadcast = UserNotification::query()
            ->selectRaw('MIN(id)')
            ->whereNotNull('recipient_label')
            ->groupBy('recipient_label', 'sender_name', 'type', 'title', 'message', 'created_at');

        $records = UserNotification::query()
            ->with('user')
            ->where(fn ($query) => $query
                ->whereNull('recipient_label')
                ->orWhereIn('id', $firstOfBroadcast))
            ->when(
                in_array($type, ['general', 'maintenance', 'community'], true),
                fn ($query) => $query->where('type', $type)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $title = 'Notifications';
        $columns = [
            'User' => 'recipient_name',
            'From' => 'sender_name',
            'Type' => 'type',
            'Title' => 'title',
            'Message' => 'message',
            'Read at' => 'read_at',
            'Created' => 'created_at',
        ];

        return view('admin.sections.index', compact('title', 'columns', 'records'));
    }

    public function create()
    {
        $users = User::query()->orderBy('name')->get(['id', 'name', 'username']);

        return view('admin.notifications.create', [
            'users' => $users,
            'notification' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(true));

        $recipients = in_array($validated['user_id'], ['admin', 'user'], true)
            ? User::query()->where('role', $validated['user_id'])->pluck('id')
            : collect([(int) $validated['user_id']]);

        $sender = $this->senderName($validated['from']);
        unset($validated['from']);

        $label = $validated['user_id'] === 'user' ? 'All users' : ($validated['user_id'] === 'admin' ? 'All admins' : null);

        foreach ($recipients as $userId) {
            UserNotification::create(array_merge($validated, [
                'user_id' => $userId,
                'sender_name' => $sender,
                'recipient_label' => $label,
            ]));
        }

        return redirect()->route('admin.notifications')->with(
            'status',
            $recipients->count() > 1
                ? "Notification sent to {$recipients->count()} users."
                : 'Notification created.'
        );
    }

    public function show(UserNotification $notification)
    {
        $notification->load('user');

        return view('admin.notifications.show', compact('notification'));
    }

    public function edit(UserNotification $notification)
    {
        $users = User::query()->orderBy('name')->get(['id', 'name', 'username']);

        return view('admin.notifications.edit', compact('notification', 'users'));
    }

    public function update(Request $request, UserNotification $notification)
    {
        $validated = $request->validate($this->rules());
        $validated['sender_name'] = $this->senderName($validated['from']);
        unset($validated['from']);

        $notification->update($validated);

        return redirect()->route('admin.notifications')->with('status', 'Notification updated.');
    }

    public function destroy(UserNotification $notification)
    {
        $notification->delete();

        return redirect()->route('admin.notifications')->with('status', 'Notification deleted.');
    }

    private function senderName(string $from): string
    {
        return $from === 'self' ? (Auth::user()->username ?: Auth::user()->name) : 'Admin';
    }

    private function rules(bool $allowRole = false): array
    {
        return [
            'from' => ['required', 'in:admin,self'],
            'user_id' => $allowRole
                ? ['required', function ($attribute, $value, $fail) {
                    if (! in_array($value, ['admin', 'user'], true) && ! User::whereKey($value)->exists()) {
                        $fail('The selected recipient is invalid.');
                    }
                }]
                : ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'read_at' => ['nullable', 'date'],
        ];
    }
}