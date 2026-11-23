<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Event;
use App\Models\Help;
use App\Models\User;
use App\Support\Search;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * 検索バーの下に出す候補（すべての検索バーで共通）
 * type: all（全体検索）/ communities / events / helps
 */
class SearchSuggestionController extends Controller
{
    private const TYPES = ['all', 'communities', 'events', 'helps'];

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:'.Search::MAX_LENGTH],
            'type' => ['nullable', 'in:'.implode(',', self::TYPES)],
        ]);
        $keyword = trim($validated['search'] ?? '');
        $type = $validated['type'] ?? 'all';

        if (Search::words($keyword) === []) {
            return response()->json(['items' => []]);
        }

        // 全体検索は種類ごとに少しずつ、ページの検索バーはその種類だけ多めに出す
        $limit = $type === 'all' ? 3 : 6;
        $items = collect()
            ->when(in_array($type, ['all', 'communities'], true), fn (Collection $items) => $items->concat($this->communities($keyword, $limit)))
            ->when(in_array($type, ['all', 'events'], true), fn (Collection $items) => $items->concat($this->events($keyword, $limit)))
            ->when(in_array($type, ['all', 'helps'], true), fn (Collection $items) => $items->concat($this->helps($keyword, $limit)))
            ->when($type === 'all', fn (Collection $items) => $items->concat($this->users($keyword, $limit)));

        return response()->json(['items' => $items->values()]);
    }

    private function communities(string $keyword, int $limit): Collection
    {
        return Community::query()
            ->visibleTo(Auth::user())
            ->search($keyword)
            ->withCount(['memberships as approved_members_count' => fn ($query) => $query->where('status', 'approved')])
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Community $community) => [
                'type' => 'community',
                'type_label' => __('Community'),
                'title' => $community->name,
                'subtitle' => collect([$community->localized_category, $community->location])->filter()->implode(' · '),
                'url' => route('community.show', $community->id),
            ]);
    }

    private function events(string $keyword, int $limit): Collection
    {
        return Event::query()
            ->listedFor(Auth::user())
            ->whereNull('community_id')
            ->where('end_at', '>=', now())
            ->search($keyword)
            ->orderBy('start_at')
            ->limit($limit)
            ->get()
            ->map(fn (Event $event) => [
                'type' => 'event',
                'type_label' => __('Event'),
                'title' => $event->title,
                'subtitle' => collect([$event->start_at?->format('M j, Y'), $event->location])->filter()->implode(' · '),
                'url' => route('events.show', $event->id),
            ]);
    }

    private function helps(string $keyword, int $limit): Collection
    {
        return Help::query()
            ->where('status', 'published')
            ->search($keyword)
            ->with('category')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Help $help) => [
                'type' => 'help',
                'type_label' => 'Help',
                'title' => $help->title,
                'subtitle' => collect([$help->category?->name, $help->location])->filter()->implode(' · '),
                'url' => route('helps.show', $help->id),
            ]);
    }

    private function users(string $keyword, int $limit): Collection
    {
        return User::query()
            ->search($keyword)
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (User $user) => [
                'type' => 'user',
                'type_label' => __('User'),
                'title' => $user->name,
                'subtitle' => $user->username ? '@'.$user->username : '',
                'url' => route('profile.show', $user->id),
            ]);
    }
}
