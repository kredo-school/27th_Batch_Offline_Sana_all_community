<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use App\Models\UserBlock;
use App\Services\ProfileImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        // 招待のみのコミュニティのイベントは、そのコミュニティが見えない人のプロフィール表示には出さない
        $events = $user->eventsOwned()
            ->visibleTo(Auth::user())
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
                ->visibleTo(Auth::user())
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

        Follow::firstOrCreate([
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

    public function update(Request $request, ProfileImages $images)
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

            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:6144', Rule::prohibitedIf($request->boolean('remove_avatar'))],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:6144', Rule::prohibitedIf($request->boolean('remove_cover_image'))],
            'remove_avatar' => 'sometimes|boolean',
            'remove_cover_image' => 'sometimes|boolean',
            'avatar_crop' => 'sometimes|array:x,y,zoom',
            'avatar_crop.x' => 'required_with:avatar_crop|numeric|between:0,100',
            'avatar_crop.y' => 'required_with:avatar_crop|numeric|between:0,100',
            'avatar_crop.zoom' => 'required_with:avatar_crop|numeric|between:1,3',
            'cover_image_crop' => 'sometimes|array:x,y,zoom',
            'cover_image_crop.x' => 'required_with:cover_image_crop|numeric|between:0,100',
            'cover_image_crop.y' => 'required_with:cover_image_crop|numeric|between:0,100',
            'cover_image_crop.zoom' => 'required_with:cover_image_crop|numeric|between:1,3',
            'bio' => 'nullable|string|max:300',
            'region' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'birthday' => 'nullable|date|before_or_equal:today',
            'occupation' => 'nullable|string|max:100',
        ]);

        try {
            DB::transaction(function () use ($validated, $images) {
                $user = $this->user->findOrFail(Auth::id());
                $user->name = $validated['name'];
                $user->email = $validated['email'] ?? $user->email;
                $user->username = $validated['username'];
                $user->preferred_language = $validated['preferred_language'];
                $user->save();

                $profile = $user->profile()->firstOrCreate([]);
                $profile->bio = $validated['bio'] ?? null;
                $profile->country = $validated['country'] ?? null;
                foreach (['region', 'gender', 'birthday', 'occupation'] as $field) {
                    if (array_key_exists($field, $validated)) {
                        $profile->$field = $validated[$field];
                    }
                }
                if (array_key_exists('birthday', $validated)) {
                    $profile->age = $profile->birthday?->age;
                }
                $images->apply($profile, $validated);
                $profile->save();
            });
        } catch (\Throwable $error) {
            $images->discardNewFiles();
            throw $error;
        }
        $images->deleteObsoleteFiles();

        return redirect()
            ->route('profile.show', Auth::id())
            ->with('success', 'Profile updated successfully.');
    }
}
