<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\DemoNights;
use App\Service\SleepCycles;
use Cake\I18n\Date;
use Cake\TestSuite\TestCase;

class DemoNightsTest extends TestCase
{
    public function testThreeWeeksEndingTodayWithTwoDaysMissing(): void
    {
        $nights = DemoNights::generate(new Date('2026-11-18'));

        $this->assertCount(DemoNights::DAYS - count(DemoNights::SKIPPED_DAYS), $nights);
        $this->assertSame('2026-10-29', $nights[0]['sleep_date']);
        $this->assertSame('2026-11-18', end($nights)['sleep_date']);
        $dates = array_column($nights, 'sleep_date');
        $this->assertSame($dates, array_unique($dates));
        $this->assertNotContains('2026-11-09', $dates);
    }

    public function testNightsArePlausible(): void
    {
        foreach (DemoNights::generate(new Date('2026-11-18')) as $night) {
            $duration = SleepCycles::durationMinutes($night['bedtime'], $night['wake_time']);
            $this->assertGreaterThanOrEqual(330, $duration);
            $this->assertLessThanOrEqual(540, $duration);
            $this->assertGreaterThanOrEqual(2, $night['morning_score']);
            $this->assertLessThanOrEqual(10, $night['morning_score']);
        }
    }

    public function testTheLastNightReachesFiveCycles(): void
    {
        $nights = DemoNights::generate(Date::today());
        $last = end($nights);

        $this->assertSame(['23:15', '06:45'], [$last['bedtime'], $last['wake_time']]);
        $this->assertSame(5.0, SleepCycles::cycles(SleepCycles::durationMinutes('23:15', '06:45')));
    }

    public function testTheSameSeedGivesTheSameNights(): void
    {
        $today = new Date('2026-11-18');

        $this->assertSame(DemoNights::generate($today), DemoNights::generate($today));
        $this->assertNotSame(DemoNights::generate($today), DemoNights::generate($today, 7));
    }
}
