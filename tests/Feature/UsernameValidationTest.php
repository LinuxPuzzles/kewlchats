<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsernameValidationTest extends TestCase
{
    use RefreshDatabase;

    private function register(array $overrides = [])
    {
        return $this->post('/register', array_merge([
            'name' => 'Test User',
            'username' => 'validname',
            'email' => 'user'.uniqid().'@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides));
    }

    public function test_reserved_usernames_are_rejected(): void
    {
        $this->register(['username' => 'admin'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_invalid_characters_are_rejected(): void
    {
        $this->register(['username' => 'Bad Name!'])->assertSessionHasErrors('username');
    }

    public function test_too_short_username_is_rejected(): void
    {
        $this->register(['username' => 'ab'])->assertSessionHasErrors('username');
    }

    public function test_duplicate_username_is_rejected(): void
    {
        User::factory()->create(['xmpp_username' => 'taken']);

        $this->register(['username' => 'taken'])->assertSessionHasErrors('username');
    }

    public function test_username_is_lowercased_before_saving(): void
    {
        $this->register(['username' => 'MixedCase', 'email' => 'mc@example.com'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['xmpp_username' => 'mixedcase']);
    }

    public function test_honeypot_blocks_bots_without_creating_a_user(): void
    {
        // The suite runs Unbotable in observe-only mode; enforce here so the gate
        // actually blocks. A bot that trips the honeypot creates no account.
        config(['unbotable.on_block' => 'fake_success']);

        $this->register(['_unbotable_hp' => 'http://spam.example']);

        $this->assertGuestOrNoUser();
    }

    private function assertGuestOrNoUser(): void
    {
        $this->assertDatabaseCount('users', 0);
    }
}
