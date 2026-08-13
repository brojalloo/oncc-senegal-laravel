<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewsletter;
use App\Models\Alerte;
use App\Models\DonneeClimatique;
use App\Models\DonneeEconomique;
use App\Models\User;
use App\Support\DatabaseSize;
use App\Support\LogTail;
use App\Support\RecentActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    /** Nombre de lignes de journal affichées dans l'interface d'administration. */
    private const LOG_LINES = 100;

    // Dashboard admin
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'climate_data' => DonneeClimatique::count(),
            'economic_data' => DonneeEconomique::count(),
            'alerts' => Alerte::where('date_fin', '>=', now())->count(),
        ];

        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_size' => DatabaseSize::human(),
        ];

        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentActivities = RecentActivity::latest();

        return view('admin.dashboard', compact('stats', 'systemInfo', 'recentUsers', 'recentActivities'));
    }

    // Rapports
    public function reports()
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'active' => User::whereNotNull('email_verified_at')->count(),
                'by_role' => User::select('role', DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->get(),
            ],
            'climate' => [
                'total' => DonneeClimatique::count(),
                'by_indicator' => DonneeClimatique::select('type_indicateur', DB::raw('count(*) as count'))
                    ->groupBy('type_indicateur')
                    ->get(),
            ],
            'economic' => [
                'total' => DonneeEconomique::count(),
            ],
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'database' => config('database.default'),
                'database_size' => DatabaseSize::human(),
            ],
        ];

        return view('admin.reports', compact('stats'));
    }

    // Logs système
    public function logs()
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = [];
        $totalLines = 0;

        if (File::exists($logFile)) {
            // Lecture par la fin : charger le fichier entier épuisait la
            // mémoire dès que le journal de production atteignait quelques
            // centaines de Mo.
            $lines = LogTail::read($logFile, self::LOG_LINES);
            $totalLines = count($lines);

            foreach ($lines as $line) {
                $log = $this->parseLogLine($line);
                if ($log) {
                    $logs[] = $log;
                }
            }

            // Inverser pour avoir les plus récents en premier
            $logs = array_reverse($logs);
        }

        return view('admin.logs', compact('logs', 'totalLines'));
    }

    // Vider les logs
    public function clearLogs()
    {
        $logFile = storage_path('logs/laravel.log');

        if (File::exists($logFile)) {
            File::put($logFile, '');

            return redirect()->route('admin.logs')
                ->with('success', 'Les logs ont été vidés avec succès.');
        }

        return redirect()->route('admin.logs')
            ->with('error', 'Le fichier de logs n\'existe pas.');
    }

    // Gestion des emails
    public function emails()
    {
        $emailStats = [
            'total_users' => User::count(),
            'active_users' => User::whereNotNull('email_verified_at')->count(),
        ];

        return view('admin.emails', compact('emailStats'));
    }

    // Envoyer un email de test
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            Mail::raw('Ceci est un email de test de ONCC-SN.', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('Test d\'envoi d\'email - ONCC-SN');
            });

            return redirect()->route('admin.emails')
                ->with('test_success', 'Email de test envoyé avec succès à '.$request->test_email);
        } catch (\Exception $e) {
            return redirect()->route('admin.emails')
                ->with('test_error', 'Erreur lors de l\'envoi : '.$e->getMessage());
        }
    }

    // Envoyer une newsletter
    public function sendNewsletter(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'target' => 'required|in:all,admin,chercheur,collectivite',
        ]);

        $recipients = User::whereNotNull('email_verified_at')
            ->when($request->target !== 'all', fn ($q) => $q->where('role', $request->target))
            ->count();

        // L'envoi part en file d'attente : le faire dans la requête expirait
        // dès quelques dizaines de destinataires, laissant l'envoi à moitié
        // fait sans qu'on sache où il s'était arrêté.
        SendNewsletter::dispatch($request->subject, $request->content, $request->target);

        return redirect()->route('admin.emails')
            ->with('newsletter_success', "Infolettre mise en file d'attente pour {$recipients} destinataire(s). L'envoi se poursuit en arrière-plan.");
    }

    // Page de validation des données
    public function validationPage()
    {
        $climatiques = DonneeClimatique::with(['region', 'utilisateur'])
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->get();

        $economiques = DonneeEconomique::with(['region', 'utilisateur'])
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.validation', compact('climatiques', 'economiques'));
    }

    // Valider une donnée
    public function validateData(Request $request, $type, $id)
    {
        if ($type === 'climatique') {
            $data = DonneeClimatique::findOrFail($id);
        } elseif ($type === 'economique') {
            $data = DonneeEconomique::findOrFail($id);
        } else {
            return redirect()->back()->withErrors(['error' => 'Type de donnée inconnu.']);
        }

        $data->update(['statut' => 'valide']);

        return redirect()->route('admin.validation')
            ->with('success', 'Donnée validée avec succès.');
    }

    // Rejeter une donnée
    public function rejectData(Request $request, $type, $id)
    {
        if ($type === 'climatique') {
            $data = DonneeClimatique::findOrFail($id);
        } elseif ($type === 'economique') {
            $data = DonneeEconomique::findOrFail($id);
        } else {
            return redirect()->back()->withErrors(['error' => 'Type de donnée inconnu.']);
        }

        $data->update(['statut' => 'rejete']);

        return redirect()->route('admin.validation')
            ->with('success', 'Donnée rejetée avec succès.');
    }

    // Gestion des utilisateurs
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users', compact('users'));
    }

    // Modifier le rôle d'un utilisateur
    public function updateUserRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role' => 'required|in:public,collectivite,chercheur,admin',
        ]);

        if ($this->wouldRemoveLastAdmin($user, $request->role !== 'admin')) {
            return redirect()->back()->withErrors([
                'error' => 'Impossible de retirer le rôle administrateur au dernier administrateur : plus personne ne pourrait accéder à cette section.',
            ]);
        }

        $user->update(['role' => $request->role]);

        return redirect()->back()
            ->with('success', 'Rôle de l\'utilisateur mis à jour avec succès.');
    }

    // Modifier le statut d'un utilisateur (actif/inactif)
    public function updateUserStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Ne pas permettre de désactiver son propre compte
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->withErrors(['error' => 'Vous ne pouvez pas modifier le statut de votre propre compte.']);
        }

        $request->validate([
            'statut' => 'required|in:actif,inactif',
        ]);

        if ($this->wouldRemoveLastAdmin($user, $request->statut !== 'actif')) {
            return redirect()->back()->withErrors([
                'error' => 'Impossible de désactiver le dernier administrateur actif : plus personne ne pourrait accéder à cette section.',
            ]);
        }

        $user->update(['statut' => $request->statut]);

        $message = $request->statut === 'actif'
                   ? 'Utilisateur activé avec succès.'
                   : 'Utilisateur désactivé avec succès.';

        return redirect()->back()->with('success', $message);
    }

    // Helper: Parser une ligne de log
    private function parseLogLine($line)
    {
        // Format Laravel standard: [2024-01-01 12:00:00] local.ERROR: Message
        if (preg_match('/\[(.*?)\]\s+(\w+)\.(\w+):\s+(.*)/', $line, $matches)) {
            $level = strtoupper($matches[3]);
            $badge = match ($level) {
                'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 'danger',
                'WARNING' => 'warning',
                'NOTICE', 'INFO' => 'info',
                'DEBUG' => 'secondary',
                default => 'secondary'
            };

            return [
                'date' => $matches[1],
                'level' => $level,
                'message' => $matches[4],
                'badge' => $badge,
            ];
        }

        // Format serveur de développement: 2024-01-01 12:00:00 /route ... ~ 123ms
        if (preg_match('/^(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(\/\S+)\s+\.+\s+~?\s*(.+)$/', trim($line), $matches)) {
            $route = $matches[2];
            $time = $matches[3];

            // Ignorer les fichiers statiques
            if (preg_match('/\.(css|js|ico|png|jpg|jpeg|gif|svg|woff|ttf)$/', $route)) {
                return null;
            }

            return [
                'date' => $matches[1],
                'level' => 'REQUEST',
                'message' => $route.' - '.$time,
                'badge' => 'info',
            ];
        }

        return null;
    }

    /**
     * L'opération retirerait-elle le dernier administrateur actif ?
     *
     * Rétrograder ou désactiver le seul compte administrateur ferme
     * l'administration à tout le monde, sans moyen de revenir en arrière par
     * l'interface : il faut alors passer par `php artisan users:promote`.
     *
     * @param  bool  $losesAdminAccess  L'opération prive-t-elle cet utilisateur de l'accès admin ?
     */
    private function wouldRemoveLastAdmin(User $user, bool $losesAdminAccess): bool
    {
        if (! $losesAdminAccess) {
            return false;
        }

        if ($user->role !== 'admin' || $user->statut !== 'actif') {
            return false;
        }

        $remainingAdmins = User::where('role', 'admin')
            ->where('statut', 'actif')
            ->where('id', '!=', $user->id)
            ->count();

        return $remainingAdmins === 0;
    }
}
