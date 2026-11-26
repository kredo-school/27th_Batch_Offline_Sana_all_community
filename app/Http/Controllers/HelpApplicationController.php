<?php

namespace App\Http\Controllers;

use App\Models\Help;
use App\Models\HelpApplication;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HelpApplicationController extends Controller
{
    public function store(Request $request, Help $help)
    {
        $validated = $request->validate([
            'cover_letter' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        if ($help->status !== 'published') {
            abort(404);
        }

        if (
            $help->application_deadline &&
            $help->application_deadline->isBefore(today())
        ) {
            return back()
                ->withErrors([
                    'application' => 'Applications are closed.',
                ])
                ->withInput();
        }

        if ((int) $help->user_id === (int) Auth::id()) {
            return back()
                ->withErrors([
                    'application' => 'You cannot apply to your own job.',
                ])
                ->withInput();
        }

        $alreadyApplied = $help->applications()
            ->where('user_id', Auth::id())
            ->exists();

        if ($alreadyApplied) {
            return back()
                ->withErrors([
                    'application' => 'You have already applied for this job.',
                ])
                ->withInput();
        }

        $help->applications()->create([
            'user_id' => Auth::id(),
            'cover_letter' => $validated['cover_letter'] ?? null,
            'status' => 'pending',
            'applied_at' => now(),
        ]);

        UserNotification::create([
            'user_id' => $help->user_id,
            'sender_name' => Auth::user()->username ?: 'Unknown user',
            'type' => 'help_application',
            'title' => 'New help application',
            'message' => (Auth::user()->username ?: 'Unknown user').' applied to your help "'.$help->title.'".',
            'related_type' => Help::class,
            'related_id' => $help->id,
        ]);

        return redirect()
            ->route('helps.show', $help)
            ->with('success', 'Your application has been submitted.');
    }

    /**
     * 掲載者向け応募者一覧
     */
    public function index(Help $help)
    {
        // 求人を作成した本人だけが閲覧可能
        abort_unless(
            Auth::id() === $help->user_id,
            403
        );

        $help->load('user');

        $applications = $help->applications()
            ->with('user')
            ->orderByDesc('applied_at')
            ->paginate(20);

        return view('helps.applications.index', [
            'help' => $help,
            'applications' => $applications,
        ]);
    }

    /**
     * 応募の承認・拒否
     */
    public function updateStatus(
        Request $request,
        Help $help,
        HelpApplication $application
    ) {
        abort_unless(
            Auth::id() === $help->user_id,
            403
        );

        // 別の求人の応募を操作できないようにする
        abort_unless(
            (int) $application->help_id === (int) $help->id,
            404
        );

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'accepted',
                    'rejected',
                ]),
            ],
        ]);

        if ($application->status !== 'pending') {
            return back()->withErrors([
                'application' => 'This application has already been processed.',
            ]);
        }

        $application->update([
            'status' => $validated['status'],
        ]);

        $accepted = $validated['status'] === 'accepted';

        UserNotification::create([
            'user_id' => $application->user_id,
            'sender_name' => Auth::user()->username ?: 'Unknown user',
            'type' => $accepted ? 'help_application_accepted' : 'help_application_rejected',
            'title' => $accepted ? 'Help application accepted' : 'Help application rejected',
            'message' => $accepted
                ? "Your application for '{$help->title}' was accepted."
                : "Your application for '{$help->title}' was rejected.",
            'related_type' => Help::class,
            'related_id' => $help->id,
        ]);

        return back()->with(
            'success',
            $accepted
                ? 'Applicant accepted.'
                : 'Applicant rejected.'
        );
    }
}
