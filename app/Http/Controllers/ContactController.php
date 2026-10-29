<?php

namespace App\Http\Controllers;

use App\Models\Contact;
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

        Contact::create($validated + ['user_id' => Auth::id()]);

        return back()->with('contact_status', 'Your inquiry has been sent. We will get back to you soon.');
    }
}
