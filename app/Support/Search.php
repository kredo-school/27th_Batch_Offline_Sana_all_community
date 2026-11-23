<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Community;
use Illuminate\Database\Eloquent\Builder;

/**
 * サイト内のすべての検索バーで共通の検索ルール
 * - 前後の空白は無視し、最大 100 文字まで
 * - 空白で区切った言葉は「全部を含む」ものを探す（baseball cebu → 両方を含む）
 * - 大文字・小文字は区別せず、部分一致で探す
 * - カテゴリーは、どの言語の表示名（スポーツ など）で探しても見つかる
 */
class Search
{
    public const MAX_LENGTH = 100;

    private const MAX_WORDS = 5;

    /**
     * 検索の文字を言葉ごとに分ける（空なら空の配列）
     */
    public static function words(?string $keyword): array
    {
        $keyword = mb_substr(trim((string) $keyword), 0, self::MAX_LENGTH);
        if ($keyword === '') {
            return [];
        }

        $words = preg_split('/\s+/u', $keyword, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_slice(array_values(array_unique($words)), 0, self::MAX_WORDS);
    }

    /**
     * 言葉ごとに条件を足す。$perWord には「その言葉が含まれる」条件を書く（項目どうしは orWhere でつなぐ）
     *
     * @param  callable(Builder $query, string $like, string $word): void  $perWord
     */
    public static function apply(Builder $query, ?string $keyword, callable $perWord): Builder
    {
        foreach (self::words($keyword) as $word) {
            $like = '%'.$word.'%';
            $query->where(fn (Builder $query) => $perWord($query, $like, $word));
        }

        return $query;
    }

    /**
     * どれかの言語の表示名に言葉が含まれる、イベントのカテゴリー（categories テーブル）の ID
     */
    public static function categoryIds(string $word): array
    {
        $needle = mb_strtolower($word);
        $like = '%'.$word.'%';

        // データベースにある名前と翻訳から探す
        $ids = Category::query()
            ->where('name', 'like', $like)
            ->orWhereHas('translations', fn ($query) => $query->where('field', 'name')->where('translated_text', 'like', $like))
            ->pluck('id');

        // プログラムに書いてある訳（Events → イベント など）からも探す
        $fromNames = Category::query()
            ->get(['id', 'name'])
            ->filter(fn (Category $category) => self::anyLocaleContains($category->name, $needle))
            ->pluck('id');

        return $ids->merge($fromNames)->unique()->values()->all();
    }

    /**
     * どれかの言語の表示名に言葉が含まれる、コミュニティのカテゴリー（保存している英語の値）
     */
    public static function communityCategories(string $word): array
    {
        $needle = mb_strtolower($word);

        return Community::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->filter(fn (string $category) => self::anyLocaleContains($category, $needle, Community::localizedCategoryNames($category)))
            ->values()
            ->all();
    }

    private static function anyLocaleContains(string $name, string $needle, array $extraNames = []): bool
    {
        $names = [$name, ...$extraNames];
        foreach (array_keys(config('app.supported_locales', ['en' => 'English'])) as $locale) {
            $names[] = Category::localizeName($name, $locale);
            $names[] = __($name, [], $locale);
        }

        foreach ($names as $candidate) {
            if (is_string($candidate) && $candidate !== '' && str_contains(mb_strtolower($candidate), $needle)) {
                return true;
            }
        }

        return false;
    }
}
