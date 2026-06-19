<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

/**
 * A tiny file-based knowledge base: Markdown articles (with YAML frontmatter) in
 * resources/kb/. No DB, no admin — author the .md files in the repo. Addresses are
 * written as tokens ({domain}, {muc}, {lounge}, {email}, {brand}) so the same article
 * renders correctly per-site (kewlchats.net vs ready2.im).
 */
class KnowledgeBase
{
    /** Display order for categories; unknown categories sort last. */
    private const CATEGORY_ORDER = [
        'Getting started' => 1,
        'People & rooms' => 2,
        'Calls & devices' => 3,
        'Good to know' => 4,
    ];

    private string $path;

    /** @var Collection<int, HelpArticle>|null */
    private ?Collection $articles = null;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? resource_path('kb');
    }

    /** @return Collection<int, HelpArticle> sorted by category, then order, then title. */
    public function all(): Collection
    {
        return $this->articles ??= collect(File::glob($this->path.'/*.md'))
            ->map(fn (string $file) => $this->parse($file))
            ->filter()
            ->sortBy(fn (HelpArticle $a) => [
                self::CATEGORY_ORDER[$a->category] ?? 99,
                $a->order,
                $a->title,
            ])
            ->values();
    }

    public function find(string $slug): ?HelpArticle
    {
        return $this->all()->firstWhere('slug', $slug);
    }

    /** @return Collection<string, Collection<int, HelpArticle>> category => articles (ordered). */
    public function byCategory(): Collection
    {
        return $this->all()->groupBy('category');
    }

    /** Weighted, case-insensitive search over title/keywords/summary/body. */
    public function search(string $q): Collection
    {
        $q = trim($q);
        if ($q === '') {
            return collect();
        }

        $needle = mb_strtolower($q);

        return $this->all()
            ->map(function (HelpArticle $a) use ($needle) {
                $score = 0;
                if (str_contains(mb_strtolower($a->title), $needle)) {
                    $score += 10;
                }
                foreach ($a->keywords as $kw) {
                    if (str_contains(mb_strtolower((string) $kw), $needle)) {
                        $score += 5;
                        break;
                    }
                }
                if (str_contains(mb_strtolower($a->summary), $needle)) {
                    $score += 3;
                }
                if (str_contains(mb_strtolower($a->text()), $needle)) {
                    $score += 1;
                }
                $a->score = $score;

                return $a;
            })
            ->filter(fn (HelpArticle $a) => $a->score > 0)
            ->sortByDesc('score')
            ->values();
    }

    private function parse(string $file): ?HelpArticle
    {
        $raw = strtr(File::get($file), $this->tokens());
        [$front, $body] = $this->splitFrontMatter($raw);

        $title = $front['title'] ?? null;
        if (! is_string($title) || $title === '') {
            return null; // skip files without a title
        }

        return new HelpArticle(
            slug: pathinfo($file, PATHINFO_FILENAME),
            title: $title,
            category: (string) ($front['category'] ?? 'Good to know'),
            order: (int) ($front['order'] ?? 99),
            summary: (string) ($front['summary'] ?? ''),
            keywords: array_map('strval', (array) ($front['keywords'] ?? [])),
            html: (string) $this->converter()->convert($body),
        );
    }

    /**
     * Minimal YAML-frontmatter split (no symfony/yaml dependency) — these files use
     * simple `key: value` lines plus an inline `keywords: [a, b]` list.
     *
     * @return array{0: array<string, mixed>, 1: string}  [frontmatter, body]
     */
    private function splitFrontMatter(string $raw): array
    {
        if (! preg_match('/^---\R(.*?)\R---\R?(.*)$/s', $raw, $m)) {
            return [[], $raw];
        }

        $front = [];
        foreach (preg_split('/\R/', $m[1]) as $line) {
            if (! preg_match('/^([A-Za-z0-9_]+):\s*(.*)$/', $line, $kv)) {
                continue;
            }
            $key = $kv[1];
            $value = trim($kv[2]);
            if ($key === 'keywords') {
                $value = trim($value, "[] \t");
                $front[$key] = $value === '' ? [] : array_map('trim', explode(',', $value));
            } else {
                $front[$key] = trim($value, "\"'");
            }
        }

        return [$front, $m[2]];
    }

    /** @return array<string, string> token => replacement, applied before Markdown render. */
    private function tokens(): array
    {
        return [
            '{domain}' => (string) config('xmpp.domain'),
            '{muc}' => (string) config('xmpp.muc_domain'),
            '{lounge}' => 'lounge@'.config('xmpp.muc_domain'),
            '{email}' => (string) config('mail.from.address'),
            '{brand}' => (string) config('app.name'),
        ];
    }

    private function converter(): MarkdownConverter
    {
        $env = new Environment(['html_input' => 'allow', 'allow_unsafe_links' => false]);
        $env->addExtension(new CommonMarkCoreExtension());
        $env->addExtension(new AutolinkExtension());

        return new MarkdownConverter($env);
    }
}
