<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventsController extends Controller
{
    public function index()
    {
        $records = Event::query()
            ->with(['owner', 'categories'])
            ->latest()
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
}