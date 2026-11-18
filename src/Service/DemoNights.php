<?php
declare(strict_types=1);

namespace App\Service;

use Cake\I18n\Date;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Trois semaines de nuits plausibles pour les comptes de démonstration.
 *
 * Les nuits sont reproductibles : la même graine donne toujours les mêmes nuits,
 * calées sur la date du jour.
 */
final class DemoNights
{
    /**
     * Nombre de jours couverts, aujourd'hui compris.
     */
    public const DAYS = 21;

    /**
     * Jours sans nuit saisie (nombre de jours avant aujourd'hui).
     */
    public const SKIPPED_DAYS = [9, 15];

    /**
     * Mot de passe des comptes de démonstration.
     */
    public const PASSWORD = 'demo-sommeil';

    /**
     * Nuits des trois dernières semaines, de la plus ancienne à la plus récente.
     *
     * @param \Cake\I18n\Date $today Date du réveil de la dernière nuit.
     * @param int $seed Graine du générateur.
     * @return list<array{sleep_date: string, bedtime: string, wake_time: string, nap_afternoon: bool, nap_evening: bool, sport: bool, morning_score: int, comment: string|null}>
     */
    public static function generate(Date $today, int $seed = 2048): array
    {
        $random = new Randomizer(new Mt19937($seed));
        $comments = [
            null, null, null, 'Réveil difficile.', 'Très bonne nuit.', 'Café trop tard.', null, 'Bruit dans la rue.',
        ];
        $nights = [];
        for ($daysAgo = self::DAYS - 1; $daysAgo >= 0; $daysAgo--) {
            if (in_array($daysAgo, self::SKIPPED_DAYS, true)) {
                continue;
            }
            // Coucher entre 22 h 30 et 0 h 45, nuits de 5 h 30 à 9 h.
            $bed = 22 * 60 + 30 + $random->getInt(0, 9) * 15;
            $length = [390, 405, 420, 450, 450, 465, 480, 495, 540, 330][$random->getInt(0, 9)];
            if ($daysAgo === 0) {
                // La nuit du jour est complète : cinq cycles, réveil en fin de cycle.
                [$bed, $length] = [23 * 60 + 15, 450];
            }
            $wake = ($bed + $length) % (24 * 60);
            $nights[] = [
                'sleep_date' => $today->subDays($daysAgo)->format('Y-m-d'),
                'bedtime' => sprintf('%02d:%02d', intdiv($bed % (24 * 60), 60), $bed % 60),
                'wake_time' => sprintf('%02d:%02d', intdiv($wake, 60), $wake % 60),
                'nap_afternoon' => $random->getInt(0, 5) === 0,
                'nap_evening' => $random->getInt(0, 9) === 0,
                'sport' => $random->getInt(0, 2) === 0,
                'morning_score' => max(2, min(10, (int)round($length / 60) - 1 + $random->getInt(-1, 2))),
                'comment' => $comments[$random->getInt(0, count($comments) - 1)],
            ];
        }

        return $nights;
    }
}
