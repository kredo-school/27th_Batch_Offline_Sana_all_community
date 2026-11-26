<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\CategoryTranslator;
use App\Support\LanguageFileWriter;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    public function index(
        Request $request,
        LanguageFileWriter $languageFiles,
        CategoryTranslator $translator,
    )
    {
        $locale = $this->locale($request->query('locale'));
        $search = trim((string) $request->query('search'));
        $words = $languageFiles->read($locale);

        if ($search !== '') {
            $words = array_filter(
                $words,
                static fn (string $value, string $key): bool =>
                    str_contains(strtolower($key), strtolower($search))
                    || str_contains(strtolower($value), strtolower($search)),
                ARRAY_FILTER_USE_BOTH,
            );
        }

        ksort($words, SORT_NATURAL | SORT_FLAG_CASE);
        $totalWords = count($words);
        $perPage = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $words = new LengthAwarePaginator(
            array_slice($words, ($currentPage - 1) * $perPage, $perPage, true),
            $totalWords,
            $perPage,
            $currentPage,
            ['path' => $request->url()],
        );
        $words->withQueryString();

        $englishWords = $locale === 'en' ? $words : $languageFiles->read('en');
        $invalidTranslations = [];
        foreach ($words as $key => $value) {
            $sameAsEnglish = $locale !== 'en'
                && array_key_exists($key, $englishWords)
                && mb_strtolower(trim($value), 'UTF-8') === mb_strtolower(trim($englishWords[$key]), 'UTF-8');
            if ($sameAsEnglish || ! $translator->isValidForLocale($value, $locale)) {
                $invalidTranslations[] = $key;
            }
        }

        return view('admin.languages.index', [
            'locale' => $locale,
            'locales' => config('app.supported_locales'),
            'words' => $words,
            'invalidTranslations' => $invalidTranslations,
        ]);
    }

    public function create(Request $request)
    {
        $preferredLocale = $request->user()?->preferred_language;
        if (! array_key_exists($preferredLocale, config('app.supported_locales'))) {
            $preferredLocale = app()->getLocale();
        }

        return view('admin.languages.form', [
            'locales' => config('app.supported_locales'),
            'locale' => $preferredLocale,
            'preferredLocale' => $preferredLocale,
            'word' => null,
            'key' => '',
            'action' => route('admin.languages.store'),
            'method' => 'POST',
        ]);
    }

    public function store(
        Request $request,
        LanguageFileWriter $languageFiles,
        CategoryTranslator $translator,
    )
    {
        $validated = $this->validated($request);
        $englishWords = $languageFiles->read('en');

        $key = $validated['key'];
        if ($validated['locale'] === 'en' && array_key_exists($key, $englishWords)) {
            return back()->withInput()->withErrors([
                'key' => __('This word already exists in English.'),
            ]);
        }

        $sourceText = array_key_exists($key, $englishWords)
            ? $englishWords[$key]
            : ($validated['locale'] === 'en' ? $validated['value'] : $key);
        if (! array_key_exists($key, $englishWords)) {
            $englishWords[$key] = $sourceText;
            $languageFiles->write('en', $englishWords);
        }
        $failedLocales = [];

        foreach (array_keys(config('app.supported_locales')) as $targetLocale) {
            if ($targetLocale === 'en') {
                continue;
            }

            $targetWords = $languageFiles->read($targetLocale);
            if (
                array_key_exists($key, $targetWords)
                && $targetWords[$key] !== $sourceText
                && $translator->isValidForLocale($targetWords[$key], $targetLocale)
            ) {
                continue;
            }

            $translated = $translator->translate(
                $sourceText,
                $targetLocale,
                'en',
            );
            if ($translated === null) {
                $failedLocales[] = $targetLocale;
                continue;
            }

            $targetWords[$key] = $translated;
            $languageFiles->write($targetLocale, $targetWords);
        }

        $redirect = redirect()->route('admin.languages.index', ['locale' => $validated['locale']]);
        if ($failedLocales !== []) {
            $redirect->with('translation_failures', $failedLocales);
        }

        return $redirect;
    }

    public function edit(string $locale, string $key, LanguageFileWriter $languageFiles)
    {
        $this->ensureLocale($locale);
        $words = $languageFiles->read($locale);
        abort_unless(array_key_exists($key, $words), 404);

        return view('admin.languages.form', [
            'locales' => config('app.supported_locales'),
            'locale' => $locale,
            'preferredLocale' => null,
            'word' => $words[$key],
            'key' => $key,
            'action' => route('admin.languages.update', ['locale' => $locale, 'key' => $key]),
            'method' => 'PATCH',
        ]);
    }

    public function update(Request $request, string $locale, string $key, LanguageFileWriter $languageFiles)
    {
        $this->ensureLocale($locale);
        $words = $languageFiles->read($locale);
        abort_unless(array_key_exists($key, $words), 404);
        $validated = $this->validated($request, $locale);

        if ($validated['key'] !== $key && array_key_exists($validated['key'], $words)) {
            return back()->withInput()->withErrors([
                'key' => __('This word already exists in the selected language.'),
            ]);
        }

        unset($words[$key]);
        $words[$validated['key']] = $validated['value'];
        $languageFiles->write($locale, $words);

        return redirect()->route('admin.languages.index', ['locale' => $locale])
            ->with('status', __('Language word updated successfully.'));
    }

    public function destroy(string $locale, string $key, LanguageFileWriter $languageFiles)
    {
        $this->ensureLocale($locale);
        $words = $languageFiles->read($locale);
        abort_unless(array_key_exists($key, $words), 404);

        unset($words[$key]);
        $languageFiles->write($locale, $words);

        return redirect()->route('admin.languages.index', ['locale' => $locale]);
    }

    private function validated(Request $request, ?string $locale = null): array
    {
        $rules = [
            'key' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:1000'],
        ];

        if ($locale === null) {
            $rules['locale'] = ['required', Rule::in(array_keys(config('app.supported_locales')))];
        }

        $validated = $request->validate($rules);
        $validated['locale'] = $locale ?? $validated['locale'];

        return $validated;
    }

    private function locale(?string $locale): string
    {
        return array_key_exists($locale, config('app.supported_locales'))
            ? $locale
            : config('app.locale');
    }

    private function ensureLocale(string $locale): void
    {
        abort_unless(array_key_exists($locale, config('app.supported_locales')), 404);
    }
}
