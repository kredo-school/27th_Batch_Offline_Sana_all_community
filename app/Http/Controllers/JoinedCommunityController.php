<?php

namespace App\Http\Controllers;

use App\Models\CommunityMember;
use Illuminate\Support\Facades\Auth;

class JoinedCommunityController extends Controller
{
    public function index()
    {
        $records = CommunityMember::query()
            ->with('community')
            ->where('user_id', Auth::id())
            ->where('status', 'approved')
            ->whereHas('community')
            ->latest('joined_at')
            ->paginate(15)
            ->withQueryString();

        $title = 'Joined Communities';
        $columns = [
            'Community' => 'community.name',
            'Description' => 'community.description',
            'Role' => 'role',
            'Joined' => 'joined_at',
        ];

        return view('show', compact('title', 'columns', 'records'));
    }
}