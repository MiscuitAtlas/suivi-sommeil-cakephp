<?php
declare(strict_types=1);

use Migrations\BaseSeed;

/**
 * Menu de navigation par défaut : bin/cake seeds run MenusSeed
 */
class MenusSeed extends BaseSeed
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $links = [
            ['Tableau de bord', '/tableau-de-bord'],
            ['Ajouter une nuit', '/nuits/ajouter'],
            ['Historique', '/nuits'],
            ['Bilan de la semaine', '/semaine'],
            ['Mon compte', '/compte'],
        ];
        $rows = [];
        foreach ($links as $i => [$label, $url]) {
            $rows[] = ['position' => $i + 1, 'label' => $label, 'url' => $url, 'created' => $now, 'modified' => $now];
        }
        $this->table('menus')->insert($rows)->save();
    }
}
