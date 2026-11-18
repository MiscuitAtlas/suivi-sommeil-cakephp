<?php
declare(strict_types=1);

namespace App\Service;

use Cake\I18n\Time;
use InvalidArgumentException;

/**
 * Règles de calcul du sommeil : un cycle dure 90 minutes en moyenne, et l'on se réveille
 * plus facilement à la fin d'un cycle qu'au milieu.
 */
final class SleepCycles
{
    public const CYCLE_MINUTES = 90;

    /** Nombre de cycles pour qu'une nuit soit jugée suffisante. */
    public const NIGHT_GOAL = 5;

    /** Objectif hebdomadaire : 42 cycles, soit six par nuit. */
    public const WEEK_GOAL = 42;

    /** Objectif hebdomadaire : quatre jours d'affilée avec au moins cinq cycles. */
    public const STREAK_GOAL = 4;

    /** Écart toléré, en cycle, pour considérer qu'un réveil tombe en fin de cycle. */
    public const CYCLE_END_TOLERANCE = 0.2;

    public const MIN_DURATION = 60;
    public const MAX_DURATION = 16 * 60;

    /**
     * Minutes écoulées depuis minuit pour une heure « 22:30 » ou un objet Time.
     */
    public static function minutesOf(Time|string $time): int
    {
        if (is_string($time)) {
            if (!preg_match('/^([01]?\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/', $time, $m)) {
                throw new InvalidArgumentException(sprintf('Heure invalide : « %s ».', $time));
            }

            return (int)$m[1] * 60 + (int)$m[2];
        }

        return $time->getHours() * 60 + $time->getMinutes();
    }

    /**
     * Durée entre le coucher et le lever, en minutes. Un lever plus tôt que le coucher
     * signifie que la nuit passe minuit (23:00 → 07:00 donne 8 heures).
     */
    public static function durationMinutes(Time|string $bedtime, Time|string $wakeTime): int
    {
        $duration = self::minutesOf($wakeTime) - self::minutesOf($bedtime);

        return $duration > 0 ? $duration : $duration + 24 * 60;
    }

    /**
     * Nombre de cycles de 90 minutes, arrondi au dixième.
     *
     * @param int $minutes
     * @return float
     */
    public static function cycles(int $minutes): float
    {
        return round($minutes / self::CYCLE_MINUTES, 1);
    }

    /**
     * Vrai si le réveil tombe près de la fin d'un cycle (4,9 ou 5,1 cycles, mais pas 5,5).
     */
    public static function endsOnCycle(float $cycles): bool
    {
        // Petite marge : 5 - 4,8 vaut 0,2000…02 en virgule flottante.
        return $cycles >= 1 && abs($cycles - round($cycles)) <= self::CYCLE_END_TOLERANCE + 1e-9;
    }

    /**
     * Une nuit est suffisante à partir de cinq cycles.
     *
     * @param float $cycles
     * @return bool
     */
    public static function isEnough(float $cycles): bool
    {
        return $cycles >= self::NIGHT_GOAL;
    }

    /**
     * Heures de réveil qui tombent en fin de cycle, pour une heure de coucher donnée.
     *
     * @param list<int> $counts Nombres de cycles proposés.
     * @return array<int, string> Nombre de cycles => heure « HH:MM ».
     */
    public static function suggestedWakeTimes(Time|string $bedtime, array $counts = [5, 6]): array
    {
        $start = self::minutesOf($bedtime);
        $times = [];
        foreach ($counts as $count) {
            $minutes = ($start + $count * self::CYCLE_MINUTES) % (24 * 60);
            $times[$count] = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
        }

        return $times;
    }

    /**
     * « 7 h 30 », « 8 h », « 45 min ».
     */
    public static function formatDuration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;
        if ($hours === 0) {
            return $rest . ' min';
        }

        return $rest === 0 ? $hours . ' h' : sprintf('%d h %02d', $hours, $rest);
    }

    /**
     * Plus longue suite de jours consécutifs du calendrier avec au moins [$minCycles] cycles.
     * Un jour sans nuit enregistrée interrompt la série.
     *
     * @param array<string, float> $cyclesByDate Date « Y-m-d » => nombre de cycles.
     */
    public static function longestStreak(array $cyclesByDate, int $minCycles = self::NIGHT_GOAL): int
    {
        ksort($cyclesByDate);
        $best = 0;
        $current = 0;
        $previous = null;
        foreach ($cyclesByDate as $date => $cycles) {
            // Numéro du jour calculé en UTC : les changements d'heure ne cassent pas la série.
            $day = intdiv((int)strtotime($date . ' 00:00:00 UTC'), 86400);
            if ($cycles < $minCycles) {
                $current = 0;
            } elseif ($previous !== null && $current > 0 && $day - $previous === 1) {
                $current++;
            } else {
                $current = 1;
            }
            $previous = $day;
            $best = max($best, $current);
        }

        return $best;
    }
}
