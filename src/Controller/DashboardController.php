<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\NightsTable;
use App\Service\WeekSummary;
use Cake\I18n\Date;

/**
 * Tableau de bord : la dernière nuit, la semaine en cours et les nuits récentes.
 */
class DashboardController extends AppController
{
    protected NightsTable $Nights;

    /**
     * Charge la table des nuits.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Nights = $this->fetchTable('Nights');
    }

    /**
     * Dernière nuit, semaine en cours et nuits récentes.
     *
     * @return void
     */
    public function index(): void
    {
        // Les données sont filtrées sur l'utilisateur connecté par les requêtes ci-dessous.
        $this->Authorization->skipAuthorization();
        $userId = $this->currentUserId();
        $today = Date::today();
        $monday = WeekSummary::mondayOf($today);

        $recent = $this->Nights->find('ownedBy', userId: $userId)->limit(7)->all()->toList();
        $lastNight = $recent[0] ?? null;
        $week = new WeekSummary(
            $monday,
            $this->Nights->find('between', userId: $userId, from: $monday, to: $monday->addDays(6))->all(),
        );

        $this->set([
            'lastNight' => $lastNight,
            'hasToday' => $lastNight !== null && $lastNight->sleep_date->equals($today),
            'week' => $week,
            'recent' => $recent,
            'total' => $this->Nights->find('ownedBy', userId: $userId)->count(),
        ]);
    }
}
