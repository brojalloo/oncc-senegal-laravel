<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordEmail;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_inactive_unverified_user_and_sends_verification_email(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'email' => 'nouveau@test.sn',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nom' => 'Ndiaye',
            'prenom' => 'Awa',
            'role' => 'chercheur',
        ]);

        $response->assertRedirect(route('login'));

        $user = User::where('email', 'nouveau@test.sn')->firstOrFail();
        $this->assertSame('inactif', $user->statut);
        $this->assertNull($user->email_verified_at);
        $this->assertNotNull($user->verification_token);

        Mail::assertSent(VerifyEmail::class, fn ($mail) => $mail->hasTo($user->email));
    }

    public function test_verify_email_activates_the_account_with_a_valid_token(): void
    {
        $user = User::factory()->unverified()->create([
            'statut' => 'inactif',
            'verification_token' => 'valid-token',
            'verification_token_expires' => now()->addHour(),
        ]);

        $response = $this->get('/verify-email/valid-token');

        $response->assertRedirect(route('login'));

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('actif', $user->statut);
        $this->assertNull($user->verification_token);
    }

    public function test_verify_email_rejects_an_expired_token(): void
    {
        $user = User::factory()->unverified()->create([
            'verification_token' => 'expired-token',
            'verification_token_expires' => now()->subHour(),
        ]);

        $response = $this->get('/verify-email/expired-token');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_login_succeeds_with_valid_credentials_for_an_active_verified_user(): void
    {
        $user = User::factory()->create([
            'email' => 'ok@test.sn',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'ok@test.sn',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'ok@test.sn',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'ok@test.sn',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_blocks_an_unverified_account(): void
    {
        User::factory()->unverified()->create([
            'email' => 'nonverifie@test.sn',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'nonverifie@test.sn',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_blocks_an_inactive_account(): void
    {
        User::factory()->inactive()->create([
            'email' => 'inactif@test.sn',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'inactif@test.sn',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
