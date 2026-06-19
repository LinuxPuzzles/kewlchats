<?php

namespace Tests\Feature;

use Tests\TestCase;

class HelpPageTest extends TestCase
{
    public function test_help_index_is_public_and_lists_articles(): void
    {
        $this->get(route('help'))
            ->assertOk()
            ->assertSee('Help &amp; Guide', false)
            ->assertSee('Join a room')          // an article title
            ->assertSee('People &amp; rooms', false); // a category
    }

    public function test_article_renders_with_domain_aware_addresses(): void
    {
        config(['xmpp.muc_domain' => 'conference.example.net']);

        $this->get(route('help.show', 'join-room'))
            ->assertOk()
            ->assertSee('Join a room')
            ->assertSee('lounge@conference.example.net'); // {lounge} token resolved
    }

    public function test_unknown_article_is_404(): void
    {
        $this->get(route('help.show', 'does-not-exist'))->assertNotFound();
        $this->get('/help/Bad_Slug')->assertNotFound(); // invalid slug shape
    }

    public function test_search_finds_matching_articles(): void
    {
        $this->get(route('help', ['q' => 'room']))
            ->assertOk()
            ->assertSee('result', false)
            ->assertSee('Join a room');
    }
}
