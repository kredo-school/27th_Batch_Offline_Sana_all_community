<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventReview;
use App\Models\User;
use App\Models\UserReview;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoReviewSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $password = Hash::make('11111111');
            $host = $this->demoUser('review-host@example.test', 'review_demo_host', 'Demo Review Host', $password);
            $start = now()->subDays(14)->setTime(10, 0);
            $event = Event::withTrashed()->firstOrCreate([
                'owner_id' => $host->id,
                'title' => 'Demo: Reviewed Community Meetup',
            ], [
                'description' => 'A completed community meetup with sample event and host reviews.',
                'language' => 'en', 'region' => 'Cebu', 'location' => 'Cebu IT Park, Cebu City',
                'start_at' => $start, 'end_at' => $start->copy()->addHours(2),
                'join_till' => $start->copy()->subDay(), 'capacity' => 20,
                'price' => 0, 'approval_required' => false,
                'visibility' => 'public', 'status' => 'published',
            ]);
            if ($event->trashed()) {
                $event->restore();
            }

            $eventComments = [
                'A fun afternoon with a welcoming group.',
                'The activities were well planned and easy to follow.',
                'A great opportunity to meet new people in Cebu.',
                'I enjoyed the meetup and would join again.',
                'Good atmosphere, though a little more time would help.',
                'The venue was convenient and the event started on time.',
                'A friendly event that was comfortable for beginners.',
                'Interesting conversations and a good mix of activities.',
                'The schedule felt a bit rushed, but I had a good time.',
                'An excellent community experience. Thank you!',
            ];
            $hostComments = [
                'The host made everyone feel included.',
                'Clear communication before and during the event.',
                'Friendly, approachable, and happy to answer questions.',
                'Everything was organized smoothly.',
                'Helpful host; a clearer schedule would make it even better.',
                'The host was prepared and kept us informed.',
                'Very welcoming to first-time participants.',
                'Thoughtful introductions helped everyone connect.',
                'Good support throughout the meetup.',
                'A wonderful host. I would attend another event!',
            ];

            foreach ($eventComments as $index => $comment) {
                $number = $index + 1;
                $reviewer = $this->demoUser(sprintf('review-participant%02d@example.test', $number),
                    sprintf('review_demo_participant_%02d', $number), sprintf('Demo Reviewer %02d', $number), $password);
                $reviewer->attendingEvents()->syncWithoutDetaching([
                    $event->id => ['status' => 'approved',
                        'requested_at' => $event->start_at->copy()->subDays(2),
                        'responded_at' => $event->start_at->copy()->subDay()],
                ]);
                $createdAt = $event->end_at->copy()->addHours($number);
                foreach ([EventReview::class => $comment, UserReview::class => $hostComments[$index]] as $model => $text) {
                    $review = $model::withTrashed()->firstOrCreate([
                        'event_id' => $event->id, 'user_id' => $reviewer->id,
                    ], ['rating' => [5, 4, 5, 4, 3, 5, 4, 5, 3, 5][$index], 'comment' => $text]);
                    if ($review->wasRecentlyCreated) {
                        $review->created_at = $createdAt;
                        $review->updated_at = $createdAt;
                        $review->save();
                    }
                    if ($review->trashed()) {
                        $review->restore();
                    }
                }
            }
        });
    }

    private function demoUser(string $email, string $username, string $name, string $password): User
    {
        $user = User::withTrashed()->firstOrCreate(['email' => $email], [
            'name' => $name, 'username' => $username, 'password' => $password, 'preferred_language' => 'en',
        ]);
        if ($user->trashed()) {
            $user->restore();
        }

        return $user;
    }
}
