<?php

namespace App\Http\Controllers;

use App\Services\LibreTranslateService;
use App\Services\MissingTranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TranslationController extends Controller
{
    public function index()
    {
        return view('translate', [
            'supportedLocales' => config('app.supported_locales'),
        ]);
    }

    public function translate(
        Request $request,
        LibreTranslateService $translator,
        MissingTranslationService $missingTranslations,
    ): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:5000'],
            'source' => ['required', 'string', Rule::in(['auto', ...array_keys(config('app.supported_locales'))])],
            'target' => ['required', 'string', Rule::in(array_keys(config('app.supported_locales')))],
        ]);

        $translation = $translator->translate(
            $validated['text'],
            $validated['target'],
            $validated['source'],
        );

        if ($translation === null) {
            return response()->json([
                'message' => __('Translation is unavailable right now.'),
            ], 422);
        }

        if ($validated['source'] === 'en' && $validated['target'] !== 'en') {
            $missingTranslations->persistExternalTranslation(
                $validated['text'],
                $validated['target'],
                $translation,
            );
        }

        return response()->json([
            'translation' => $translation,
            'source' => $validated['source'],
            'target' => $validated['target'],
        ]);
    }
}
