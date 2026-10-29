<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Community;
use App\Models\Event;

class CommunityController extends Controller
{
    /**
     * 1. コミュニティ一覧・発見ページ (Discover)
     */
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('search'));

        $communities = Community::when($keyword !== '', function ($query) use ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('category', 'like', "%{$keyword}%");
            });
        })->withCount([
            'memberships as approved_members_count' => fn ($query) => $query->where('status', 'approved'),
        ])->withExists([
            'memberships as owner_is_member' => fn ($query) => $query->where('status', 'approved')
                ->whereColumn('user_id', 'communities.owner_id'),
        ])->get();
        $upcomingEvents = Event::with('community')
            ->where('status', 'published')
            ->where('start_at', '>=', now())
            ->whereHas('community')
            ->orderBy('start_at')
            ->limit(10)
            ->get();

        return view('community.index', compact('communities', 'upcomingEvents', 'keyword'));
    }

    /**
     * 2. コミュニティ新規作成フォーム画面
     */
    public function create()
    {
    $editing = false;
    $title = 'Create Community';
    return view('community.create', compact('editing', 'title'));
    }

    /**
     * 3. コミュニティ新規登録の保存処理
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
            'visibility' => 'required|in:public,approval,private',
            'guidelines' => 'nullable|string|max:5000',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $community = $request->user()->communitiesOwned()->make(collect($validated)->except([
            'icon',
            'cover_image',
        ])->all());

        if ($request->hasFile('icon')) {
            $community->icon = $request->file('icon')->store('communities/icons', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $community->image = $request->file('cover_image')->store('communities/covers', 'public');
        }

        $community->save();

        return redirect()
            ->route('community.index')
            ->with('success', 'コミュニティを作成しました。');
    }

    /**
     * 4. コミュニティ詳細ページ
     */
    public function show($id)
    {
        $community = Community::with([
            'owner.profile',
            'owner',
            'events' => fn ($query) => $query->where('start_at', '>=', now())
                ->orderBy('start_at')
                ->limit(5),
            'members' => fn ($query) => $query->wherePivot('status', 'approved')->with('profile'),
        ])->findOrFail($id);
        $allEvents = $community->events()->where('start_at', '>=', now())->orderBy('start_at')->get();
        $eventCount = $community->events()->count();
        $memberCount = $community->memberships()->where('status', 'approved')->count();
        if (! $community->memberships()->where('user_id', $community->owner_id)->where('status', 'approved')->exists()) {
            $memberCount++;
        }

        return view('community.show', compact('community', 'allEvents', 'eventCount', 'memberCount'));
    }

    public function storeEvent(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        abort_unless($community->owner_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:10000',
            'location' => 'nullable|string|max:255',
            'start_at' => 'required|date|after:now',
            'end_at' => 'required|date|after:start_at',
        ]);

        $community->events()->create([
            ...$validated,
            'owner_id' => $request->user()->id,
            'join_till' => $validated['start_at'],
        ]);

        return redirect()
            ->route('community.show', $community->id)
            ->with('success', 'イベントを投稿しました。');
    }

    /**
     * 5. コミュニティ編集画面
     */
    public function edit($id)
    {
        $editing = true;
        $title = 'Edit Community';
        $community = Community::findOrFail($id);
        abort_unless($community->owner_id === auth()->id(), 403);

        return view('community.create', compact('editing', 'title', 'community'));
    }

    /**
     * 6. コミュニティ更新処理
     */
    public function update(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        abort_unless($community->owner_id === auth()->id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
            'visibility' => 'required|in:public,approval,private',
            'guidelines' => 'nullable|string|max:5000',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $community->fill(collect($validated)->except(['icon', 'cover_image'])->all());

        // 画像を差し替えた場合は古いファイルを削除する
        if ($request->hasFile('icon')) {
            if ($community->icon) {
                Storage::disk('public')->delete($community->icon);
            }
            $community->icon = $request->file('icon')->store('communities/icons', 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($community->image) {
                Storage::disk('public')->delete($community->image);
            }
            $community->image = $request->file('cover_image')->store('communities/covers', 'public');
        }

        $community->save();

        return redirect()
            ->route('community.show', $community->id)
            ->with('success', 'コミュニティを更新しました。');
    }

    public function destroy($id)
    {
        $community = Community::findOrFail($id);
        abort_unless($community->owner_id === auth()->id(), 403);

        $community->delete();

        return redirect()
            ->route('community.index')
            ->with('success', 'コミュニティを削除しました。');
    }

}
