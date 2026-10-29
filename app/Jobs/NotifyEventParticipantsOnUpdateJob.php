<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyEventParticipantsOnUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public array $changedFields,
    ) {
    }

    public function handle(): void
    {
        $this->event->users()
            ->wherePivot('status', 'accepted')
            ->each(function ($user): void {
                UserNotification::create([
                    'user_id' => $user->id,
                    'type' => 'event_updated',
                    'title' => 'Event updated',
                    'message' => "The event '{$this->event->title}' has been updated.",
                    'related_type' => Event::class,
                    'related_id' => $this->event->id,
                ]);
            });
    }
}