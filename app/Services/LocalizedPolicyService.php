<?php

namespace App\Services;

use App\Models\SiteSetting;
use App\Support\CategoryTranslator;
use Illuminate\Support\Facades\Log;

final class LocalizedPolicyService
{
    private const MAX_TRANSLATION_CHUNK_LENGTH = 3000;

    /**
     * @param  array<string, string>  $policies
     * @return array<string, string>
     */
    public function localizedPolicies(array $policies, string $locale): array
    {
        $localized = [];

        foreach ($policies as $suffix => $source) {
            $source = (string) $source;
            if ($source === '') {
                $localized[$suffix] = '';
                continue;
            }

            $translations = $this->readTranslations($suffix);
            $translation = $translations[$locale] ?? null;
            $localized[$suffix] = $locale === 'en'
                ? $source
                : (($translations['en'] ?? null) === $source
                    && is_string($translation)
                    && trim($translation) !== ''
                    && trim($translation) !== trim($source)
                    ? $translation
                    : $source);
        }

        return $localized;
    }

    public function translateForLocale(string $suffix, string $source, string $locale): ?string
    {
        if (trim($source) === '') {
            return null;
        }

        if ($locale === 'en') {
            return $source;
        }

        $translations = $this->readTranslations($suffix);
        $cached = $translations[$locale] ?? null;
        if (
            ($translations['en'] ?? null) === $source
            && is_string($cached)
            && trim($cached) !== ''
            && trim($cached) !== trim($source)
        ) {
            return $cached;
        }

        $translation = $this->translateText($source, $locale);
        if ($translation === null) {
            Log::warning('Policy translation is unavailable', [
                'policy' => $suffix,
                'target' => $locale,
            ]);

            return null;
        }

        $translations['en'] = $source;
        $translations[$locale] = $translation;
        SiteSetting::put($this->translationKey($suffix), $translations);

        return $translation;
    }

    public function translateUncached(string $source, string $locale): ?string
    {
        return trim($source) === '' ? null : $this->translateText($source, $locale);
    }

    public function translateAndStore(string $suffix, string $source): void
    {
        if ($source === '') {
            SiteSetting::put($this->translationKey($suffix), ['en' => '']);
            return;
        }

        $translations = ['en' => $source];

        foreach (array_keys(config('app.supported_locales', [])) as $locale) {
            if ($locale === 'en') {
                continue;
            }

            try {
                $translation = $this->translateText($source, $locale);
            } catch (\Throwable $exception) {
                Log::warning('Automatic policy translation failed', [
                    'policy' => $suffix,
                    'target' => $locale,
                    'error' => $exception->getMessage(),
                ]);
                $translation = null;
            }

            $translations[$locale] = $translation ?: $source;
        }

        SiteSetting::put($this->translationKey($suffix), $translations);
    }

    private function translateText(string $source, string $locale): ?string
    {
        $translatedChunks = [];

        foreach ($this->splitIntoChunks($source) as $chunk) {
            preg_match('/^\s+/u', $chunk, $leadingWhitespace);
            preg_match('/\s+$/u', $chunk, $trailingWhitespace);
            $content = trim($chunk);

            if ($content === '') {
                $translatedChunks[] = $chunk;
                continue;
            }

            $translation = app(CategoryTranslator::class)->translate($content, $locale, 'auto');
            if (! is_string($translation) || trim($translation) === '') {
                return null;
            }

            $translatedChunks[] = ($leadingWhitespace[0] ?? '')
                .trim($translation)
                .($trailingWhitespace[0] ?? '');
        }

        return implode('', $translatedChunks);
    }

    /**
     * @return list<string>
     */
    private function splitIntoChunks(string $text): array
    {
        $parts = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (! is_array($parts)) {
            throw new \RuntimeException('Unable to split policy text for translation.');
        }

        $chunks = [];
        $current = '';
        foreach ($parts as $part) {
            $partLength = mb_strlen($part, 'UTF-8');
            if ($partLength > self::MAX_TRANSLATION_CHUNK_LENGTH) {
                if ($current !== '') {
                    $chunks[] = $current;
                    $current = '';
                }

                for ($offset = 0; $offset < $partLength; $offset += self::MAX_TRANSLATION_CHUNK_LENGTH) {
                    $chunks[] = mb_substr($part, $offset, self::MAX_TRANSLATION_CHUNK_LENGTH, 'UTF-8');
                }

                continue;
            }

            if (
                $current !== ''
                && mb_strlen($current.$part, 'UTF-8') > self::MAX_TRANSLATION_CHUNK_LENGTH
            ) {
                $chunks[] = $current;
                $current = '';
            }

            $current .= $part;
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks ?: [$text];
    }

    /**
     * @return array<string, string>
     */
    private function readTranslations(string $suffix): array
    {
        $value = SiteSetting::get($this->translationKey($suffix), '{}');
        $translations = json_decode((string) $value, true);

        return is_array($translations)
            ? array_map(static fn ($translation): string => (string) $translation, $translations)
            : [];
    }

    private function translationKey(string $suffix): string
    {
        return 'policy_translations_'.$suffix;
    }
}
