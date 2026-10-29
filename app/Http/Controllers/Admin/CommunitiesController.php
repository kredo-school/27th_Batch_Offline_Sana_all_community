<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Http\Request;

class CommunitiesController extends Controller
{
    public function index(Request $request)
    {
        $query = Community::query()
            ->with('owner')
            ->withCount(['memberships as members_count' => fn ($query) => $query->where('status', 'approved')]);

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

        $title = 'Communities';
        $columns = [
            'Name' => 'name',
            'Owner' => 'owner.name',
            'Members' => 'members_count',
            'Visibility' => 'visibility',
            'Language' => 'language',
            'Created' => 'created_at',
        ];

        return view('admin.sections.index', compact('title', 'columns', 'records'));
    }

    public function show(Community $community)
    {
        $community->load('owner')->loadCount([
            'memberships as members_count' => fn ($query) => $query->where('status', 'approved'),
            'events',
        ]);
        $memberships = $community->memberships()
            ->with('user')
            ->orderByRaw("status = 'approved' desc")
            ->oldest('joined_at')
            ->get();

        return view('admin.communities.show', compact('community', 'memberships'));
    }

    public function edit(Community $community)
    {
        return view('admin.communities.edit', compact('community'));
    }

    public function update(Request $request, Community $community)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'language' => ['required', 'string', 'size:2'],
            'visibility' => ['required', 'in:public,private'],
        ]);

        $community->update($validated);

        return redirect()->route('admin.communities.edit', $community)
            ->with('status', 'Community updated successfully.');
    }

    public function destroy(Community $community)
    {
        $community->delete();

        return redirect()->route('admin.communities')->with('status', 'Community deleted.');
    }
}
