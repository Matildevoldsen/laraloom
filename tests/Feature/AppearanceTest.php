<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('community pages expose the persistent Flux color theme switcher', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Switch color theme')
        ->assertSee('$flux.dark = ! $flux.dark', false)
        ->assertDontSee('class="dark"', false);
});

test('authentication pages respect the selected appearance instead of forcing dark mode', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('class="dark"', false);
});
