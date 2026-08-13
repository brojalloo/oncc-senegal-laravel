<?php

namespace App\Support;

class LogTail
{
    /**
     * Lit les dernières lignes d'un fichier sans le charger entièrement.
     *
     * L'implémentation précédente faisait `File::get()` puis découpait la fin :
     * sur un journal de production de plusieurs centaines de Mo, la page
     * d'administration épuisait la mémoire de PHP. On remonte donc le fichier
     * par blocs depuis la fin, en ne gardant que ce qui est demandé.
     *
     * @return list<string> Les lignes, de la plus ancienne à la plus récente.
     */
    public static function read(string $path, int $lines = 100, int $chunkSize = 8192): array
    {
        if ($lines < 1 || ! is_readable($path)) {
            return [];
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return [];
        }

        try {
            fseek($handle, 0, SEEK_END);
            $position = ftell($handle);
            $buffer = '';
            $newlines = 0;

            // On recule par blocs jusqu'à avoir assez de sauts de ligne, ou
            // jusqu'au début du fichier.
            while ($position > 0 && $newlines <= $lines) {
                $read = (int) min($chunkSize, $position);
                $position -= $read;

                fseek($handle, $position, SEEK_SET);
                $chunk = fread($handle, $read);

                if ($chunk === false) {
                    break;
                }

                $buffer = $chunk.$buffer;
                $newlines = substr_count($buffer, "\n");
            }
        } finally {
            fclose($handle);
        }

        $all = preg_split("/\r\n|\n|\r/", $buffer) ?: [];

        // Une lecture partielle peut couper la première ligne en plein milieu :
        // on l'écarte, sauf si on a atteint le début du fichier.
        if ($position > 0 && count($all) > 0) {
            array_shift($all);
        }

        $all = array_values(array_filter($all, static fn (string $l) => trim($l) !== ''));

        return array_slice($all, -$lines);
    }
}
