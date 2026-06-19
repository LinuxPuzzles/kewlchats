<?php

namespace App\Http\Controllers;

use App\Support\KnowledgeBase;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index(Request $request, KnowledgeBase $kb)
    {
        $q = trim((string) $request->query('q', ''));

        return view('help.index', [
            'q' => $q,
            'results' => $q !== '' ? $kb->search($q) : null,
            'categories' => $kb->byCategory(),
        ]);
    }

    public function show(string $slug, KnowledgeBase $kb)
    {
        abort_unless(preg_match('/^[a-z0-9-]+$/', $slug), 404);

        $article = $kb->find($slug);
        abort_unless($article, 404);

        $related = $kb->all()
            ->where('category', $article->category)
            ->where('slug', '!=', $article->slug)
            ->values();

        return view('help.show', compact('article', 'related'));
    }
}
