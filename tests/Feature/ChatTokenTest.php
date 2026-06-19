<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_gets_a_chat_token_without_exposing_the_password(): void
    {
        $user = User::factory()->create([
            'xmpp_username' => 'alice',
            'xmpp_status' => 'active',
            'xmpp_pending_secret' => null,
        ]);

        $response = $this->actingAs($user)->getJson(route('chat.token'));

        $response->assertOk()->assertJsonStructure(['jid', 'password', 'expires_at']);

        // JID comes from the user's own domain (their row), not the browsed Host.
        $this->assertSame($user->jid(), $response->json('jid'));

        // The "password" field carries a minted, self-expiring token — never the
        // user's real secret or the hashed password.
        $this->assertStringStartsWith('mock-', $response->json('password'));
        $this->assertNotSame($user->getAuthPassword(), $response->json('password'));
    }

    public function test_token_is_refused_until_the_account_is_active(): void
    {
        $user = User::factory()->create([
            'xmpp_username' => 'bob',
            'xmpp_status' => 'pending',
        ]);

        $this->actingAs($user)->getJson(route('chat.token'))->assertStatus(409);
    }

    public function test_chat_page_requires_a_verified_email(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('chat'))->assertRedirect(route('verification.notice'));
    }
}
