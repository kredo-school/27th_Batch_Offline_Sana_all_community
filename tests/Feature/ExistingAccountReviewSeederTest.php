<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventReview;
use App\Models\User;
use App\Models\UserReview;
use Database\Seeders\ExistingAccountReviewSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExistingAccountReviewSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_reviews_use_existing_accounts_and_preserve_edits_and_deletions_on_rerun(): void
    {
        $this->withoutVite();
        $account = User::factory()->create(['username' => 'existing_member', 'role' => 'user']);
        User::factory()->count(3)->create(['role' => 'user']);
        $originalAccount = $account->fresh()->getAttributes();
        $this->seed(ExistingAccountReviewSeeder::class);
        $this->assertSame(4, User::count());
        $this->assertSame($originalAccount, $account->fresh()->getAttributes());
        $this->assertSame(4, Event::count());
        $this->assertSame(6, EventReview::count());
        $this->assertSame(6, UserReview::count());
        $this->assertSame(3, EventReview::where('user_id', $account->id)->count());
        $this->assertSame(3, UserReview::where('user_id', $account->id)->count());
        $review = EventReview::where('user_id', $account->id)->first();
        $review->update(['rating' => 1, 'comment' => 'Keep my edits']);
        $deleted = UserReview::where('user_id', $account->id)->first();
        $deleted->delete();
        $this->seed(ExistingAccountReviewSeeder::class);
        $this->assertSame(4, Event::count());
        $this->assertSame(6, EventReview::withTrashed()->count());
        $this->assertSame(6, UserReview::withTrashed()->count());
        $this->assertSame('Keep my edits', $review->fresh()->comment);
        $this->assertSoftDeleted($deleted);
        $this->actingAs($account)->get(route('profile.show', ['id' => $account->id, 'tab' => 'review', 'review_type' => 'written']))
            ->assertOk()->assertSee('Keep my edits')->assertViewHas('writtenReviews', fn ($reviews) => $reviews->total() === 5);
        $this->get(route('profile.show', ['id' => $account->id, 'tab' => 'review', 'review_type' => 'host']))
            ->assertOk()->assertViewHas('hostReviews', fn ($reviews) => $reviews->total() === 3);
    }
}
