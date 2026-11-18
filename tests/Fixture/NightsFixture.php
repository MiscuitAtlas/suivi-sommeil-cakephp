<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\I18n\Date;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * Nuits d'Alice sur les jours passés de la semaine en cours, et une nuit de Bruno.
 */
class NightsFixture extends TestFixture
{
    public function init(): void
    {
        $today = Date::today();
        $now = '2026-01-01 08:00:00';
        $night = fn(int $id, int $userId, Date $date, string $bed, string $wake, int $duration, float $cycles) => [
            'id' => $id,
            'user_id' => $userId,
            'sleep_date' => $date->format('Y-m-d'),
            'bedtime' => $bed,
            'wake_time' => $wake,
            'duration' => $duration,
            'cycles' => $cycles,
            'nap_afternoon' => false,
            'nap_evening' => false,
            'sport' => $id % 2 === 0,
            'morning_score' => 7,
            'comment' => null,
            'created' => $now,
            'modified' => $now,
        ];
        $this->records = [
            $night(1, 1, $today->subDays(1), '23:00:00', '06:30:00', 450, 5.0),
            $night(2, 1, $today->subDays(2), '00:30:00', '06:00:00', 330, 3.7),
            $night(3, 2, $today->subDays(1), '22:00:00', '07:00:00', 540, 6.0),
        ];
        parent::init();
    }
}
