<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyEventParticipantsOnUpdateJob;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query()->with(['owner', 'categories']);

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($ownerQuery) use ($search) {
                        $ownerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    })
                    ->orWhereHas('categories', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $sortOptions = [
            'latest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'event_asc' => ['title', 'asc'],
            'event_desc' => ['title', 'desc'],
        ];
        [$column, $direction] = $sortOptions[$request->query('sort')] ?? $sortOptions['latest'];

        $records = $query->orderBy($column, $direction)
            ->paginate(10)
            ->withQueryString();

        $title = 'Events';
        $columns = [
            'Title' => 'title',
            'Owner' => 'owner.name',
            'Categories' => 'category_names',
            'Starts' => 'start_at',
            'Join till' => 'join_till',
            'Ends' => 'end_at',
            'Approval' => 'approval_required',
            'Status' => 'status',
        ];

        return view('admin.sections.index', compact('title', 'columns', 'records'));
    }

    public function show(Event $event)
    {
        $event->load(['owner.profile', 'categories', 'community', 'reviews.user']);

        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $event->load('categories');
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.events.edit', compact('event', 'categories'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'language' => ['required', 'string', 'size:2'],
            'region' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_at' => ['required', 'date'],
            'join_till' => ['required', 'date', 'before_or_equal:start_at'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'approval_required' => ['required', 'boolean'],
            'visibility' => ['required', 'in:public,private'],
            'status' => ['required', 'in:draft,published,cancelled'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ]);

        $eventAttributes = collect($validated)->except('categories')->all();
        $changedFields = array_keys(collect($eventAttributes)->only($event->getFillable())->filter(
            fn ($value, $key) => $event->getAttribute($key) != $value
        )->all());

        $event->update($eventAttributes);
        $event->categories()->sync($validated['categories'] ?? []);

        if ($changedFields !== []) {
            NotifyEventParticipantsOnUpdateJob::dispatch($event, $changedFields)->afterCommit();
        }

        return redirect()->route('admin.events.edit', $event)
            ->with('status', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('admin.events')->with('status', 'Event deleted successfully.');
    }
}