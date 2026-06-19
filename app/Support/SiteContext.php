<?php

namespace App\Support;

use Illuminate\Support\Facades\View;

/**
 * Resolves the "current site" (front door) and activates its brand/theme/mail/XMPP
 * config. One install serves multiple domains; the browsed Host picks the door for a
 * web request, while async work (jobs/mail) activates the door of the *user* being
 * acted on (their `domain` column), since a queue worker has no Host header.
 *
 * Hosts not in config('sites.sites') fall back to the plain .env values, so dev/test
 * (single tenant, one .env) keeps working with no registry entry.
 */
class SiteContext
{
    /** @var array<string, mixed>|null the active site */
    protected static ?array $current = null;

    /** @return array<string, array<string, string>> known doors keyed by domain */
    public static function registry(): array
    {
        return (array) config('sites.sites', []);
    }

    /** Resolve (but don't activate) the site config for a Host. */
    public static function forHost(?string $host): array
    {
        $host = $host ? preg_replace('/^www\./', '', strtolower($host)) : '';

        return static::registry()[$host] ?? static::fromEnv($host);
    }

    /**
     * Make a domain the active site: point per-door config (brand, JID domain, mail,
     * web-chat URLs) at it. Safe in a request or a queue job. Returns the site.
     *
     * @return array<string, string>
     */
    public static function apply(string $domain): array
    {
        $site = static::registry()[$domain] ?? static::fromEnv($domain);

        config([
            'app.name' => $site['brand'],
            'xmpp.domain' => $site['domain'],
            'site.theme' => $site['theme'],
            'mail.from.address' => $site['mail_from'],
            'mail.from.name' => $site['brand'],
            'mail.markdown.theme' => $site['mail_theme'],
            'xmpp.web_chat.websocket_url' => "wss://{$site['domain']}/ws",
            'xmpp.web_chat.bosh_url' => "https://{$site['domain']}/bosh",
        ]);

        return static::$current = $site;
    }

    /** apply() + prepend the theme view dir. Web requests only (per-request finder state). */
    public static function applyWithTheme(string $domain): array
    {
        $site = static::apply($domain);

        if (($theme = $site['theme']) && is_dir($dir = resource_path("views/themes/{$theme}"))) {
            View::getFinder()->prependLocation($dir);
        }

        return $site;
    }

    /** The active site (falls back to the .env site if nothing was applied yet). */
    public static function current(): array
    {
        return static::$current ??= static::fromEnv((string) config('xmpp.domain'));
    }

    /** The .env-derived site — the dev/test/single-tenant fallback. */
    protected static function fromEnv(string $domain): array
    {
        return [
            'domain' => $domain !== '' ? $domain : (string) config('xmpp.domain'),
            'brand' => (string) config('app.name'),
            'theme' => (string) config('site.theme', ''),
            'mail_from' => (string) config('mail.from.address'),
            'mail_theme' => (string) config('mail.markdown.theme', 'default'),
        ];
    }
}
