<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    private $user;
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function show(Request $request, $id)
    {
        $user = $this->user->with('profile')->withCount([
            'attendingEvents',
            'eventsOwned' => function ($query) use ($id) {
                if ((int) Auth::id() !== (int) $id) {
                    $query->where('visibility', 'public')->where('status', 'published');
                }
            },
            'followers', 'following',
        ])->findOrFail($id);
        $events = $user->eventsOwned()
            ->with('categories')
            ->withExists(['bookmarkedBy as is_bookmarked' => fn ($query) => $query->where('users.id', Auth::id())])
            ->when(Auth::id() !== $user->id, function ($query) {
                $query->where('visibility', 'public')->where('status', 'published');
            })
            ->latest()
            ->get();
        $isBlockedByViewer = Auth::check()
            && Auth::id() !== $user->id
            && UserBlock::query()
                ->active()
                ->where('user_id', $user->id)
                ->where('blocked_by', Auth::id())
                ->exists();

        $bookmarkedEvents = Auth::id() === $user->id
            ? $user->bookmarkedEvents()
                ->with('categories')
                ->orderByPivot('created_at', 'desc')
                ->paginate(12, ['events.*'], 'bookmarks_page')
                ->appends(['tab' => 'bookmark'])
            : null;

        $historyEvents = Auth::id() === $user->id
            ? $user->attendingEvents()
                ->wherePivotIn('status', ['approved', 'pending'])
                ->with('categories')
                ->withExists(['bookmarkedBy as is_bookmarked' => fn ($query) => $query->where('users.id', Auth::id())])
                ->orderByDesc('events.start_at')
                ->orderByDesc('events.id')
                ->paginate(12, ['events.*'], 'history_page')
                ->appends(['tab' => 'history'])
            : null;

        $joinedCommunities = $user->communities()
            ->wherePivot('status', 'approved')
            ->when(Auth::id() !== $user->id, fn ($query) => $query->where('communities.visibility', 'public'))
            ->orderByPivot('joined_at', 'desc')
            ->orderByDesc('communities.id')
            ->paginate(12, ['communities.*'], 'communities_page')
            ->appends(['tab' => 'community']);

        $isFollowing = Auth::user()->following()->where('users.id', $user->id)->exists();
        $followBlocked = Auth::id() !== $user->id
            && UserBlock::blocksEitherWay(Auth::id(), $user->id);
        $connectionType = $request->query('connections');
        $connections = in_array($connectionType, ['followers', 'following'], true)
            ? $user->{$connectionType}()->with('profile')->orderBy('users.name')->orderBy('users.id')
                ->paginate(20, ['users.*'], 'connections_page')->appends(['connections' => $connectionType])
            : null;

        return view('profile.show', compact('user', 'events', 'isBlockedByViewer', 'bookmarkedEvents', 'historyEvents',
            'isFollowing', 'followBlocked', 'connectionType', 'connections', 'joinedCommunities'));

    }

    public function follow(Request $request, User $user)
    {
        abort_if($request->user()->id === $user->id, 403, 'You cannot follow yourself.');
        abort_if(UserBlock::blocksEitherWay($request->user()->id, $user->id), 403,
            'You cannot follow a blocked user.');

        \App\Models\Follow::firstOrCreate([
            'follower_id' => $request->user()->id,
            'following_id' => $user->id,
        ], ['created_at' => now()]);

        return redirect()->route('profile.show', $user->id)->with('success', 'You are now following this user.');
    }

    public function unfollow(Request $request, User $user)
    {
        $request->user()->following()->detach($user->id);

        return redirect()->route('profile.show', $user->id)->with('success', 'You unfollowed this user.');
    }

    public function edit()
    {
        $user = $this->user->with('profile')->withCount([
            'attendingEvents', 'eventsOwned', 'followers', 'following',
        ])->findOrFail(Auth::id());

        return view('profile.edit')->with('user', $user);
    }

    public function update(Request $request)
{
    if ($request->hasAny(['birthday_year', 'birthday_month', 'birthday_day'])) {
        $parts = $request->validate([
            'birthday_year' => 'nullable|required_with:birthday_month,birthday_day|integer|min:1|max:9999',
            'birthday_month' => 'nullable|required_with:birthday_year,birthday_day|integer|min:1|max:12',
            'birthday_day' => 'nullable|required_with:birthday_year,birthday_month|integer|min:1|max:31',
        ]);
        $request->merge(['birthday' => empty($parts['birthday_year']) ? null : sprintf(
            '%04d-%02d-%02d', $parts['birthday_year'], $parts['birthday_month'], $parts['birthday_day']
        )]);
    }

    $validated = $request->validate([
        'name' => 'required|string|min:1|max:255',
        'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore(Auth::id())],
        'preferred_language' => ['required', Rule::in(['en', 'ja'])],
        'email' => [
            'sometimes',
            'required',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore(Auth::id()),
        ],

        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:6144',
        'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:6144',
        'bio' => 'nullable|string|max:300',
        'region' => 'nullable|string|max:100',
        'country' => 'nullable|string|max:100',
        'gender' => 'nullable|in:male,female,other',
        'birthday' => 'nullable|date|before_or_equal:today',
        'occupation' => 'nullable|string|max:100',
    ]);

    
    $user = $this->user->findOrFail(Auth::id());

    $user->name = $validated['name'];
    $user->email = $validated['email'] ?? $user->email;
    $user->username = $validated['username'];
    $user->preferred_language = $validated['preferred_language'];

    $user->save();

    
    $profile = $user->profile()->firstOrCreate([]);

    
    $profile->bio = $validated['bio'] ?? null;
    if (array_key_exists('region', $validated)) {
        $profile->region = $validated['region'];
    }
    $profile->country = $validated['country'] ?? null;
    if (array_key_exists('gender', $validated)) {
        $profile->gender = $validated['gender'];
    }
    if (array_key_exists('birthday', $validated)) {
        $profile->birthday = $validated['birthday'];
        $profile->age = $profile->birthday?->age;
    }
    if (array_key_exists('occupation', $validated)) {
        $profile->occupation = $validated['occupation'];
    }


    if ($request->hasFile('avatar')) {
        $profile->avatar = $request->file('avatar')->store('avatars', 'public');
    }

    if ($request->hasFile('cover_image')) {
        $profile->cover_image = $request->file('cover_image')->store('covers', 'public');
    }

    $profile->save();

    return redirect()
        ->route('profile.show', $user->id)
        ->with('success', 'Profile updated successfully.');
    }
}
