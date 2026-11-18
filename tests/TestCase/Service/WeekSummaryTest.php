<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\Night;
use App\Service\WeekSummary;
use Cake\I18n\Date;
use Cake\TestSuite\TestCase;

class WeekSummaryTest extends TestCase
{
    private function night(string $date, float $cycles, int $duration, ?int $score = null, bool $nap = false, bool $sport = false): Night
    {
        return new Night([
            'sleep_date' => new Date($date),
            'cycles' => $cycles,
            'duration' => $duration,
            'morning_score' => $score,
            'nap_afternoon' => $nap,
            'nap_evening' => false,
            'sport' => $sport,
        ], ['guard' => false]);
    }

    public function testMondayOf(): void
    {
        $this->assertSame('2026-11-16', WeekSummary::mondayOf(new Date('2026-11-18'))->format('Y-m-d'));
        $this->assertSame('2026-11-16', WeekSummary::mondayOf(new Date('2026-11-16'))->format('Y-m-d'));
        $this->assertSame('2026-11-16', WeekSummary::mondayOf(new Date('2026-11-22'))->format('Y-m-d'));
    }

    public function testSummary(): void
    {
        $summary = new WeekSummary(new Date('2026-11-16'), [
            $this->night('2026-11-16', 5.0, 450, 7, sport: true),
            $this->night('2026-11-17', 6.0, 540, 9),
            $this->night('2026-11-18', 5.3, 480, null, nap: true),
            $this->night('2026-11-20', 4.0, 360, 5),
            // Hors de la semaine : ignorée.
            $this->night('2026-11-23', 6.0, 540, 10),
        ]);

        $this->assertCount(7, $summary->days);
        $this->assertNull($summary->days['2026-11-19']);
        $this->assertSame(4, $summary->nightCount);
        $this->assertSame(20.3, $summary->totalCycles);
        $this->assertSame(3, $summary->longestStreak);
        $this->assertSame(458, $summary->averageDuration);
        $this->assertSame(7.0, $summary->averageScore);
        $this->assertSame(1, $summary->napCount);
        $this->assertSame(1, $summary->sportCount);
        $this->assertFalse($summary->reachesCycleGoal());
        $this->assertFalse($summary->reachesStreakGoal());
        $this->assertSame('2026-11-22', $summary->sunday()->format('Y-m-d'));
    }

    public function testGoalsReached(): void
    {
        $nights = [];
        foreach (range(16, 22) as $day) {
            $nights[] = $this->night("2026-11-{$day}", 6.0, 540);
        }
        $summary = new WeekSummary(new Date('2026-11-16'), $nights);
        $this->assertSame(42.0, $summary->totalCycles);
        $this->assertTrue($summary->reachesCycleGoal());
        $this->assertTrue($summary->reachesStreakGoal());
    }

    public function testEmptyWeek(): void
    {
        $summary = new WeekSummary(new Date('2026-11-16'), []);
        $this->assertSame(0, $summary->nightCount);
        $this->assertSame(0.0, $summary->totalCycles);
        $this->assertNull($summary->averageDuration);
        $this->assertNull($summary->averageScore);
    }
}
