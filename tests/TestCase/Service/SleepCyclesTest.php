<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\SleepCycles;
use Cake\I18n\Time;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

class SleepCyclesTest extends TestCase
{
    public static function durations(): array
    {
        return [
            'nuit qui passe minuit' => ['23:00', '07:00', 480],
            'coucher après minuit' => ['00:30', '06:00', 330],
            'sieste dans la journée' => ['14:00', '15:30', 90],
            'une minute avant minuit' => ['23:59', '00:01', 2],
            'heures égales : 24 heures' => ['07:00', '07:00', 1440],
        ];
    }

    #[DataProvider('durations')]
    public function testDurationMinutes(string $bed, string $wake, int $expected): void
    {
        $this->assertSame($expected, SleepCycles::durationMinutes($bed, $wake));
    }

    public function testDurationAcceptsTimeObjects(): void
    {
        $this->assertSame(450, SleepCycles::durationMinutes(new Time('23:00:00'), new Time('06:30:00')));
    }

    public function testRejectsInvalidTime(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SleepCycles::minutesOf('25:00');
    }

    public function testCyclesAreRoundedToOneDecimal(): void
    {
        $this->assertSame(5.0, SleepCycles::cycles(450));
        $this->assertSame(3.7, SleepCycles::cycles(330));
        $this->assertSame(5.3, SleepCycles::cycles(480));
    }

    public function testEndsOnCycle(): void
    {
        $this->assertTrue(SleepCycles::endsOnCycle(5.0));
        $this->assertTrue(SleepCycles::endsOnCycle(4.8));
        $this->assertTrue(SleepCycles::endsOnCycle(6.2));
        $this->assertFalse(SleepCycles::endsOnCycle(5.5));
        $this->assertFalse(SleepCycles::endsOnCycle(0.1), 'moins d\'un cycle : pas de fin de cycle');
    }

    public function testSuggestedWakeTimes(): void
    {
        $this->assertSame([5 => '06:30', 6 => '08:00'], SleepCycles::suggestedWakeTimes('23:00'));
        $this->assertSame([1 => '00:30'], SleepCycles::suggestedWakeTimes('23:00', [1]));
    }

    public function testFormatDuration(): void
    {
        $this->assertSame('7 h 30', SleepCycles::formatDuration(450));
        $this->assertSame('8 h', SleepCycles::formatDuration(480));
        $this->assertSame('45 min', SleepCycles::formatDuration(45));
        $this->assertSame('6 h 05', SleepCycles::formatDuration(365));
    }

    public function testLongestStreakCountsConsecutiveCalendarDays(): void
    {
        $this->assertSame(3, SleepCycles::longestStreak([
            '2026-11-02' => 5.0,
            '2026-11-03' => 6.1,
            '2026-11-04' => 5.4,
            '2026-11-05' => 3.0,
            '2026-11-06' => 5.0,
        ]));
    }

    public function testStreakIsBrokenByAMissingDay(): void
    {
        // Le 4 n'est pas renseigné : les deux séries ne se rejoignent pas.
        $this->assertSame(2, SleepCycles::longestStreak([
            '2026-11-02' => 5.0,
            '2026-11-03' => 5.0,
            '2026-11-05' => 5.0,
            '2026-11-06' => 5.0,
        ]));
    }

    public function testStreakAcrossDaylightSavingChange(): void
    {
        // Passage à l'heure d'hiver le 25 octobre 2026.
        $this->assertSame(3, SleepCycles::longestStreak(['2026-10-24' => 6.0, '2026-10-25' => 6.0, '2026-10-26' => 6.0]));
    }
}
