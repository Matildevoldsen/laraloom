<?php

test('the community footer links to the source repository', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('https://github.com/Matildevoldsen/laraloom', escape: false)
        ->assertSee('rel="noopener noreferrer"', escape: false)
        ->assertSee('target="_blank"', escape: false)
        ->assertSee('GitHub');
});
