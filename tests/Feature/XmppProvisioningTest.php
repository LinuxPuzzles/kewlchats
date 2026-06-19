<?php

namespace Tests\Feature;

use App\Jobs\ProvisionXmppAccount;
use App\Jobs\SyncXmppPassword;
use App\Models\User;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class XmppProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_stashes_encrypted_secret_and_does_not_provision_before_verification(): void
    {
        // The provisioner must NOT be asked to register during signup.
        $spy = Mockery::mock(XmppProvisioner::class);
        $spy->shouldReceive('accountExists')->andReturnFalse();
        $spy->shouldNotReceive('register');
        $this->app->instance(XmppProvisioner::class, $spy);

        $this->post('/register', [
            'name' => 'Alice',
            'username' => 'alice',
            'email' => 'alice@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'alice@example.com')->firstOrFail();

        $this->assertSame('alice', $user->xmpp_username);
        $this->assertSame('pending', $user->xmpp_status);
        $this->assertNull($user->xmpp_provisioned_at);
        // Stash holds the chosen password (decrypts back via the cast).
        $this->assertSame('password', $user->xmpp_pending_secret);
        $this->assertSame('alice@'.$user->domain, $user->jid());
    }

    public function test_verifying_email_provisions_account_and_wipes_the_stash(): void
    {
        $user = User::factory()->unverified()->create([
            'xmpp_username' => 'bob',
            'xmpp_pending_secret' => 'password',
            'xmpp_status' => 'pending',
        ]);

        $mock = Mockery::mock(XmppProvisioner::class);
        $mock->shouldReceive('register')->once()->with('bob', 'password', $user->domain);
        $this->app->instance(XmppProvisioner::class, $mock);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($url);

        $user->refresh();
        $this->assertSame('active', $user->xmpp_status);
        $this->assertNotNull($user->xmpp_provisioned_at);
        $this->assertNull($user->xmpp_pending_secret);
    }

    public function test_verification_dispatches_provisioning_job(): void
    {
        Bus::fake();

        $user = User::factory()->unverified()->create([
            'xmpp_username' => 'carol',
            'xmpp_pending_secret' => 'password',
        ]);

        event(new Verified($user));

        Bus::assertDispatched(ProvisionXmppAccount::class, fn ($job) => $job->userId === $user->id);
    }

    public function test_password_change_syncs_to_xmpp(): void
    {
        Bus::fake();

        $user = User::factory()->create([
            'xmpp_username' => 'dave',
            'xmpp_status' => 'active',
        ]);

        $this->actingAs($user)->from('/profile')->put('/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors();

        Bus::assertDispatched(SyncXmppPassword::class, fn ($job) => $job->userId === $user->id && $job->newPassword === 'new-password');
    }

    public function test_dashboard_renders_without_revealing_password(): void
    {
        $user = User::factory()->create([
            'xmpp_username' => 'erin',
            'xmpp_status' => 'active',
            'xmpp_provisioned_at' => now(),
            // A leftover stash should never surface on the page.
            'xmpp_pending_secret' => 'S3cretSentinelValue',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee($user->jid());
        // Neither the secret stash nor the hashed password may appear.
        $response->assertDontSee('S3cretSentinelValue');
        $response->assertDontSee($user->getAuthPassword());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
