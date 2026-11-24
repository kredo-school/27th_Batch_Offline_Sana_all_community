<?php

namespace App\Services;

use App\Models\SiteSetting;
use App\Support\CategoryTranslator;
use Illuminate\Support\Facades\Log;

final class LocalizedPolicyService
{
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
            $localized[$suffix] = $locale === 'en'
                ? $source
                : ($translations[$locale] ?? $source);
        }

        return $localized;
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
                $translation = app(CategoryTranslator::class)->translate($source, $locale);
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
