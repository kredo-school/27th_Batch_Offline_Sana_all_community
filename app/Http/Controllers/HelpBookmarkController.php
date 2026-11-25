<?php

namespace App\Http\Controllers;

use App\Models\Help;
use App\Models\HelpBookmark;
use App\Models\HelpCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelpBookmarkController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $sort = $request->input('sort', 'created_desc');

        $query = $user
            ->bookmarkedHelps()
            ->where('helps.status', 'published')
            ->with([
                'user',
                'category',
            ])
            ->withCount([
                'bookmarks as is_bookmarked' => function ($bookmarkQuery) use ($user) {
                    $bookmarkQuery->where('user_id', $user->id);
                },
            ])
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = $request->input('keyword');

                $query->where(function ($query) use ($keyword) {
                    $query
                        ->where('helps.title', 'like', "%{$keyword}%")
                        ->orWhere('helps.company_name', 'like', "%{$keyword}%")
                        ->orWhere('helps.location', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where(
                    'helps.category_id',
                    $request->input('category_id')
                );
            });

        switch ($sort) {
            case 'deadline_asc':
                $query
                    ->orderByRaw('helps.application_deadline IS NULL')
                    ->orderBy('helps.application_deadline', 'asc');
                break;

            case 'deadline_desc':
                $query
                    ->orderByRaw('helps.application_deadline IS NULL')
                    ->orderBy('helps.application_deadline', 'desc');
                break;

            case 'created_desc':
            default:
                $query->orderByDesc('helps.created_at');
                break;
        }

        $helps = $query
            ->paginate(18)
            ->withQueryString();

        $categories = HelpCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('helps.bookmark', [
            'helps' => $helps,
            'sort' => $sort,
            'categories' => $categories,
            'filterRouteName' => 'helps.bookmarks.index',
        ]);
    }

    public function store(Request $request, Help $help): RedirectResponse
    {
        $user = $request->user();

        abort_unless($help->status === 'published', 404);

        // Help作成者本人はBookmark不可
        abort_if(
            (int) $help->user_id === (int) $user->id,
            403
        );

        HelpBookmark::firstOrCreate([
            'help_id' => $help->id,
            'user_id' => $user->id,
        ]);

        return back();
    }

    public function destroy(Request $request, Help $help): RedirectResponse
    {
        $help->bookmarks()
            ->where('user_id', $request->user()->id)
            ->delete();

        return back();
    }
}
