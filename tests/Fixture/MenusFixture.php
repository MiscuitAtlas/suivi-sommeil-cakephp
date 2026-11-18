<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class MenusFixture extends TestFixture
{
    public string $table = 'menus';

    public array $records = [
        ['id' => 1, 'position' => 1, 'label' => 'Tableau de bord', 'url' => '/tableau-de-bord', 'created' => '2026-01-01 08:00:00', 'modified' => '2026-01-01 08:00:00'],
        ['id' => 2, 'position' => 2, 'label' => 'Historique', 'url' => '/nuits', 'created' => '2026-01-01 08:00:00', 'modified' => '2026-01-01 08:00:00'],
        ['id' => 3, 'position' => 3, 'label' => 'Bilan de la semaine', 'url' => '/semaine', 'created' => '2026-01-01 08:00:00', 'modified' => '2026-01-01 08:00:00'],
    ];
}
