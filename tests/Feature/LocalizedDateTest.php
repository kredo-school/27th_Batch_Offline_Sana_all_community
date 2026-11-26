<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserNotification;
use App\Support\LocalizedValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LocalizedDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_dates_follow_the_users_preferred_language(): void
    {
        $user = User::factory()->create(['preferred_language' => 'ja']);
        $date = Carbon::create(2026, 11, 25, 16, 45, 0, 'Asia/Manila');
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'type' => 'general',
            'title' => 'Localized date test',
            'message' => 'Notification detail',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $this->actingAs($user)
            ->get(route('notifications.show', $notification))
            ->assertOk()
            ->assertSee('2026年11月25日');
    }

    public function test_date_formatter_supports_cebuano_month_names(): void
    {
        $date = Carbon::create(2026, 11, 25, 16, 45, 0, 'Asia/Manila');
        $locale = app()->getLocale();
        app()->setLocale('ceb');

        try {
            $this->assertStringContainsString('Nobyembre', LocalizedValue::dateTime($date, 'date'));
        } finally {
            app()->setLocale($locale);
        }
    }
}
