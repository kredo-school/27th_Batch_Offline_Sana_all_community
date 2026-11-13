<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validateWithBag('contact', [
            'name' => ['required', 'string', 'max:100'],
            'username' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'detail' => ['required', 'string', 'max:2000'],
        ]);

        $contact = Contact::create($validated + ['user_id' => Auth::id()]);
        $this->notifyAdmins($contact, 'New contact inquiry');

        return back()->with('contact_status', 'Your inquiry has been sent. We will get back to you soon.');
    }

    public function report(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 422, 'You cannot report yourself.');

        $validated = $request->validate([
            'detail' => ['required', 'string', 'max:2000'],
        ]);

        $contact = Contact::create([
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'username' => $user->username,
            'email' => $request->user()->email,
            'detail' => "Report about user {$user->name} (ID: {$user->id}):\n\n{$validated['detail']}",
        ]);
        $this->notifyAdmins($contact, 'New user report');

        return back()->with('contact_status', 'Your report has been sent to the administrators.');
    }

    private function notifyAdmins(Contact $contact, string $title): void
    {
        User::query()->where('role', 'admin')->pluck('id')->each(function (int $adminId) use ($contact, $title): void {
            UserNotification::create([
                'user_id' => $adminId,
                'sender_name' => $contact->username ?: $contact->name,
                'type' => 'admin_contact',
                'title' => $title,
                'message' => $contact->detail,
                'related_type' => Contact::class,
                'related_id' => $contact->id,
            ]);
        });
    }
}
