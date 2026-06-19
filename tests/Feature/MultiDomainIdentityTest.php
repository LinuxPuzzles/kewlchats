<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * One install, multiple front doors: the door you sign up at becomes your home
 * domain (JID suffix), the localpart is globally unique across the community, and
 * your JID always reflects your home domain regardless of which door you browse.
 */
class MultiDomainIdentityTest extends TestCase
{
    use RefreshDatabase;

    private function signup(string $url, array $overrides = []): void
    {
        $spy = Mockery::mock(XmppProvisioner::class);
        $spy->shouldReceive('accountExists')->andReturnFalse();
        $this->app->instance(XmppProvisioner::class, $spy);

        $this->post($url, array_merge([
            'name' => 'Zed',
            'username' => 'zed',
            'email' => 'zed@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides));
    }

    public function test_signup_domain_comes_from_the_door(): void
    {
        $this->signup('http://ready2.im/register');

        $user = User::where('email', 'zed@example.com')->firstOrFail();

        $this->assertSame('ready2.im', $user->domain);
        $this->assertSame('zed@ready2.im', $user->jid());
    }

    public function test_localpart_is_unique_across_doors(): void
    {
        User::factory()->create(['xmpp_username' => 'taken', 'domain' => 'kewlchats.net']);

        // Try to grab the same localpart from the OTHER door.
        $this->from('http://ready2.im/register')
            ->signup('http://ready2.im/register', ['username' => 'taken', 'email' => 'other@example.com']);

        $this->assertSame(1, User::where('xmpp_username', 'taken')->count());
        $this->assertNull(User::where('email', 'other@example.com')->first());
    }

    public function test_jid_reflects_home_domain_not_the_browsed_door(): void
    {
        $user = User::factory()->create([
            'xmpp_username' => 'oguser',
            'domain' => 'kewlchats.net',
            'xmpp_status' => 'active',
            'xmpp_provisioned_at' => now(),
        ]);

        // An OG with a @kewlchats.net account, browsing the ready2.im door, must still
        // see their real address — theme is the door, data is the account.
        $this->actingAs($user)->get('http://ready2.im/dashboard')
            ->assertOk()
            ->assertSee('oguser@kewlchats.net');
    }
}
