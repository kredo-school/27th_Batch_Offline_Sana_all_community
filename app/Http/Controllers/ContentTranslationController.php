<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\EventReview;
use App\Models\UserReview;
use App\Services\MessageTranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentTranslationController extends Controller
{
    public function comment(
        Request $request,
        Comment $comment,
        MessageTranslationService $translator,
    ): JsonResponse {
        abort_unless($comment->event && $comment->event->isVisibleTo($request->user()), 404);

        return $this->translate($request, $comment, $comment->content, 'comment', $translator);
    }

    public function review(
        Request $request,
        EventReview $review,
        MessageTranslationService $translator,
    ): JsonResponse {
        abort_unless($review->event && $review->event->isVisibleTo($request->user()), 404);

        return $this->translate($request, $review, $review->comment, 'event_review', $translator);
    }

    public function hostReview(
        Request $request,
        UserReview $review,
        MessageTranslationService $translator,
    ): JsonResponse {
        abort_unless($review->event && $review->event->isVisibleTo($request->user()), 404);

        return $this->translate($request, $review, $review->comment, 'user_review', $translator);
    }

    private function translate(
        Request $request,
        \Illuminate\Database\Eloquent\Model $content,
        ?string $text,
        string $type,
        MessageTranslationService $translator,
    ): JsonResponse {
        if (! is_string($text) || trim($text) === '') {
            return response()->json(['message' => __('Translation is unavailable right now.')], 422);
        }

        $translation = $translator->translate(
            $content,
            (string) $text,
            $type,
            (string) $request->user()->preferred_language,
            'comment',
        );

        if ($translation === null) {
            return response()->json([
                'message' => __('Translation is unavailable right now.'),
            ], 422);
        }

        return response()->json([
            'translation' => $translation,
            'target_language' => $request->user()->preferred_language,
        ]);
    }
}
