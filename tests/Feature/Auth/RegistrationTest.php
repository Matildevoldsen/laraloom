<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_redirects_to_github_only_login(): void
    {
        $response = $this->get(route('register'));

        $response->assertRedirect(route('login'));
    }

    public function test_password_registration_is_not_available_on_the_web(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertMethodNotAllowed();

        $this->assertGuest();
    }
}
