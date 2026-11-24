<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHelpRequest;
use App\Http\Requests\UpdateHelpRequest;
use App\Models\Help;
use App\Models\HelpCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HelpController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'keyword' => ['nullable', 'string', 'max:100'],

            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('help_categories', 'id')
                    ->where('is_active', true),
            ],

            'event_date' => [
                'nullable',
                'date',
            ],

            'sort' => [
                'nullable',
                Rule::in([
                    'deadline_asc',
                    'deadline_desc',
                    'created_desc',
                ]),
            ],
        ]);

        $sort = $request->input('sort', 'created_desc');

        $query = Help::query()
            ->where('status', 'published')
            ->with([
                'user',
                'category',
            ])
            // サイト共通の検索ルール（タイトル・会社名・説明・場所・カテゴリー名。言葉を分けると全部を含むもの）
            ->when($request->filled('keyword'), fn ($query) => $query->search($request->input('keyword')))
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where(
                    'category_id',
                    $request->input('category_id')
                );
            })
            ->when($request->filled('event_date'), function ($query) use ($request) {
                // application_deadlineではなく、作業開始日だけで絞り込む
                $query->whereDate(
                    'event_start_at',
                    $request->input('event_date')
                );
            });

        if (Auth::check()) {
            $query->withCount([
                'bookmarks as is_bookmarked' => function ($bookmarkQuery) {
                    $bookmarkQuery->where(
                        'user_id',
                        Auth::id()
                    );
                },
            ]);
        }

        switch ($sort) {
            case 'deadline_asc':
                $query
                    ->orderByRaw('application_deadline IS NULL')
                    ->orderBy('application_deadline', 'asc');
                break;

            case 'deadline_desc':
                $query
                    ->orderByRaw('application_deadline IS NULL')
                    ->orderBy('application_deadline', 'desc');
                break;

            case 'created_desc':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        $helps = $query->paginate(18)->withQueryString();

        $categories = HelpCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('helps.index', compact(
            'helps',
            'sort',
            'categories'
        ));
    }

    public function create()
    {
        $categories = HelpCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('helps.create', compact('categories'));
    }

    public function store(StoreHelpRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request
                ->file('image')
                ->store('helps', 'public');
        }

        unset($data['image']);

        $help = Help::create([
            ...$data,
            'user_id' => Auth::id(),
            'status' => 'published',
            'published_at' => now(),
        ]);

        return redirect()
            ->route('helps.show', $help)
            ->with('success', 'Help created successfully.');
    }

    public function show(Help $help)
    {
        // 非公開求人は一般ユーザーに見せない
        if (
            $help->status !== 'published'
            && Auth::id() !== $help->user_id
        ) {
            abort(404);
        }

        $help->load([
            'user',
            'category',
        ]);

        return view('helps.detail', compact('help'));
    }

    public function edit(Help $help)
    {
        abort_unless(
            Auth::id() === $help->user_id,
            403
        );

        $categories = HelpCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('helps.edit', compact(
            'help',
            'categories'
        ));
    }

    public function update(UpdateHelpRequest $request, Help $help)
    {
        abort_unless(
            (int) Auth::id() === (int) $help->user_id,
            403
        );

        $data = $request->validated();

        // フォームの image はDBカラムではないため除外
        unset($data['image']);

        $oldImagePath = $help->image_path;
        $newImagePath = null;

        if ($request->hasFile('image')) {
            $newImagePath = $request
                ->file('image')
                ->store('helps', 'public');

            $data['image_path'] = $newImagePath;
        }

        $help->update($data);

        // DB更新後に古い画像を削除
        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()
            ->route('helps.show', $help)
            ->with('success', 'Help updated successfully.');
    }

    public function destroy(Help $help)
    {
        abort_unless(
            Auth::id() === $help->user_id,
            403
        );

        if ($help->applications()->exists()) {
            return back()->withErrors([
                'Help' => 'This Help cannot be deleted because it has applications.',
            ]);
        }

        if ($help->image_path) {
            Storage::disk('public')->delete($help->image_path);
        }

        $help->delete();

        return redirect()
            ->route('helps.index')
            ->with('success', 'Help deleted successfully.');
    }
}
