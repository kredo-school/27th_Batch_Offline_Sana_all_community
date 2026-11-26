<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class MessageTranslationService
{
    private const CACHE_VERSION = 'username-handles-v1';

    public function translate(
        Model $message,
        string $text,
        string $type,
        string $targetLocale,
        string $field = 'message',
    ): ?string
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $sourceHash = hash('sha256', self::CACHE_VERSION."\0".$text);
        $cached = Translation::query()
            ->where('translatable_type', $type)
            ->where('translatable_id', $message->getKey())
            ->where('field', $field)
            ->where('target_language', $targetLocale)
            ->first();

        if (
            $cached instanceof Translation
            && hash_equals($sourceHash, (string) $cached->source_hash)
            && is_string($cached->translated_text)
            && $cached->translated_text !== ''
        ) {
            return $cached->translated_text;
        }

        [$translationInput, $protectedHandles] = $this->protectHandles($text);
        $translated = app(LibreTranslateService::class)->translate($translationInput, $targetLocale, 'auto');

        if (! is_string($translated) || trim($translated) === '') {
            return null;
        }

        foreach ($protectedHandles as $placeholder => $handle) {
            if (substr_count(strtoupper($translated), strtoupper($placeholder)) !== 1) {
                Log::warning('Translation response did not preserve a username placeholder', [
                    'type' => $type,
                    'field' => $field,
                    'target_language' => $targetLocale,
                ]);

                return null;
            }
        }

        $translated = str_ireplace(array_keys($protectedHandles), array_values($protectedHandles), $translated);

        Translation::query()->updateOrCreate(
            [
                'translatable_type' => $type,
                'translatable_id' => $message->getKey(),
                'field' => $field,
                'target_language' => $targetLocale,
            ],
            [
                'source_language' => 'auto',
                'source_hash' => $sourceHash,
                'translated_text' => trim($translated),
            ],
        );

        return trim($translated);
    }

    /**
     * @return array{string, array<string, string>}
     */
    private function protectHandles(string $text): array
    {
        $handles = [];
        $prefix = 'ZXQ'.strtoupper(bin2hex(random_bytes(8))).'QXZ';
        $maskedText = preg_replace_callback(
            '/(?<![\p{L}\p{N}_-])@[\p{L}\p{N}_-]+/u',
            static function (array $matches) use (&$handles, $prefix): string {
                $placeholder = $prefix.count($handles).'QXZ';
                $handles[$placeholder] = $matches[0];

                return $placeholder;
            },
            $text,
        );

        if (! is_string($maskedText)) {
            throw new \RuntimeException('Failed to protect username handles before translation.');
        }

        return [$maskedText, $handles];
    }
}
