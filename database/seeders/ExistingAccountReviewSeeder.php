<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventReview;
use App\Models\User;
use App\Models\UserReview;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExistingAccountReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->where(function ($query) {
            $query->whereNull('role')->orWhere('role', '!=', 'admin');
        })->orderBy('id')->get();
        $account = $users->first(fn ($user) => !str_starts_with($user->username ?? '', 'review_demo_')
            && !str_starts_with($user->username ?? '', 'sana_demo_')) ?? $users->first();
        $others = $users->reject(fn ($user) => $user->id === $account?->id)->take(3);
        if (!$account || $others->isEmpty()) {
            throw new RuntimeException('At least two existing non-admin accounts are required.');
        }

        DB::transaction(function () use ($account, $others) {
            $host = $others->first();
            foreach (['Coffee and Conversation', 'Community City Walk', 'Weekend Language Exchange'] as $index => $title) {
                $event = $this->demoEvent($host, 'Demo Review: '.$title.' [account '.$account->id.']', $index + 7);
                $this->addReviews($event, $account, [5, 4, 3][$index],
                    ['A welcoming meetup with interesting conversations.', 'A relaxing walk and a good chance to meet new people.', 'A helpful language exchange. More practice time would make it even better.'][$index],
                    ['The host made introductions easy and included everyone.', 'Friendly host with clear directions and good communication.', 'The host was supportive and patient with beginners.'][$index]);
            }
            $hosted = $this->demoEvent($account, 'Demo Review: Hosted Community Meetup [account '.$account->id.']', 5);
            foreach ($others->values() as $index => $reviewer) {
                $this->addReviews($hosted, $reviewer, [5, 4, 5][$index],
                    ['Great atmosphere and well-planned activities.', 'An enjoyable meetup. I would join again.', 'A lovely opportunity to connect with the community.'][$index],
                    ['Welcoming and thoughtful host. Thank you!', 'The host kept everyone informed and comfortable.', 'Everything was organized smoothly. Highly recommended.'][$index]);
            }
        });

        $this->command?->info('Added demo reviews for '.$account->name.' (@'.$account->username.', ID '.$account->id.'). Existing reviews and account details were preserved.');
    }

    private function demoEvent(User $host, string $title, int $daysAgo): Event
    {
        $start = now()->subDays($daysAgo)->setTime(10, 0);
        return Event::withTrashed()->firstOrCreate(['owner_id' => $host->id, 'title' => $title], [
            'description' => 'Demo event for previewing received and written reviews.',
            'language' => 'en', 'region' => 'Cebu', 'location' => 'Cebu IT Park',
            'start_at' => $start, 'end_at' => $start->copy()->addHours(2), 'join_till' => $start->copy()->subDay(),
            'capacity' => 20, 'price' => 0, 'approval_required' => false,
            'visibility' => 'public', 'status' => 'published',
        ]);
    }

    private function addReviews(Event $event, User $writer, int $rating, string $eventComment, string $hostComment): void
    {
        // Preserve deleted demo events and reviews when this seeder is rerun.
        if ($event->trashed()) {
            return;
        }
        DB::table('event_participants')->insertOrIgnore([
            'event_id' => $event->id, 'user_id' => $writer->id, 'status' => 'approved',
            'requested_at' => $event->start_at->copy()->subDays(2),
            'responded_at' => $event->start_at->copy()->subDay(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ([EventReview::class => $eventComment, UserReview::class => $hostComment] as $model => $comment) {
            $model::withTrashed()->firstOrCreate(['event_id' => $event->id, 'user_id' => $writer->id], [
                'rating' => $rating, 'comment' => $comment,
                'created_at' => $event->end_at->copy()->addHours($writer->id % 12 + 1),
            ]);
        }
    }
}
