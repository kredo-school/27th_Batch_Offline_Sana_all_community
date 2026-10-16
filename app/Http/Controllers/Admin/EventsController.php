<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventsController extends Controller
{
    public function index()
    {
        $records = Event::query()
            ->with('owner')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $title = 'Events';
        $columns = [
            'Title' => 'title',
            'Owner' => 'owner.name',
            'Starts' => 'start_at',
            'Status' => 'status',
        ];

        return view('admin.sections.index', compact('title', 'columns', 'records'));
    }
}