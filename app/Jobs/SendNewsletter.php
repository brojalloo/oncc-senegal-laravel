<?php

namespace App\Jobs;

use App\Mail\NewsletterEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envoie l'infolettre en arrière-plan.
 *
 * L'envoi se faisait auparavant dans la requête HTTP, un destinataire après
 * l'autre : au-delà de quelques dizaines de comptes, la requête expirait et
 * l'envoi s'interrompait au milieu, sans trace de ce qui était parti.
 */
class SendNewsletter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Un destinataire lent ne doit pas faire échouer tout le lot. */
    public int $tries = 3;

    public int $timeout = 600;

    public function __construct(
        public readonly string $subject,
        public readonly string $content,
        public readonly string $target,
    ) {}

    public function handle(): void
    {
        $sent = 0;
        $failed = 0;

        $this->recipients()->chunkById(100, function ($users) use (&$sent, &$failed) {
            foreach ($users as $user) {
                try {
                    Mail::to($user->email)->send(
                        new NewsletterEmail($this->subject, $this->content)
                    );
                    $sent++;
                } catch (\Throwable $e) {
                    // Un destinataire invalide ne doit pas priver les suivants
                    // de l'envoi.
                    $failed++;
                    Log::warning('Échec d\'envoi de l\'infolettre', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        Log::info('Infolettre traitée', [
            'target' => $this->target,
            'sent' => $sent,
            'failed' => $failed,
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    private function recipients()
    {
        $query = User::whereNotNull('email_verified_at');

        if ($this->target !== 'all') {
            $query->where('role', $this->target);
        }

        return $query;
    }
}
