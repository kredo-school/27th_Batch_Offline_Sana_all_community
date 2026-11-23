<?php

namespace App\Http\Controllers;

use App\Models\Help;
use App\Models\HelpBookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HelpBookmarkController extends Controller
{
    public function store(Help $help)
    {
        abort_unless($help->status === 'published', 404);

        // 求人作成者本人は自分のHelpをブックマークしない
        abort_if(
            (int) $help->user_id === (int) Auth::id(),
            403
        );

        HelpBookmark::firstOrCreate([
            'Help_id' => $help->id,
            'user_id' => Auth::id(),
        ]);

        return back();
    }

    public function destroy(Help $help)
    {
        $help->bookmarks()
            ->where('user_id', Auth::id())
            ->delete();

        return back();
    }
}
