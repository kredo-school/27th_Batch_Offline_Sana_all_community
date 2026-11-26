<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommunitiesController extends Controller
{
    public function index(Request $request)
    {
        $query = Community::query()
            ->with('owner')
            ->withCount(['memberships as members_count' => fn ($query) => $query->where('status', 'approved')])
            ->withExists(['memberships as owner_is_member' => fn ($query) => $query
                ->where('status', 'approved')
                ->whereColumn('user_id', 'communities.owner_id')]);

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($ownerQuery) use ($search) {
                        $ownerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        $sortOptions = [
            'latest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'community_asc' => ['name', 'asc'],
            'community_desc' => ['name', 'desc'],
        ];
        [$column, $direction] = $sortOptions[$request->query('sort')] ?? $sortOptions['latest'];

        $records = $query->orderBy($column, $direction)
            ->paginate(10)
            ->withQueryString();
        $records->getCollection()->each(function (Community $community): void {
            if ((int) $community->owner_is_member === 0) {
                $community->members_count++;
            }
        });

        $title = 'Communities';
        $columns = [
            'Name' => 'name',
            'Owner' => 'owner.username',
            'Members' => 'members_count',
            'Visibility' => 'visibility',
            'Language' => 'language',
            'Created' => 'created_at',
        ];

        return view('admin.sections.index', compact('title', 'columns', 'records'));
    }

    public function show(Community $community)
    {
        $community->load([
            'owner.profile',
            'events.owner',
            'events.categories',
        ])->loadCount([
            'memberships as members_count' => fn ($query) => $query->where('status', 'approved'),
            'events',
        ])->loadExists([
            'memberships as owner_is_member' => fn ($query) => $query
                ->where('status', 'approved')
                ->whereColumn('user_id', 'communities.owner_id'),
        ]);
        if ((int) $community->owner_is_member === 0) {
            $community->members_count++;
        }
        $memberships = $community->memberships()
            ->with('user.profile')
            ->orderByRaw("status = 'approved' desc")
            ->oldest('joined_at')
            ->get();

        return view('admin.communities.show', compact('community', 'memberships'));
    }

    public function edit(Community $community)
    {
        $community->load(['owner.profile', 'memberships.user.profile']);
        $categoryOptions = Category::query()
            ->with(['translations' => fn ($query) => $query
                ->where('field', 'name')
                ->where('target_language', app()->getLocale())])
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Category $category) => [$category->name => $category->localized_name]);

        if ($community->category && ! $categoryOptions->has($community->category)) {
            $categoryOptions->put($community->category, Community::getLocalizedCategoryName($community->category));
        }

        $users = User::query()
            ->withTrashed()
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'deleted_at']);
        $memberships = $community->memberships()
            ->with('user.profile')
            ->orderByRaw("status = 'approved' desc")
            ->oldest('joined_at')
            ->get();

        // オーナーを外すときに選べる、承認済みメンバー（オーナー自身は除く）
        $organizerCandidates = $memberships
            ->where('status', 'approved')
            ->where('user_id', '!=', $community->owner_id)
            ->filter(fn (CommunityMember $membership) => $membership->user !== null)
            ->values();

        return view('admin.communities.edit', compact(
            'community',
            'categoryOptions',
            'users',
            'memberships',
            'organizerCandidates',
        ));
    }

    public function replaceOwner(Request $request, Community $community)
    {
        $validated = $request->validate([
            'new_owner_id' => ['required', 'integer', 'exists:users,id'],
        ]);
        $newOwnerId = (int) $validated['new_owner_id'];
        $oldOwnerId = (int) $community->owner_id;
        abort_if($newOwnerId === $oldOwnerId, 422, __('Choose a different organizer.'));

        $membership = $community->memberships()
            ->where('user_id', $newOwnerId)
            ->where('status', 'approved')
            ->firstOrFail();

        DB::transaction(function () use ($community, $membership, $oldOwnerId) {
            // 新しいオーナーはメンバーの行を持たない形にそろえる
            $membership->delete();
            $community->update(['owner_id' => $membership->user_id]);

            // 外されたオーナーは通常のメンバーとして残る
            $community->memberships()->updateOrCreate(
                ['user_id' => $oldOwnerId],
                ['role' => 'member', 'status' => 'approved', 'joined_at' => now()],
            );
        });

        return redirect()->route('admin.communities')
            ->with('status', __('Organizer changed.'));
    }

    public function update(Request $request, Community $community)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'category' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'guidelines' => ['nullable', 'string', 'max:5000'],
            'language' => ['required', Rule::in(array_keys(config('app.supported_locales')))],
            'visibility' => ['required', 'in:public,approval,private'],
            'icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'remove_icon' => ['nullable', 'boolean'],
            'remove_cover_image' => ['nullable', 'boolean'],
        ]);

        $attributes = collect($validated)->except([
            'icon',
            'cover_image',
            'remove_icon',
            'remove_cover_image',
        ]);

        if (! $request->filled('owner_id')) {
            $attributes = $attributes->except('owner_id');
        }

        DB::transaction(function () use ($community, $attributes, $request, $validated): void {
            $oldOwnerId = (int) $community->owner_id;
            $newOwnerId = $request->filled('owner_id') ? (int) $validated['owner_id'] : $oldOwnerId;

            $community->update($attributes->all());

            if ($newOwnerId !== $oldOwnerId) {
                $oldOwnerMembership = $community->memberships()->firstOrNew(['user_id' => $oldOwnerId]);
                $oldOwnerMembership->fill([
                    'role' => 'member',
                    'status' => 'approved',
                    'joined_at' => $oldOwnerMembership->joined_at ?? now(),
                ]);
                $oldOwnerMembership->save();

                $community->memberships()
                    ->where('user_id', $newOwnerId)
                    ->delete();
                $community->update(['owner_id' => $newOwnerId]);
            }

            if ($request->boolean('remove_icon') && $community->icon) {
                Storage::disk('public')->delete($community->icon);
                $community->update(['icon' => null]);
            } elseif ($request->hasFile('icon')) {
                if ($community->icon) {
                    Storage::disk('public')->delete($community->icon);
                }
                $community->update([
                    'icon' => $request->file('icon')->store('communities/icons', 'public'),
                ]);
            }

            if ($request->boolean('remove_cover_image') && $community->image) {
                Storage::disk('public')->delete($community->image);
                $community->update(['image' => null]);
            } elseif ($request->hasFile('cover_image')) {
                if ($community->image) {
                    Storage::disk('public')->delete($community->image);
                }
                $community->update([
                    'image' => $request->file('cover_image')->store('communities/covers', 'public'),
                ]);
            }
        });

        return redirect()->route('admin.communities')
            ->with('status', __('Community updated successfully.'));
    }

    public function updateMemberRole(Request $request, Community $community, User $member)
    {
        abort_if((int) $community->owner_id === (int) $member->id, 422, __('The community owner cannot use a member role.'));

        $validated = $request->validate([
            'role' => ['required', 'in:member,moderator'],
        ]);

        $membership = CommunityMember::query()
            ->where('community_id', $community->id)
            ->where('user_id', $member->id)
            ->where('status', 'approved')
            ->firstOrFail();

        $membership->update(['role' => $validated['role']]);

        return redirect()->route('admin.communities')
            ->with('status', __('Community member role updated.'));
    }

    public function removeMember(Community $community, User $member)
    {
        abort_if((int) $community->owner_id === (int) $member->id, 422, __('The community owner cannot be removed here.'));

        CommunityMember::query()
            ->where('community_id', $community->id)
            ->where('user_id', $member->id)
            ->delete();

        return redirect()->route('admin.communities')
            ->with('status', __('Community member removed.'));
    }

    public function destroy(Community $community)
    {
        $community->delete();

        return redirect()->route('admin.communities')->with('status', __('Community deleted.'));
    }
}
