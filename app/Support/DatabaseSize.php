<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DatabaseSize
{
    /**
     * Taille de la base, formatée pour l'affichage.
     *
     * L'implémentation précédente interrogeait `information_schema` en lisant
     * `database.connections.mysql.database` — une connexion que ce projet
     * n'utilise pas. Sur SQLite comme sur PostgreSQL la requête échouait, et
     * l'exception étant avalée, le tableau de bord affichait « N/A » en
     * permanence sans que rien ne le signale.
     *
     * Chaque moteur expose sa taille autrement : on interroge donc celui qui
     * est réellement connecté.
     */
    public static function human(): string
    {
        $bytes = self::bytes();

        return $bytes === null ? 'Indisponible' : self::format($bytes);
    }

    /**
     * Taille en octets, ou null si le moteur ne sait pas la donner.
     */
    public static function bytes(): ?int
    {
        try {
            return match (DB::connection()->getDriverName()) {
                'sqlite' => self::sqlite(),
                'pgsql' => self::pgsql(),
                'mysql', 'mariadb' => self::mysql(),
                default => null,
            };
        } catch (\Throwable) {
            return null;
        }
    }

    private static function sqlite(): ?int
    {
        $path = DB::connection()->getDatabaseName();

        // Une base en mémoire n'a pas de fichier : on additionne les pages.
        if ($path === ':memory:' || ! is_file($path)) {
            $pages = DB::selectOne('PRAGMA page_count')->page_count ?? null;
            $size = DB::selectOne('PRAGMA page_size')->page_size ?? null;

            return $pages && $size ? (int) $pages * (int) $size : null;
        }

        $bytes = @filesize($path);

        return $bytes === false ? null : $bytes;
    }

    private static function pgsql(): ?int
    {
        $row = DB::selectOne('SELECT pg_database_size(current_database()) AS size');

        return isset($row->size) ? (int) $row->size : null;
    }

    private static function mysql(): ?int
    {
        $row = DB::selectOne(
            'SELECT SUM(data_length + index_length) AS size
             FROM information_schema.TABLES
             WHERE table_schema = ?',
            [DB::connection()->getDatabaseName()]
        );

        return isset($row->size) ? (int) $row->size : null;
    }

    private static function format(int $bytes): string
    {
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $i === 0 ? 0 : 1).' '.$units[$i];
    }
}
