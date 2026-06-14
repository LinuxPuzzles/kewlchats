<?php

namespace App\Rules;

use App\Services\Xmpp\XmppProvisioner;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a desired XMPP localpart (the part before @kewlchats.net).
 *
 * We are intentionally stricter than XMPP's nodeprep so usernames are friendly
 * and unambiguous: lowercase letters, digits, dot, dash and underscore only.
 * The value is expected to already be lowercased by the caller. Also rejects a
 * blocklist of reserved/role/abuse-prone names and anything already taken on
 * the server.
 */
class XmppUsername implements ValidationRule
{
    /**
     * Names that must never be handed to a normal user.
     *
     * @var list<string>
     */
    protected array $reserved = [
        'admin', 'administrator', 'root', 'sysop', 'operator', 'webmaster',
        'hostmaster', 'postmaster', 'abuse', 'security', 'support', 'help',
        'info', 'contact', 'noreply', 'no-reply', 'mail', 'mailer-daemon',
        'kewlchats', 'team', 'staff', 'moderator', 'mod', 'system', 'server',
        'register', 'login', 'api', 'www', 'ftp', 'test', 'null', 'undefined',
        'conference', 'muc', 'pubsub', 'proxy', 'upload',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('Please choose a valid username.');

            return;
        }

        if (strlen($value) < 3 || strlen($value) > 30) {
            $fail('Username must be between 3 and 30 characters.');

            return;
        }

        if (! preg_match('/^[a-z0-9](?:[a-z0-9._-]*[a-z0-9])?$/', $value)) {
            $fail('Use only lowercase letters, numbers, dots, dashes and underscores (must start and end with a letter or number).');

            return;
        }

        if (str_contains($value, '..')) {
            $fail('Username cannot contain consecutive dots.');

            return;
        }

        if (in_array($value, $this->reserved, true)) {
            $fail('That username is reserved. Please choose another.');

            return;
        }

        if (app(XmppProvisioner::class)->accountExists($value)) {
            $fail('That username is already taken.');
        }
    }
}
