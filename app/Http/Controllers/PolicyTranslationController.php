<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Services\LocalizedPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PolicyTranslationController extends Controller
{
    private const POLICY_SUFFIXES = [
        'terms',
        'guidelines',
        'report',
        'event',
        'job',
        'advertising',
        'p2p',
    ];

    public function translate(
        Request $request,
        string $suffix,
        LocalizedPolicyService $policies,
    ): JsonResponse {
        abort_unless(in_array($suffix, self::POLICY_SUFFIXES, true), 404);

        $validated = $request->validate([
            'source' => ['nullable', 'string', 'max:20000'],
        ]);

        $storedSource = SiteSetting::get('policy_'.$suffix, '');
        $sourceIsStored = is_string($storedSource) && trim($storedSource) !== '';
        $source = $sourceIsStored ? $storedSource : (string) ($validated['source'] ?? '');
        if (trim($source) === '') {
            return response()->json([
                'message' => __('Policy text is unavailable right now.'),
            ], 422);
        }

        $locale = $request->user()?->preferred_language ?: app()->getLocale();
        if (! array_key_exists($locale, config('app.supported_locales', []))) {
            $locale = (string) config('app.locale');
        }

        $translation = $sourceIsStored
            ? $policies->translateForLocale($suffix, $source, $locale)
            : $policies->translateUncached($source, $locale);

        if ($translation === null) {
            return response()->json([
                'message' => __('Translation is unavailable right now.'),
            ], 422);
        }

        return response()->json([
            'translation' => $translation,
            'target_language' => $locale,
        ]);
    }
}
