<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;

class MessageTranslationService
{
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

        $cached = Translation::query()
            ->where('translatable_type', $type)
            ->where('translatable_id', $message->getKey())
            ->where('field', $field)
            ->where('target_language', $targetLocale)
            ->value('translated_text');

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        if (strtolower($targetLocale) === 'en') {
            $translated = $text;
        } else {
            $translated = app(LibreTranslateService::class)->translate($text, $targetLocale, 'auto');
        }

        if (! is_string($translated) || trim($translated) === '') {
            return null;
        }

        Translation::query()->updateOrCreate(
            [
                'translatable_type' => $type,
                'translatable_id' => $message->getKey(),
                'field' => $field,
                'target_language' => $targetLocale,
            ],
            [
                'source_language' => 'auto',
                'translated_text' => trim($translated),
            ],
        );

        return trim($translated);
    }
}
