<?php

namespace Tests\Feature;

use App\Jobs\SyncXmppPassword;
use App\Models\User;
use App\Services\Xmpp\EjabberdApiProvisioner;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    // --- C3: re-auth on destructive admin actions --------------------------------

    public function test_destructive_admin_action_requires_password_confirmation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $target = User::factory()->create(['xmpp_username' => 'x']);

        // No recent password confirmation → bounced to the confirm screen.
        $this->actingAs($admin)
            ->post(route('admin.users.ban', $target))
            ->assertRedirect(route('password.confirm'));
    }

    // --- C4: enumeration + throttle ----------------------------------------------

    public function test_password_reset_never_reveals_membership(): void
    {
        $this->post(route('password.email'), ['email' => 'nobody@example.com'])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();
    }

    public function test_forgot_password_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('password.email'), ['email' => 'a@b.com']);
        }

        $this->post(route('password.email'), ['email' => 'a@b.com'])->assertStatus(429);
    }

    // --- C5: chat token scope ----------------------------------------------------

    public function test_chat_token_is_issued_with_sasl_auth_scope_only(): void
    {
        config(['xmpp.api.base' => 'http://ejabberd.test/api', 'xmpp.api.token' => 'tok']);
        Http::fake(['*oauth_issue_token*' => Http::response(['token' => 'abc', 'expires_in' => '600'])]);

        (new EjabberdApiProvisioner)->issueChatToken('alice');

        Http::assertSent(fn ($req) => str_contains($req->url(), 'oauth_issue_token')
            && str_contains($req->body(), '"scopes":"sasl_auth"')
            && ! str_contains($req->body(), 'ejabberd:admin'));
    }

    // --- C2: sync drift visibility + reconcile -----------------------------------

    public function test_failed_password_sync_flags_desync(): void
    {
        $user = User::factory()->create(['xmpp_username' => 'drift', 'xmpp_status' => 'active']);

        (new SyncXmppPassword($user->id, 'newpw'))->failed(new \RuntimeException('ejabberd down'));

        $user->refresh();
        $this->assertNotNull($user->xmpp_desynced_at);
        $this->assertSame('password', $user->xmpp_desync_reason);
    }

    public function test_reconcile_redrives_ban_drift_and_clears_the_flag(): void
    {
        $spy = Mockery::spy(XmppProvisioner::class);
        $this->app->instance(XmppProvisioner::class, $spy);

        $user = User::factory()->create([
            'xmpp_username' => 'banned',
            'banned_at' => now(),
            'xmpp_desynced_at' => now(),
            'xmpp_desync_reason' => 'ban',
        ]);

        $this->artisan('kewlchats:reconcile')->assertSuccessful();

        $spy->shouldHaveReceived('ban')->once();
        $this->assertNull($user->fresh()->xmpp_desynced_at);
    }

    // --- M1: secret not mass-assignable ------------------------------------------

    public function test_secret_and_status_are_not_mass_assignable(): void
    {
        $user = User::create([
            'name' => 'X',
            'email' => 'x@example.com',
            'password' => 'password123',
            'xmpp_username' => 'x',
            'xmpp_pending_secret' => 'leak-attempt',
            'xmpp_status' => 'active',
        ]);

        $this->assertNull($user->fresh()->xmpp_pending_secret);
        $this->assertNotSame('active', $user->fresh()->xmpp_status); // DB default, not the injected value
    }
}
