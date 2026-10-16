<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;

class CommunitiesController extends Controller
{
    public function index()
    {
        $records = Community::query()
            ->with('owner')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $title = 'Communities';
        $columns = [
            'Name' => 'name',
            'Owner' => 'owner.name',
            'Visibility' => 'visibility',
            'Language' => 'language',
            'Created' => 'created_at',
        ];

        return view('admin.sections.index', compact('title', 'columns', 'records'));
    }
}