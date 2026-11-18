<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Night;
use Cake\I18n\Date;

/**
 * Bilan d'une semaine, du lundi au dimanche.
 */
final class WeekSummary
{
    /**
     * @var array<string, \App\Model\Entity\Night|null> Les sept jours de la semaine, du lundi au dimanche.
     */
    public readonly array $days;
    public readonly float $totalCycles;
    public readonly int $longestStreak;
    public readonly int $nightCount;
    public readonly ?int $averageDuration;
    public readonly ?float $averageScore;
    public readonly int $napCount;
    public readonly int $sportCount;

    /**
     * @param \Cake\I18n\Date $monday Premier jour de la semaine.
     * @param iterable<\App\Model\Entity\Night> $nights Nuits de la semaine.
     */
    public function __construct(public readonly Date $monday, iterable $nights)
    {
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[$monday->addDays($i)->format('Y-m-d')] = null;
        }
        foreach ($nights as $night) {
            $key = $night->sleep_date->format('Y-m-d');
            if (array_key_exists($key, $days)) {
                $days[$key] = $night;
            }
        }
        $this->days = $days;

        /** @var list<\App\Model\Entity\Night> $recorded */
        $recorded = array_values(array_filter($days));
        $this->nightCount = count($recorded);
        $this->totalCycles = round(array_sum(array_map(fn(Night $n) => (float)$n->cycles, $recorded)), 1);
        $this->longestStreak = SleepCycles::longestStreak(
            array_map(fn(?Night $n) => $n === null ? 0.0 : (float)$n->cycles, $days),
        );
        $this->averageDuration = $recorded
            ? (int)round(array_sum(array_map(fn(Night $n) => $n->duration, $recorded)) / count($recorded))
            : null;
        $scores = array_values(array_filter(
            array_map(fn(Night $n) => $n->morning_score, $recorded),
            fn($score) => $score !== null,
        ));
        $this->averageScore = $scores ? round(array_sum($scores) / count($scores), 1) : null;
        $this->napCount = count(array_filter($recorded, fn(Night $n) => $n->nap_afternoon || $n->nap_evening));
        $this->sportCount = count(array_filter($recorded, fn(Night $n) => $n->sport));
    }

    /**
     * Lundi de la semaine qui contient la date donnée.
     *
     * @param \Cake\I18n\Date $date
     * @return \Cake\I18n\Date
     */
    public static function mondayOf(Date $date): Date
    {
        return $date->subDays($date->dayOfWeek - 1);
    }

    /**
     * Dimanche de la semaine résumée.
     *
     * @return \Cake\I18n\Date
     */
    public function sunday(): Date
    {
        return $this->monday->addDays(6);
    }

    /**
     * Objectif hebdomadaire de cycles atteint.
     *
     * @return bool
     */
    public function reachesCycleGoal(): bool
    {
        return $this->totalCycles >= SleepCycles::WEEK_GOAL;
    }

    /**
     * Au moins quatre nuits suffisantes consécutives dans la semaine.
     *
     * @return bool
     */
    public function reachesStreakGoal(): bool
    {
        return $this->longestStreak >= SleepCycles::STREAK_GOAL;
    }
}
