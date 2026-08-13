<?php

namespace App\Support;

use App\Models\Alerte;
use App\Models\DonneeClimatique;
use App\Models\DonneeEconomique;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Fil d'activité du tableau de bord d'administration.
 *
 * Le tableau de bord affichait auparavant quatre lignes écrites en dur —
 * « Alerte créée pour Dakar », « Rapport généré » — horodatées à la volée par
 * now()->subHours(). Elles paraissaient authentiques et ne l'étaient jamais :
 * un administrateur consultant une plateforme vide y lisait de l'activité.
 *
 * Tout ce qui suit vient de la base. Les saisies, inscriptions et alertes sont
 * datées par created_at. Les validations s'appuient sur updated_at : les seuls
 * chemins qui modifient une donnée sont validateData et rejectData, aucune
 * route d'édition n'existant, si bien qu'une donnée non « en_attente » dont la
 * date de modification diffère de la création a nécessairement été traitée.
 */
class RecentActivity
{
    /**
     * @return list<array{time: string, message: string, at: Carbon}>
     */
    public static function latest(int $limit = 8): array
    {
        $entries = array_merge(
            self::registrations($limit),
            self::submissions($limit),
            self::reviews($limit),
            self::alerts($limit),
        );

        usort($entries, fn (array $a, array $b) => $b['at'] <=> $a['at']);

        return array_slice(self::group($entries), 0, $limit);
    }

    /**
     * Regroupe les entrées identiques.
     *
     * Une validation en lot, ou un import, produit sinon une dizaine de lignes
     * rigoureusement identiques qui chassent tout le reste du fil.
     *
     * @param  list<array{time: string, message: string, at: Carbon}>  $entries
     * @return list<array{time: string, message: string, at: Carbon}>
     */
    private static function group(array $entries): array
    {
        $grouped = [];

        foreach ($entries as $entry) {
            $key = $entry['message'];

            if (isset($grouped[$key])) {
                $grouped[$key]['count']++;

                continue;
            }

            $grouped[$key] = $entry + ['count' => 1];
        }

        return array_values(array_map(function (array $entry) {
            if ($entry['count'] > 1) {
                $entry['message'] .= " (×{$entry['count']})";
            }

            unset($entry['count']);

            return $entry;
        }, $grouped));
    }

    /**
     * @return list<array{time: string, message: string, at: Carbon}>
     */
    private static function registrations(int $limit): array
    {
        return User::latest()->limit($limit)->get()
            ->map(fn (User $u) => self::entry(
                $u->created_at,
                trim("{$u->prenom} {$u->nom}").' a créé un compte '.self::role($u->role)
            ))
            ->all();
    }

    /**
     * @return list<array{time: string, message: string, at: Carbon}>
     */
    private static function submissions(int $limit): array
    {
        $climate = DonneeClimatique::with('region')->latest()->limit($limit)->get()
            ->map(fn (DonneeClimatique $d) => self::entry(
                $d->created_at,
                'Donnée climatique saisie pour '.self::regionName($d).' ('.self::label($d->type_indicateur).')'
            ));

        $economic = DonneeEconomique::with('region')->latest()->limit($limit)->get()
            ->map(fn (DonneeEconomique $d) => self::entry(
                $d->created_at,
                'Donnée économique saisie pour '.self::regionName($d).' ('.self::label($d->secteur).')'
            ));

        return $climate->concat($economic)->all();
    }

    /**
     * Validations et rejets.
     *
     * @return list<array{time: string, message: string, at: Carbon}>
     */
    private static function reviews(int $limit): array
    {
        $describe = fn ($d, string $kind) => self::entry(
            $d->updated_at,
            sprintf(
                'Donnée %s %s pour %s',
                $kind,
                $d->statut === 'valide' ? 'validée' : 'rejetée',
                self::regionName($d)
            )
        );

        $reviewed = fn (string $model) => $model::with('region')
            ->whereIn('statut', ['valide', 'rejete'])
            ->whereColumn('updated_at', '>', 'created_at')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();

        return $reviewed(DonneeClimatique::class)
            ->map(fn ($d) => $describe($d, 'climatique'))
            ->concat($reviewed(DonneeEconomique::class)->map(fn ($d) => $describe($d, 'économique')))
            ->all();
    }

    /**
     * @return list<array{time: string, message: string, at: Carbon}>
     */
    private static function alerts(int $limit): array
    {
        return Alerte::with('region')->latest()->limit($limit)->get()
            ->map(fn (Alerte $a) => self::entry(
                $a->created_at,
                'Alerte '.self::label($a->type_alerte).' ('.self::label($a->niveau).') pour '.self::regionName($a)
            ))
            ->all();
    }

    /**
     * @return array{time: string, message: string, at: Carbon}
     */
    private static function entry(?Carbon $at, string $message): array
    {
        $at ??= Carbon::now();

        return [
            'at' => $at,
            'time' => $at->diffForHumans(),
            'message' => $message,
        ];
    }

    private static function regionName(object $model): string
    {
        return $model->region?->nom ?? 'une région inconnue';
    }

    private static function role(?string $role): string
    {
        return match ($role) {
            'admin' => 'administrateur',
            'chercheur' => 'chercheur',
            'collectivite' => 'collectivité',
            default => 'public',
        };
    }

    /**
     * Transforme un libellé de base en texte lisible : « pluviometrie » devient
     * « pluviométrie ».
     */
    private static function label(?string $value): string
    {
        return match ($value) {
            'secheresse' => 'sécheresse',
            'desertification' => 'désertification',
            'temperature' => 'température',
            'pluviometrie' => 'pluviométrie',
            'peche' => 'pêche',
            'elevage' => 'élevage',
            'foret' => 'forêt',
            'energie' => 'énergie',
            'eleve' => 'élevé',
            null => 'non précisé',
            default => $value,
        };
    }
}
