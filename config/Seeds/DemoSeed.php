<?php
declare(strict_types=1);

use App\Service\SleepCycles;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Migrations\BaseSeed;

/**
 * Compte de démonstration avec trois semaines de nuits :
 * bin/cake seeds run DemoSeed
 *
 * Connexion : demo@exemple.fr / demo-sommeil (compte administrateur)
 */
class DemoSeed extends BaseSeed
{
    public function getDependencies(): array
    {
        return ['MenusSeed'];
    }

    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->table('users')->insert([
            'first_name' => 'Camille',
            'last_name' => 'Martin',
            'email' => 'demo@exemple.fr',
            'password' => (new DefaultPasswordHasher())->hash('demo-sommeil'),
            'role' => 'admin',
            'created' => $now,
            'modified' => $now,
        ])->save();
        $userId = (int)($this->fetchRow("SELECT id FROM users WHERE email = 'demo@exemple.fr'")['id'] ?? 0);

        // Nuits plausibles et reproductibles : coucher entre 22 h 30 et 0 h 45.
        mt_srand(2048);
        $comments = [null, null, null, 'Réveil difficile.', 'Très bonne nuit.', 'Café trop tard.', null, 'Bruit dans la rue.'];
        $rows = [];
        for ($daysAgo = 20; $daysAgo >= 0; $daysAgo--) {
            if ($daysAgo === 9 || $daysAgo === 15) {
                continue; // quelques jours sans saisie
            }
            $bed = 22 * 60 + 30 + mt_rand(0, 9) * 15;
            $length = [390, 405, 420, 450, 450, 465, 480, 495, 540, 330][mt_rand(0, 9)];
            if ($daysAgo === 0) {
                // La nuit du jour est complète : cinq cycles, réveil en fin de cycle.
                [$bed, $length] = [23 * 60 + 15, 450];
            }
            $wake = ($bed + $length) % (24 * 60);
            $bedtime = sprintf('%02d:%02d:00', intdiv($bed % (24 * 60), 60), $bed % 60);
            $wakeTime = sprintf('%02d:%02d:00', intdiv($wake, 60), $wake % 60);
            $duration = SleepCycles::durationMinutes($bedtime, $wakeTime);
            $rows[] = [
                'user_id' => $userId,
                'sleep_date' => date('Y-m-d', strtotime("-{$daysAgo} days")),
                'bedtime' => $bedtime,
                'wake_time' => $wakeTime,
                'duration' => $duration,
                'cycles' => SleepCycles::cycles($duration),
                'nap_afternoon' => mt_rand(0, 5) === 0,
                'nap_evening' => mt_rand(0, 9) === 0,
                'sport' => mt_rand(0, 2) === 0,
                'morning_score' => max(2, min(10, (int)round($duration / 60) - 1 + mt_rand(-1, 2))),
                'comment' => $comments[mt_rand(0, count($comments) - 1)],
                'created' => $now,
                'modified' => $now,
            ];
        }
        $this->table('nights')->insert($rows)->save();
    }
}
