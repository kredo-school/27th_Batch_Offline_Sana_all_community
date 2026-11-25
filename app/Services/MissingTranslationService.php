<?php

namespace App\Services;

use App\Support\CategoryTranslator;
use App\Support\LanguageFileWriter;
use Illuminate\Support\Facades\Log;

final class MissingTranslationService
{
    /** @var array<string, array<string, string>> */
    private array $resolved = [];

    public function __construct(
        private readonly LanguageFileWriter $languageFiles,
        private readonly CategoryTranslator $translator,
    ) {}

    /**
     * Translate a missing JSON translation key and persist it for every locale.
     *
     * The key is used as the English source text because JSON translations use
     * the source phrase itself as their key.
     */
    public function translate(string $key, ?string $locale): ?string
    {
        $key = trim($key);
        $locale = $locale ?: config('app.locale', 'en');

        if ($key === '' || ! $this->shouldTranslate($key)) {
            return null;
        }

        if (isset($this->resolved[$key])) {
            return $this->resolved[$key][$locale] ?? $key;
        }

        $translations = ['en' => $key];

        foreach (array_keys(config('app.supported_locales', [])) as $targetLocale) {
            if ($targetLocale === 'en') {
                continue;
            }

            try {
                $translation = $this->translator->translate($key, $targetLocale);
            } catch (\Throwable $exception) {
                Log::warning('Automatic translation failed', [
                    'key' => $key,
                    'target' => $targetLocale,
                    'error' => $exception->getMessage(),
                ]);
                $translation = null;
            }

            if ($translation === null || trim($translation) === '') {
                Log::warning('Automatic translation unavailable', [
                    'key' => $key,
                    'target' => $targetLocale,
                ]);
                $translation = $key;
            }

            $translations[$targetLocale] = $translation;
        }

        try {
            $this->languageFiles->addMissingKey($key, $translations);
        } catch (\Throwable $exception) {
            Log::warning('Unable to persist automatic translation', [
                'key' => $key,
                'error' => $exception->getMessage(),
            ]);
        }

        $this->resolved[$key] = $translations;

        return $translations[$locale] ?? $key;
    }

    /**
     * Persist an externally translated English phrase in every locale file.
     * Existing keys remain unchanged.
     */
    public function persistExternalTranslation(
        string $englishText,
        string $targetLocale,
        string $translatedText,
    ): void {
        $englishText = trim($englishText);
        $targetLocale = strtolower(trim($targetLocale));
        $translatedText = trim($translatedText);

        if (
            $englishText === ''
            || $translatedText === ''
            || $targetLocale === 'en'
            || ! array_key_exists($targetLocale, config('app.supported_locales', []))
        ) {
            return;
        }

        $translations = [
            'en' => $englishText,
            $targetLocale => $translatedText,
        ];

        foreach (array_keys(config('app.supported_locales', [])) as $locale) {
            if ($locale === 'en' || $locale === $targetLocale) {
                continue;
            }

            try {
                $translation = $this->translator->translate($englishText, $locale);
            } catch (\Throwable $exception) {
                Log::warning('Automatic translation failed while saving external translation', [
                    'key' => $englishText,
                    'target' => $locale,
                    'error' => $exception->getMessage(),
                ]);
                $translation = null;
            }

            $translations[$locale] = is_string($translation) && trim($translation) !== ''
                ? trim($translation)
                : $englishText;
        }

        try {
            $this->languageFiles->addMissingKey($englishText, $translations);
        } catch (\Throwable $exception) {
            Log::warning('Unable to persist external translation', [
                'key' => $englishText,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function shouldTranslate(string $key): bool
    {
        // Translation keys for PHP groups and package namespaces are not JSON
        // UI phrases and should keep Laravel's normal missing-key behavior.
        return ! preg_match('/[^\x00-\x7F]/', $key)
            && ! str_contains($key, '::')
            && ! str_starts_with($key, 'validation.')
            && ! str_starts_with($key, 'passwords.')
            && ! str_starts_with($key, 'pagination.');
    }
}
