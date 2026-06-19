<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * A single knowledge-base article, parsed from a Markdown file in resources/kb/.
 * Plain value object — the KnowledgeBase service builds these.
 */
class HelpArticle
{
    /** Relevance score, set transiently during a search. */
    public int $score = 0;

    /**
     * @param  string[]  $keywords
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly string $category,
        public readonly int $order,
        public readonly string $summary,
        public readonly array $keywords,
        public readonly string $html,
    ) {}

    /** Body as plain text (for search + snippets). */
    public function text(): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strip_tags($this->html)));
    }

    /** A short, highlighted excerpt around the first match of $q (returns HTML). */
    public function excerpt(string $q, int $len = 160): string
    {
        $text = $this->text();
        $q = trim($q);

        if ($q === '') {
            return e(Str::limit($text, $len));
        }

        $pos = mb_stripos($text, $q);
        if ($pos === false) {
            return e(Str::limit($text, $len));
        }

        $start = max(0, $pos - 40);
        $snippet = mb_substr($text, $start, $len);
        $snippet = ($start > 0 ? '… ' : '').e($snippet).' …';

        return (string) preg_replace('/('.preg_quote(e($q), '/').')/i', '<mark>$1</mark>', $snippet);
    }
}
