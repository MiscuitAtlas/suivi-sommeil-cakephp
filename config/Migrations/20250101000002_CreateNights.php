<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateNights extends BaseMigration
{
    public function change(): void
    {
        $this->table('nights')
            ->addColumn('user_id', 'integer', ['null' => false])
            // Date du réveil : la nuit du 17 au 18 est enregistrée au 18.
            ->addColumn('sleep_date', 'date', ['null' => false])
            ->addColumn('bedtime', 'time', ['null' => false])
            ->addColumn('wake_time', 'time', ['null' => false])
            // Calculés par l'application à partir des heures de coucher et de lever.
            ->addColumn('duration', 'integer', ['null' => false, 'comment' => 'minutes'])
            ->addColumn('cycles', 'decimal', ['precision' => 3, 'scale' => 1, 'null' => false])
            ->addColumn('nap_afternoon', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('nap_evening', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('sport', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('morning_score', 'tinyinteger', ['null' => true, 'signed' => false])
            ->addColumn('comment', 'text', ['null' => true])
            ->addColumn('created', 'datetime', ['null' => false])
            ->addColumn('modified', 'datetime', ['null' => false])
            ->addIndex(['user_id', 'sleep_date'], ['unique' => true])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
