<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMessage;
use App\Models\Message;
use App\Services\MessageTranslationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MessageTranslationController extends Controller
{
    public function direct(Request $request, Message $message, MessageTranslationService $translator): JsonResponse
    {
        abort_unless(
            (int) $message->sender_id === (int) $request->user()->id
                || (int) $message->receiver_id === (int) $request->user()->id,
            403
        );

        return $this->translate($request, $message, 'message', $translator);
    }

    public function community(
        Request $request,
        $id,
        CommunityMessage $message,
        MessageTranslationService $translator,
    ): JsonResponse {
        $community = Community::findOrFail($id);
        abort_unless($community->hasMember($request->user()), 403);
        abort_unless((int) $message->community_id === (int) $community->id, 404);

        return $this->translate($request, $message, 'community_message', $translator);
    }

    private function translate(
        Request $request,
        Message|CommunityMessage $message,
        string $type,
        MessageTranslationService $translator,
    ): JsonResponse {
        $translation = $translator->translate(
            $message,
            (string) $message->message,
            $type,
            (string) $request->user()->preferred_language,
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
