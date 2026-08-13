<?php

namespace Tests\Feature;

use App\Jobs\SendNewsletter;
use App\Mail\NewsletterEmail;
use App\Mail\ResetPasswordEmail;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueuedMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_verification_email_is_queued_not_sent_inline(): void
    {
        Mail::fake();

        $this->post('/register', [
            'email' => 'nouveau@test.sn',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nom' => 'Ndiaye',
            'prenom' => 'Awa',
            'role' => 'chercheur',
        ]);

        // assertQueued échoue si le mailable est parti en synchrone.
        Mail::assertQueued(VerifyEmail::class);
        Mail::assertNotSent(VerifyEmail::class);
    }

    public function test_the_password_reset_email_is_queued(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'connu@test.sn']);

        $this->post('/forgot-password', ['email' => 'connu@test.sn']);

        Mail::assertQueued(ResetPasswordEmail::class);
        Mail::assertNotSent(ResetPasswordEmail::class);
    }

    public function test_both_transactional_mailables_declare_shouldqueue(): void
    {
        // Les deux mailables construisent une URL signée depuis le jeton :
        // il doit être présent, sinon la génération de route échoue.
        $user = User::factory()->make([
            'verification_token' => 'jeton-de-verification',
            'reset_token' => 'jeton-de-reinitialisation',
        ]);

        $this->assertInstanceOf(ShouldQueue::class, new VerifyEmail($user));
        $this->assertInstanceOf(ShouldQueue::class, new ResetPasswordEmail($user));
    }

    public function test_the_newsletter_is_dispatched_to_the_queue(): void
    {
        Bus::fake();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.emails.newsletter'), [
            'subject' => 'Bulletin climatique',
            'content' => '<p>Bonjour</p>',
            'target' => 'all',
        ]);

        $response->assertRedirect(route('admin.emails'));
        Bus::assertDispatched(SendNewsletter::class);
    }

    public function test_the_newsletter_request_returns_immediately_regardless_of_audience(): void
    {
        Queue::fake();
        $admin = User::factory()->admin()->create();
        User::factory()->count(30)->create();

        $response = $this->actingAs($admin)->post(route('admin.emails.newsletter'), [
            'subject' => 'Bulletin',
            'content' => '<p>Contenu</p>',
            'target' => 'all',
        ]);

        $response->assertSessionHas('newsletter_success');

        // Un seul travail est mis en file, quel que soit le nombre de
        // destinataires : la requête HTTP ne boucle plus sur les comptes.
        Queue::assertPushed(SendNewsletter::class, 1);
    }

    public function test_the_newsletter_job_sends_to_verified_users_of_the_target_role(): void
    {
        Mail::fake();

        User::factory()->create(['role' => 'chercheur', 'email' => 'cible@test.sn']);
        User::factory()->create(['role' => 'public', 'email' => 'hors-cible@test.sn']);
        User::factory()->unverified()->create(['role' => 'chercheur', 'email' => 'non-verifie@test.sn']);

        (new SendNewsletter('Sujet', '<p>Corps</p>', 'chercheur'))->handle();

        Mail::assertSentCount(1);
        Mail::assertSent(NewsletterEmail::class, fn ($mail) => $mail->hasTo('cible@test.sn'));
    }

    public function test_a_failing_recipient_does_not_stop_the_rest_of_the_batch(): void
    {
        User::factory()->create(['role' => 'chercheur', 'email' => 'premier@test.sn']);
        User::factory()->create(['role' => 'chercheur', 'email' => 'second@test.sn']);

        Mail::shouldReceive('to')
            ->twice()
            ->andReturnUsing(function (string $email) {
                if ($email === 'premier@test.sn') {
                    throw new \RuntimeException('SMTP refuse ce destinataire');
                }

                return new class
                {
                    public function send($mailable): void {}
                };
            });

        // Ne doit pas propager : le second destinataire est bien traité.
        (new SendNewsletter('Sujet', '<p>Corps</p>', 'chercheur'))->handle();

        $this->assertTrue(true);
    }
}
