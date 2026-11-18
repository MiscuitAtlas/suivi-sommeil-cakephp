<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * Trois comptes : Alice (utilisatrice), Bruno (utilisateur) et Admin.
 * Mot de passe de chacun : « mot-de-passe ».
 */
class UsersFixture extends TestFixture
{
    public const PASSWORD = 'mot-de-passe';

    public function init(): void
    {
        $hash = (new DefaultPasswordHasher())->hash(self::PASSWORD);
        $now = '2026-01-01 08:00:00';
        $this->records = [
            ['id' => 1, 'first_name' => 'Alice', 'last_name' => 'Durand', 'email' => 'alice@exemple.fr', 'password' => $hash, 'role' => 'user', 'created' => $now, 'modified' => $now],
            ['id' => 2, 'first_name' => 'Bruno', 'last_name' => 'Petit', 'email' => 'bruno@exemple.fr', 'password' => $hash, 'role' => 'user', 'created' => $now, 'modified' => $now],
            ['id' => 3, 'first_name' => 'Admin', 'last_name' => 'Site', 'email' => 'admin@exemple.fr', 'password' => $hash, 'role' => 'admin', 'created' => $now, 'modified' => $now],
        ];
        parent::init();
    }
}
