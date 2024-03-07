<?php

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_user_can_login_with_correct_credentials()
    {
        $user = $this->withUserAttrs([
            'password' => bcrypt($password = 'password'),
        ])->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->dd();

        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_user_cannot_login_with_incorrect_password()
    {
        $user = $this->createUser();

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'invalid-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_remember_me_functionality()
    {
        $user = $this->createUser();

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $response->assertCookie(Auth::guard()->getRecallerName(), vsprintf('%s|%s|%s', [
            $user->id,
            $user->getRememberToken(),
            $user->password,
        ]));

        $this->assertAuthenticatedAs($user);
    }
}
