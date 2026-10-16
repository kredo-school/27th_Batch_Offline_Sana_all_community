<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_pages_render_their_forms(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('EVENTS')
            ->assertSee('class="auth-main"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);

        $this->get('/register')
            ->assertOk()
            ->assertSee('EVENTS')
            ->assertSee('class="auth-main"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="username"', false)
            ->assertSee('name="preferred_language"', false)
            ->assertSee('name="password_confirmation"', false);
    }

    public function test_user_can_register_and_log_in(): void
    {
        $this->post('/register', [
            'name' => 'Sana User',
            'username' => 'sana-user',
            'preferred_language' => 'en',
            'email' => 'sana@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect('/home');

        $user = User::where('email', 'sana@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(password_verify('secret-password', $user->password));

        $this->post('/logout')->assertRedirect('/');

        $this->post('/login', [
            'email' => 'sana@example.com',
            'password' => 'secret-password',
        ])->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);
    }
}