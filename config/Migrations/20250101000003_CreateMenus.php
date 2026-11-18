<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateMenus extends BaseMigration
{
    public function change(): void
    {
        $this->table('menus')
            ->addColumn('position', 'integer', ['null' => false])
            ->addColumn('label', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('url', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('created', 'datetime', ['null' => false])
            ->addColumn('modified', 'datetime', ['null' => false])
            ->addIndex(['position'])
            ->create();
    }
}
