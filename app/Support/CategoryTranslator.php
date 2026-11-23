<?php

namespace App\Support;

use App\Services\LibreTranslateService;

final class CategoryTranslator
{
    /**
     * Translates an English word into the target locale using LibreTranslate.
     * Returns null when no service is configured or the request fails.
     */
    public function translate(string $text, string $targetLocale, string $sourceLocale = 'en'): ?string
    {
        $service = app(LibreTranslateService::class);
        $translated = $service->translate($text, $targetLocale, $sourceLocale);

        $translated = $translated ? $this->repairDoubleEncoding(trim($translated)) : '';
        if (
            $translated !== ''
            && strtolower($targetLocale) !== strtolower($sourceLocale)
            && mb_strtolower(trim($text), 'UTF-8') === mb_strtolower($translated, 'UTF-8')
        ) {
            $translated = '';
        }

        if ($translated !== '' && ! $this->isValidForLocale($translated, $targetLocale)) {
            $translated = '';
        }

        $googleEnabled = filter_var(
            config('services.google_translate.enabled', true),
            FILTER_VALIDATE_BOOLEAN,
        );
        if ($translated === '' && $googleEnabled) {
            $googleTranslated = $service->translateWithGoogleFallback($text, $targetLocale, $sourceLocale);
            $translated = $googleTranslated
                ? $this->repairDoubleEncoding(trim($googleTranslated))
                : '';
        }

        if (
            $translated !== ''
            && strtolower($targetLocale) !== strtolower($sourceLocale)
            && mb_strtolower(trim($text), 'UTF-8') === mb_strtolower($translated, 'UTF-8')
        ) {
            $translated = '';
        }

        return $translated !== '' && $this->isValidForLocale($translated, $targetLocale)
            ? $translated
            : null;
    }

    public function isValidForLocale(string $text, string $locale): bool
    {
        $text = trim($text);
        if ($text === '') {
            return false;
        }

        return match (strtolower($locale)) {
            'ja' => (bool) preg_match('/[\p{Han}\p{Hiragana}\p{Katakana}]/u', $text),
            'zh' => (bool) preg_match('/\p{Han}/u', $text),
            'ko' => (bool) preg_match('/\p{Hangul}/u', $text),
            'ru' => (bool) preg_match('/\p{Cyrillic}/u', $text),
            'ar' => (bool) preg_match('/\p{Arabic}/u', $text),
            'th' => (bool) preg_match('/\p{Thai}/u', $text),
            default => ! preg_match('/[\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}\p{Cyrillic}\p{Arabic}\p{Thai}]/u', $text),
        };
    }

    /**
     * LibreTranslate can return Arabic UTF-8 bytes re-encoded as Latin-1 (e.g. "Ø§Ù…"),
     * so decode them back when the text contains only Latin-1 characters.
     */
    private function repairDoubleEncoding(string $text): string
    {
        if (! preg_match('/[\x{0080}-\x{00FF}]/u', $text) || preg_match('/[^\x{0000}-\x{00FF}]/u', $text)) {
            return $text;
        }

        $repaired = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');

        return mb_check_encoding($repaired, 'UTF-8') ? $repaired : $text;
    }
}
