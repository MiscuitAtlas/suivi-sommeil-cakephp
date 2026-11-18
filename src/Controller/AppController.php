<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use Cake\Controller\Controller;
use Cake\Event\EventInterface;

/**
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
class AppController extends Controller
{
    /**
     * Charge les composants communs à toutes les pages.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');
        // Toutes les pages demandent une connexion, sauf celles listées par chaque contrôleur.
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Authorization.Authorization');
    }

    /**
     * Transmet l'utilisateur connecté et le menu à la mise en page.
     *
     * @param \Cake\Event\EventInterface $event
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);
        $user = $this->currentUser();
        $this->set('currentUser', $user);
        if ($user !== null && $this->viewBuilder()->getLayout() !== 'ajax') {
            $this->set('navMenus', $this->fetchTable('Menus')->find()->orderBy(['position' => 'ASC'])->all());
        }
    }

    /**
     * Utilisateur connecté, ou null.
     */
    protected function currentUser(): ?User
    {
        // Lu sur la requête plutôt que via le composant : fonctionne aussi pour les pages d'erreur.
        $identity = $this->request->getAttribute('identity');
        $user = $identity?->getOriginalData();

        return $user instanceof User ? $user : null;
    }

    /**
     * Identifiant de l'utilisateur connecté ; les actions qui l'appellent exigent une connexion.
     */
    protected function currentUserId(): int
    {
        return (int)$this->Authentication->getIdentityData('id');
    }
}
