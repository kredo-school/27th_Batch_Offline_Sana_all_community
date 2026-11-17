<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Community;
use App\Models\Event;
use App\Models\EventReview;
use App\Models\Follow;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserReview;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProfileFeatureDemoSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('status', 'active')->orderBy('id')->get();
        if ($users->count() < 2) {
            throw new RuntimeException('At least two active existing accounts are required.');
        }
        DB::transaction(function () use ($users) {
            $category = Category::firstOrCreate(['name' => 'Meet up']);
            $eventsByOwner = [];
            $communitiesByOwner = [];
            foreach ($users as $user) {
                // Fill only empty public profile fields; never change account or login details.
                $profile = $user->profile()->withTrashed()->firstOrCreate([], [
                    'bio' => 'I enjoy community events and meeting new people.',
                ]);
                if (!$profile->trashed()) {
                    foreach (['region' => 'Cebu', 'country' => 'Philippines', 'occupation' => 'Community Member'] as $field => $value) {
                        if (trim($profile->$field ?? '') === '') {
                            $profile->$field = $value;
                        }
                    }
                    $profile->save();
                }
                foreach ([
                    ['Coffee Meetup', 3, 'published'], ['City Walk', 7, 'published'],
                    ['Language Exchange', 10, 'published'], ['Creative Workshop', 14, 'published'],
                    ['Past Coffee Meetup', -10, 'published'], ['Past City Walk', -7, 'published'],
                    ['Draft Picnic', 20, 'draft'], ['Draft Game Night', 25, 'draft'],
                ] as $index => [$title, $days, $status]) {
                    $start = now()->addDays($days)->setTime(10, 0);
                    $event = Event::withTrashed()->firstOrCreate([
                        'owner_id' => $user->id, 'title' => 'Profile Demo: '.$title.' [user '.$user->id.']',
                    ], [
                        'description' => 'Sample event for checking profile filters, bookmarks, participation and reviews.',
                        'language' => 'en', 'region' => 'Cebu', 'location' => 'Cebu IT Park',
                        'start_at' => $start, 'end_at' => $start->copy()->addHours(2), 'join_till' => $start->copy()->subDay(),
                        'capacity' => 100, 'price' => 0, 'approval_required' => true,
                        'visibility' => 'public', 'status' => $status,
                    ]);
                    $eventsByOwner[$user->id][$index] = $event;
                    if (!$event->trashed()) {
                        $event->categories()->syncWithoutDetaching([$category->id]);
                    }
                }
                foreach (['Coffee Circle', 'Weekend Explorers'] as $index => $name) {
                    $community = Community::withTrashed()->firstOrCreate([
                        'owner_id' => $user->id, 'name' => 'Profile Demo: '.$name.' [user '.$user->id.']',
                    ], [
                        'description' => 'Sample community for checking profile community cards and links.',
                        'category' => $index === 0 ? 'Learning' : 'Outdoors', 'location' => 'Cebu City',
                        'visibility' => 'public', 'language' => 'en',
                    ]);
                    $communitiesByOwner[$user->id][] = $community;
                    if (!$community->trashed()) {
                        $this->membership($community, $user, 'owner');
                    }
                }
            }
            foreach ($users as $index => $user) {
                $host = $users[($index + 1) % $users->count()];
                foreach ($eventsByOwner[$host->id] as $slot => $event) {
                    if ($event->trashed() || $slot >= 6) {
                        continue;
                    }
                    $pending = in_array($slot, [2, 3], true);
                    DB::table('event_participants')->insertOrIgnore([
                        'event_id' => $event->id, 'user_id' => $user->id, 'status' => $pending ? 'pending' : 'approved',
                        'requested_at' => $event->start_at->copy()->subDays(2),
                        'responded_at' => $pending ? null : $event->start_at->copy()->subDay(),
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    if (in_array($slot, [0, 1, 4, 5], true)) {
                        DB::table('bookmarks')->insertOrIgnore([
                            'user_id' => $user->id, 'event_id' => $event->id, 'created_at' => now()->subHours($slot),
                        ]);
                    }
                    if ($slot >= 4) {
                        foreach ([EventReview::class => 'An enjoyable event with a friendly group. I would join again.',
                            UserReview::class => 'A welcoming host with clear communication and thoughtful planning.'] as $model => $comment) {
                            $model::withTrashed()->firstOrCreate(['event_id' => $event->id, 'user_id' => $user->id], [
                                'rating' => $slot === 4 ? 5 : 4, 'comment' => $comment,
                                'created_at' => $event->end_at->copy()->addHours(2),
                            ]);
                        }
                    }
                }
                foreach ($communitiesByOwner[$host->id] as $community) {
                    if (!$community->trashed()) {
                        $this->membership($community, $user, 'member');
                    }
                }
                for ($offset = 1; $offset <= min(2, $users->count() - 1); $offset++) {
                    $person = $users[($index + $offset) % $users->count()];
                    if (!UserBlock::blocksEitherWay($user->id, $person->id)) {
                        Follow::firstOrCreate(['follower_id' => $user->id, 'following_id' => $person->id]);
                    }
                }
            }
        });
        $this->command?->info('Profile demo data added for '.$users->count().' existing active accounts. Existing records and deleted demo records were preserved.');
    }

    private function membership(Community $community, User $user, string $role): void
    {
        DB::table('community_members')->insertOrIgnore([
            'community_id' => $community->id, 'user_id' => $user->id,
            'role' => $role, 'status' => 'approved', 'joined_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
