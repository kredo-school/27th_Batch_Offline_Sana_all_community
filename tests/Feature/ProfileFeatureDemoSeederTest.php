<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Event;
use App\Models\EventReview;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserReview;
use Database\Seeders\ProfileFeatureDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileFeatureDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_account_gets_profile_examples_without_overwriting_existing_data(): void
    {
        $this->withoutVite();
        $users = User::factory()->count(3)->create(['status' => 'active']);
        $users[0]->profile()->create(['bio' => 'Keep this bio', 'region' => 'Tokyo', 'occupation' => 'Designer']);
        UserBlock::block($users[1]->id, $users[0]->id);
        $this->seed(ProfileFeatureDemoSeeder::class);
        $this->assertSame(3, User::count());
        $this->assertSame(24, Event::count());
        $this->assertSame(6, Community::count());
        foreach ($users as $user) {
            $this->assertSame(8, $user->eventsOwned()->count());
            $this->assertSame(2, $user->attendingEvents()->wherePivot('status', 'pending')->count());
            $this->assertSame(4, $user->attendingEvents()->wherePivot('status', 'approved')->count());
            $this->assertSame(4, $user->bookmarkedEvents()->count());
            $this->assertSame(4, $user->communities()->count());
            $this->assertSame(2, EventReview::where('user_id', $user->id)->count());
            $this->assertSame(2, UserReview::where('user_id', $user->id)->count());
            foreach ([['tab' => 'event'], ['tab' => 'bookmark'], ['tab' => 'history'], ['tab' => 'feed'],
                ['tab' => 'community'], ['tab' => 'review', 'review_type' => 'host'],
                ['tab' => 'review', 'review_type' => 'written']] as $parameters) {
                $this->actingAs($user)->get(route('profile.show', ['id' => $user->id] + $parameters))->assertOk();
            }
        }
        $this->assertFalse($users[0]->following()->where('users.id', $users[1]->id)->exists());
        $this->assertSame('Tokyo', $users[0]->profile->fresh()->region);
        $this->assertSame('Designer', $users[0]->profile->fresh()->occupation);
        $this->assertSame('Keep this bio', $users[0]->profile->fresh()->bio);
        $review = EventReview::first();
        $review->update(['comment' => 'Keep edited review']);
        $deleted = UserReview::first();
        $deleted->delete();
        $this->seed(ProfileFeatureDemoSeeder::class);
        $this->assertSame(24, Event::count());
        $this->assertSame(6, Community::count());
        $this->assertSame(6, EventReview::withTrashed()->count());
        $this->assertSame(6, UserReview::withTrashed()->count());
        $this->assertSame('Keep edited review', $review->fresh()->comment);
        $this->assertSoftDeleted($deleted);
    }
}
