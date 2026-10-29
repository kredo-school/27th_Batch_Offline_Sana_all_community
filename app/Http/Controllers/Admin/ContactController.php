<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function index()
    {
        $records = Contact::query()
            ->with('user')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $title = 'Contacts';
        $columns = [
            'Name' => 'name',
            'Username' => 'username',
            'Email' => 'email',
            'Detail' => 'detail',
            'Replied' => 'replied_at',
            'Received' => 'created_at',
        ];

        return view('admin.sections.index', compact('title', 'columns', 'records'));
    }

    public function show(Contact $contact)
    {
        $contact->load('user');
        $recipient = $contact->resolvedUser();

        return view('admin.contacts.show', compact('contact', 'recipient'));
    }

    public function reply(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $recipient = $contact->resolvedUser();

        if (! $recipient) {
            return back()->withErrors([
                'message' => 'This inquirer is not a registered user. Please reply by email instead.',
            ]);
        }

        abort_if((int) $recipient->id === (int) Auth::id(), 422, 'You cannot reply to your own inquiry.');

        UserNotification::create([
            'user_id' => $recipient->id,
            'sender_name' => 'Admin',
            'type' => 'general',
            'title' => 'Reply to your inquiry #' . $contact->id,
            'message' => trim($validated['message']),
        ]);

        $contact->update(['replied_at' => now()]);

        return back()->with('status', 'Reply sent to ' . ($recipient->username ?: $recipient->name) . '.');
    }
}
