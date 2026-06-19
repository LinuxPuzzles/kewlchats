# Themes

One codebase, an `.env`-selected skin per site. `SITE_THEME` (→ `config('site.theme')`) makes
`AppServiceProvider::boot()` **prepend** `resources/views/themes/<theme>` to the Blade view finder,
so a theme can override **any** page, layout, or component by placing a file at the **same relative
path**. Anything a theme doesn't define falls back to the shared base in `resources/views/`.
Controllers and `view(...)` call sites never change — only templates do.

```
resources/views/                  ← shared base (fallback)
  landing.blade.php
  layouts/app.blade.php
  components/brand.blade.php
resources/views/themes/
  kewlchats/                      ← SITE_THEME=kewlchats overrides
    landing.blade.php             ← overrides base landing
  ready2im/                       ← SITE_THEME=ready2im overrides
    layouts/guest.blade.php       ← overrides base guest layout
    components/brand.blade.php     ← overrides <x-brand>
```

Empty theme dir = the site renders the base (today's look). Fill it as you design.

## Contract a theme must honor

Override freely, but a theme that replaces a **layout** must keep the functional hooks the base
provides, or things silently break:

- **`layouts/app.blade.php` / `layouts/guest.blade.php`** — include
  `@vite(['resources/css/themes/<theme>.css', 'resources/js/app.js'])` (your theme's CSS bundle +
  the shared Alpine JS). The **guest** layout must also include `@unbotableJs`.
- **Auth forms** (`auth/login`, `auth/register`) — keep `@unbotableHoneypot`,
  `@unbotableTimestamp`, and `@unbotableWire('#form-id')`.
- **Chat page** (`chat.blade.php`) — include `<x-converse-chat />` (the functional Converse embed;
  your file owns only the surrounding chrome/copy).

## Per-theme CSS

Each theme has its own bundle at `resources/css/themes/<theme>.css` (registered in
`vite.config.js`). Put that theme's Tailwind layer customizations / design tokens there; the
theme's layout `@vite`s it. `npm run build` emits all theme bundles; each site loads only its own.

## Shared (don't usually override)

Functional primitives stay in base and are meant to be reused as-is: `components/text-input`,
`input-label`, `input-error`, `dropdown`, `dropdown-link`, `modal`, `nav-link`, the button
components, `error-page`, and `converse-chat`. A theme *can* override them, but normally won't.
