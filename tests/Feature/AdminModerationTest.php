<?php

namespace Tests\Feature;

use App\Jobs\BanXmppAccount;
use App\Jobs\UnbanXmppAccount;
use App\Models\User;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class AdminModerationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'xmpp_username' => 'boss',
            'xmpp_status' => 'active',
        ]);
    }

    public function test_non_admin_cannot_reach_admin_area(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.users'))
            ->assertForbidden();
    }

    public function test_admin_can_reach_admin_area(): void
    {
        $this->actingAs($this->admin())->get(route('admin.users'))->assertOk();
    }

    public function test_banning_a_user_blocks_both_sides(): void
    {
        Queue::fake();
        $target = User::factory()->create(['xmpp_username' => 'baddie', 'xmpp_status' => 'active']);

        $this->actingAs($this->admin())
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post(route('admin.users.ban', $target), ['reason' => 'spam'])
            ->assertRedirect();

        $target->refresh();
        $this->assertNotNull($target->banned_at);
        $this->assertSame('disabled', $target->xmpp_status);
        $this->assertSame('spam', $target->ban_reason);

        Queue::assertPushed(BanXmppAccount::class, fn ($job) => $job->username === 'baddie' && $job->reason === 'spam');
    }

    public function test_unban_reverses_both_sides(): void
    {
        Queue::fake();
        $target = User::factory()->create([
            'xmpp_username' => 'baddie',
            'xmpp_status' => 'disabled',
            'banned_at' => now(),
        ]);

        $this->actingAs($this->admin())->post(route('admin.users.unban', $target))->assertRedirect();

        $target->refresh();
        $this->assertNull($target->banned_at);
        $this->assertSame('active', $target->xmpp_status);
        Queue::assertPushed(UnbanXmppAccount::class);
    }

    public function test_admins_cannot_be_banned(): void
    {
        $other = User::factory()->create(['is_admin' => true, 'xmpp_username' => 'other']);

        $this->actingAs($this->admin())
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post(route('admin.users.ban', $other))
            ->assertSessionHasErrors('ban');

        $this->assertNull($other->fresh()->banned_at);
    }

    public function test_banned_user_cannot_log_in(): void
    {
        $user = User::factory()->create(['password' => 'password123', 'banned_at' => now()]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_create_a_channel(): void
    {
        $this->actingAs($this->admin())
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post(route('admin.channels.store'), [
                'localpart' => 'general',
                'name' => 'General',
                'description' => 'Talk about anything.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('channels', ['localpart' => 'general', 'name' => 'General']);
    }

    public function test_make_admin_command_promotes_a_user(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->artisan('kewlchats:make-admin', ['email' => $user->email])->assertSuccessful();

        $this->assertTrue($user->fresh()->is_admin);
    }

    public function test_admin_can_kick_sessions(): void
    {
        $spy = Mockery::spy(XmppProvisioner::class);
        $this->app->instance(XmppProvisioner::class, $spy);

        $target = User::factory()->create(['xmpp_username' => 'noisy', 'xmpp_status' => 'active']);

        $this->actingAs($this->admin())
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post(route('admin.users.kick', $target))->assertRedirect();

        $spy->shouldHaveReceived('kick')->with('noisy')->once();
    }

    public function test_admin_can_send_a_password_reset(): void
    {
        Notification::fake();
        $target = User::factory()->create();

        $this->actingAs($this->admin())
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post(route('admin.users.reset', $target))->assertRedirect();

        Notification::assertSentTo($target, ResetPassword::class);
    }

    public function test_admin_users_page_shows_stats(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('online now')
            ->assertSee('registered');
    }
}
