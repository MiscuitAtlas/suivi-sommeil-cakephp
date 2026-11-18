<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\SleepCycles;
use Cake\Event\EventInterface;
use Cake\Http\Response;

class PagesController extends AppController
{
    /**
     * La page d'accueil est publique.
     *
     * @param \Cake\Event\EventInterface $event
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['home']);
    }

    /**
     * Présentation de l'application ; un utilisateur connecté va directement à son tableau de bord.
     */
    public function home(): ?Response
    {
        $this->Authorization->skipAuthorization();
        if ($this->currentUser() !== null) {
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
        }
        $this->set('example', SleepCycles::suggestedWakeTimes('23:00', [4, 5, 6]));

        return null;
    }
}
