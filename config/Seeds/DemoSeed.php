<?php
declare(strict_types=1);

use App\Service\DemoNights;
use App\Service\SleepCycles;
use Cake\I18n\Date;
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
            'password' => (new DefaultPasswordHasher())->hash(DemoNights::PASSWORD),
            'role' => 'admin',
            'created' => $now,
            'modified' => $now,
        ])->save();
        $userId = (int)($this->fetchRow("SELECT id FROM users WHERE email = 'demo@exemple.fr'")['id'] ?? 0);

        $rows = [];
        foreach (DemoNights::generate(Date::today()) as $night) {
            $duration = SleepCycles::durationMinutes($night['bedtime'], $night['wake_time']);
            $rows[] = [
                'user_id' => $userId,
                'bedtime' => $night['bedtime'] . ':00',
                'wake_time' => $night['wake_time'] . ':00',
                'duration' => $duration,
                'cycles' => SleepCycles::cycles($duration),
                'created' => $now,
                'modified' => $now,
            ] + $night;
        }
        $this->table('nights')->insert($rows)->save();
    }
}
