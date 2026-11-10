<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoCommunitySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $password = Hash::make('11111111');
            $topics = [
                ['Coffee and Conversation', 'Meet up', 'Learning'],
                ['Weekend Beach Walk', 'Sports', 'Outdoors'],
                ['Japanese Language Exchange', 'Exchange', 'Learning'],
                ['Creative Photography', 'Events', 'Creative'],
                ['Web Development Meetup', 'Jobs', 'Technology'],
                ['Morning Yoga', 'Sports', 'Wellness'],
                ['Local Food Adventure', 'Meet up', 'Other'],
                ['Board Game Night', 'Events', 'Other'],
                ['Watercolor Workshop', 'Events', 'Creative'],
                ['English Conversation', 'Exchange', 'Learning'],
                ['City Cycling', 'Sports', 'Outdoors'],
                ['Book Lovers Circle', 'Meet up', 'Learning'],
                ['Music Jam Session', 'Events', 'Creative'],
                ['Career Sharing', 'Jobs', 'Learning'],
                ['Sunset Hiking', 'Sports', 'Outdoors'],
                ['Beginner Coding', 'Jobs', 'Technology'],
                ['Mindful Meditation', 'Meet up', 'Wellness'],
                ['Cooking Together', 'Events', 'Other'],
                ['Travel Stories', 'Exchange', 'Other'],
                ['Community Picnic', 'Meet up', 'Outdoors'],
            ];

            foreach ($topics as $index => [$topic, $categoryName, $communityCategory]) {
                $number = $index + 1;
                $user = User::firstOrCreate(['email' => sprintf('demo%02d@example.test', $number)], [
                    'name' => fake()->name(),
                    'username' => sprintf('sana_demo_%02d', $number),
                    'password' => $password,
                    'email_verified_at' => now(),
                    'preferred_language' => 'en',
                ]);
                $user->profile()->firstOrCreate([], ['bio' => "I enjoy {$topic} and meeting new people in Cebu."]);

                $community = Community::firstOrCreate([
                    'owner_id' => $user->id, 'name' => 'Demo '.$topic.' Club',
                ], [
                    'description' => "A friendly group for people interested in {$topic}. Beginners are welcome!",
                    'category' => $communityCategory, 'location' => 'Cebu City',
                    'visibility' => 'public', 'language' => 'en',
                ]);
                $community->members()->syncWithoutDetaching([
                    $user->id => ['role' => 'owner', 'status' => 'approved', 'joined_at' => now()],
                ]);

                $start = now()->addDays($number + 1)->setTime(10, 0);
                $event = Event::firstOrCreate([
                    'owner_id' => $user->id, 'title' => 'Demo: '.$topic,
                ], [
                    'description' => "Join us for {$topic} in Cebu City. Meet new friends and enjoy a relaxed activity together. No experience is needed. Bring water and arrive ten minutes early.",
                    'language' => 'en', 'region' => 'Cebu', 'location' => 'Cebu IT Park, Cebu City',
                    'start_at' => $start, 'join_till' => $start->copy()->subDay(),
                    'end_at' => $start->copy()->addHours(2), 'capacity' => 20,
                    'price' => 0, 'approval_required' => false,
                    'visibility' => 'public', 'status' => 'published',
                ]);
                $category = Category::firstOrCreate(['name' => $categoryName]);
                $event->categories()->syncWithoutDetaching([$category->id]);
            }
        });
    }
}
