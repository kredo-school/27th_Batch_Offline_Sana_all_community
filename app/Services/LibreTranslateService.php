<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class LibreTranslateService
{
    public function translate(string $text, string $targetLang, string $sourceLang = 'en'): ?string
    {
        if (trim($text) === '') {
            return '';
        }

        $googleEnabled = filter_var(
            config('services.google_translate.enabled', true),
            FILTER_VALIDATE_BOOLEAN,
        );

        $translated = $this->translateWithLibreTranslate($text, $targetLang, $sourceLang);
        if (
            $translated !== null
            && ! $this->isUntranslatedText($text, $translated)
        ) {
            return $translated;
        }

        if (! $googleEnabled) {
            return null;
        }

        $googleTranslated = $this->translateWithGoogleFallback($text, $targetLang, $sourceLang);

        return $googleTranslated !== null
            && ! $this->isUntranslatedText($text, $googleTranslated)
                ? $googleTranslated
                : null;
    }

    private function translateWithLibreTranslate(string $text, string $targetLang, string $sourceLang): ?string
    {
        $configuredUrl = config('services.libretranslate.url');
        if (! is_string($configuredUrl) || trim($configuredUrl) === '') {
            return null;
        }

        $baseUrl = rtrim($configuredUrl, '/');
        $apiKey  = config('services.libretranslate.key');

        $payload = [
            'q'      => $text,
            'source' => $sourceLang,
            'target' => $targetLang,
            'format' => 'text',
        ];

        if (!empty($apiKey)) {
            $payload['api_key'] = $apiKey;
        }

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->post("{$baseUrl}/translate", $payload);

            if ($response->successful()) {
                return $response->json('translatedText');
            }

            Log::warning('LibreTranslate API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('LibreTranslate connection failed: ' . $e->getMessage());
        }

        return null;
    }

    private function isUntranslatedText(string $source, string $translated): bool
    {
        $normalize = static fn (string $value): string => mb_strtolower(
            trim(preg_replace('/\s+/u', ' ', $value)),
            'UTF-8',
        );

        return $normalize($source) === $normalize($translated);
    }

    public function translateWithGoogleFallback(
        string $text,
        string $targetLang,
        string $sourceLang = 'en',
    ): ?string
    {
        $targetLang = match (strtolower($targetLang)) {
            'zh' => 'zh-CN',
            default => strtolower($targetLang),
        };
        $sourceLang = strtolower($sourceLang);

        $verifyAttempts = [true, false];
        $attempt = 0;
        foreach ($verifyAttempts as $verify) {
            $attempt++;
            try {
                $tr = new GoogleTranslate(
                    $targetLang,
                    $sourceLang,
                    [
                        'timeout' => 15,
                        'connect_timeout' => 10,
                        'verify' => $verify,
                        'headers' => [
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                            'Accept' => 'application/json,text/plain,*/*',
                        ],
                    ],
                );
                $translated = $tr->translate($text);
                if (is_string($translated) && trim($translated) !== '') {
                    return $translated;
                }
            } catch (\Throwable $e) {
                Log::warning('Google translation fallback attempt failed', [
                    'attempt' => $attempt,
                    'source' => $sourceLang,
                    'target' => $targetLang,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }
}
