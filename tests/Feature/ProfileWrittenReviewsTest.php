<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventReview;
use App\Models\User;
use App\Models\UserReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileWrittenReviewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function event(User $owner): Event
    {
        return Event::create(['owner_id' => $owner->id, 'title' => 'Reviewed meetup',
            'start_at' => now()->subDays(2), 'end_at' => now()->subDay(), 'join_till' => now()->subDays(3),
            'visibility' => 'public', 'status' => 'published']);
    }

    private function listUrl(User $user, array $parameters = []): string
    {
        return route('profile.show', ['id' => $user->id, 'tab' => 'review', 'review_type' => 'written'] + $parameters);
    }

    public function test_written_reviews_combine_types_filter_paginate_and_stay_private(): void
    {
        $owner = User::factory()->create();
        $writer = User::factory()->create();
        $event = $this->event($owner);
        EventReview::create(['user_id' => $writer->id, 'event_id' => $event->id, 'rating' => 4, 'comment' => 'My event feedback']);
        UserReview::create(['user_id' => $writer->id, 'event_id' => $event->id, 'rating' => 5, 'comment' => 'My host feedback']);
        EventReview::create(['user_id' => $owner->id, 'event_id' => $event->id, 'rating' => 1, 'comment' => 'Someone else feedback']);
        $this->actingAs($writer)->get($this->listUrl($writer))->assertOk()
            ->assertSee('My event feedback')->assertSee('My host feedback')->assertDontSee('Someone else feedback')
            ->assertSee('Edit review')->assertSee('Delete review')
            ->assertViewHas('writtenReviews', fn ($reviews) => $reviews->total() === 2);
        foreach (['event', 'host'] as $type) {
            $this->get($this->listUrl($writer, ['written_type' => $type]))->assertOk()
                ->assertViewHas('writtenReviews', fn ($reviews) => $reviews->total() === 1 && $reviews->first()->review_kind === $type);
        }
        for ($i = 0; $i < 10; $i++) {
            $other = $this->event($owner);
            EventReview::create(['user_id' => $writer->id, 'event_id' => $other->id, 'rating' => 3]);
        }
        $this->get($this->listUrl($writer, ['written_type' => 'event']))->assertOk()
            ->assertViewHas('writtenReviews', fn ($reviews) => $reviews->count() === 10 && $reviews->total() === 11)
            ->assertSee('written_type=event', false)->assertSee('review_type=written', false);
        $this->get($this->listUrl($writer, ['written_type' => 'event', 'written_reviews_page' => 2]))->assertOk()
            ->assertViewHas('writtenReviews', fn ($reviews) => $reviews->count() === 1);
        $this->actingAs($owner)->get($this->listUrl($writer))->assertForbidden();
        $this->get(route('profile.show', $writer->id))->assertOk()->assertDontSee('Written reviews');
    }

    public function test_both_review_types_can_be_edited_and_deleted_only_by_their_author(): void
    {
        $owner = User::factory()->create();
        $writer = User::factory()->create();
        $event = $this->event($owner);
        foreach (['event' => EventReview::class, 'host' => UserReview::class] as $type => $model) {
            $review = $model::create(['user_id' => $writer->id, 'event_id' => $event->id, 'rating' => 5, 'comment' => 'Before']);
            $params = ['type' => $type, 'id' => $review->id];
            $edit = route('profile.reviews.edit', $params);
            $update = route('profile.reviews.update', $params);
            $delete = route('profile.reviews.destroy', $params);
            $this->actingAs($owner)->get($edit)->assertForbidden();
            $this->patch($update, ['rating' => 1])->assertForbidden();
            $this->delete($delete)->assertForbidden();
            $this->assertSame(5, (int) $review->fresh()->rating);
            $this->actingAs($writer)->get($edit)->assertOk()->assertSee('Before');
            $this->patch($update, ['rating' => 6, 'comment' => str_repeat('a', 1001)])
                ->assertSessionHasErrors(['rating', 'comment']);
            $this->patch($update, ['rating' => 3, 'comment' => 'Updated feedback', 'written_type' => $type])
                ->assertRedirect($this->listUrl($writer, ['written_type' => $type]));
            $this->assertSame(3, (int) $review->fresh()->rating);
            $this->assertSame('Updated feedback', $review->fresh()->comment);
            if ($type === 'host') {
                $this->get(route('profile.show', $owner->id))->assertOk()
                    ->assertViewHas('hostRatingSummary', fn ($summary) => (float) $summary->average_rating === 3.0 && (int) $summary->review_count === 1);
            }
            $this->delete($delete, ['written_type' => $type])->assertRedirect($this->listUrl($writer, ['written_type' => $type]));
            $this->assertSoftDeleted($review);
            $this->get($edit)->assertNotFound();
            $this->patch($update, ['rating' => 4])->assertNotFound();
        }
        $this->get($this->listUrl($writer))->assertOk()->assertSee('No written reviews yet.');
        $this->get(route('profile.show', $owner->id))->assertOk()
            ->assertViewHas('hostRatingSummary', fn ($summary) => (int) $summary->review_count === 0);
    }

    public function test_deleted_events_do_not_break_written_reviews_and_guests_cannot_manage_them(): void
    {
        $owner = User::factory()->create();
        $writer = User::factory()->create();
        $event = $this->event($owner);
        $review = UserReview::create(['user_id' => $writer->id, 'event_id' => $event->id, 'rating' => 4]);
        $params = ['type' => 'host', 'id' => $review->id];
        $this->get(route('profile.reviews.edit', $params))->assertRedirect(route('login'));
        $this->patch(route('profile.reviews.update', $params), ['rating' => 2])->assertRedirect(route('login'));
        $this->delete(route('profile.reviews.destroy', $params))->assertRedirect(route('login'));
        $event->delete();
        $this->actingAs($writer)->get($this->listUrl($writer))->assertOk()->assertSee('Event no longer available');
        $this->get(route('profile.reviews.edit', $params))->assertOk()->assertSee('Event no longer available');
        $this->delete(route('profile.reviews.destroy', $params))->assertRedirect();
        $this->assertSoftDeleted($review);
    }
}
