<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HowItWorksTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_how_it_works_page_loads_and_is_linked(): void
    {
        $this->get(route('how-it-works'))
            ->assertOk()
            ->assertSee('How it works')
            ->assertSee('Get off the couch')
            ->assertSee("I'm in", false)
            ->assertSee('Find a game')
            ->assertSee('Is it free?')
            ->assertSee(route('games.index'), false)
            ->assertDontSee('not legal advice');

        $this->get('/')
            ->assertOk()
            ->assertSee(route('how-it-works'), false)
            ->assertSee('See how a game works');

        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('not legal advice')
            ->assertSee(route('how-it-works'), false);
    }
}
