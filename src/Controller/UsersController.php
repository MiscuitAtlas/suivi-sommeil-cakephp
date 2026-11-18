<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\UsersTable;
use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * Inscription, connexion et compte de l'utilisateur connecté. Aucune action ne prend
 * d'identifiant en paramètre : chacun ne peut agir que sur son propre compte.
 */
class UsersController extends AppController
{
    protected UsersTable $Users;

    /**
     * Ouvre la connexion et l'inscription aux visiteurs.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Users = $this->fetchTable('Users');
    }

    /**
     * Ces pages ne portent sur aucune ressource : l'autorisation est vérifiée à la main.
     *
     * @param \Cake\Event\EventInterface $event
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['login', 'register']);
        // Ces pages ne portent que sur le compte de la personne connectée.
        $this->Authorization->skipAuthorization();
    }

    /**
     * Connexion par email et mot de passe.
     *
     * @return \Cake\Http\Response|null
     */
    public function login(): ?Response
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        if ($result !== null && $result->isValid()) {
            $target = $this->Authentication->getLoginRedirect() ?? ['controller' => 'Dashboard', 'action' => 'index'];

            return $this->redirect($target);
        }
        if ($this->request->is('post')) {
            // Même message que l'email existe ou non.
            $this->Flash->error('Email ou mot de passe incorrect.');
        }

        return null;
    }

    /**
     * Création d'un compte, puis connexion directe.
     *
     * @return \Cake\Http\Response|null
     */
    public function register(): ?Response
    {
        if ($this->currentUser() !== null) {
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
        }
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData(), [
                'fields' => ['first_name', 'last_name', 'email', 'password'],
            ]);
            if ($this->Users->save($user)) {
                $this->Authentication->setIdentity($user);
                $this->Flash->success('Bienvenue ! Enregistrez votre première nuit.');

                return $this->redirect(['controller' => 'Nights', 'action' => 'add']);
            }
            $this->Flash->error('Le compte n\'a pas pu être créé : corrigez les champs signalés.');
        }
        $user->unset(['password']);
        $this->set(compact('user'));

        return null;
    }

    /**
     * Déconnexion, uniquement en POST.
     *
     * @return \Cake\Http\Response
     */
    public function logout(): Response
    {
        $this->request->allowMethod(['post']);
        $this->Authentication->logout();
        $this->Flash->success('Vous êtes déconnecté.');

        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Prénom, nom et adresse email du compte connecté.
     */
    public function account(): ?Response
    {
        $user = $this->Users->get($this->currentUserId());
        if ($this->request->is(['post', 'put', 'patch'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData(), [
                'fields' => ['first_name', 'last_name', 'email'],
            ]);
            if ($this->Users->save($user)) {
                // La session garde une copie de l'utilisateur : on la met à jour.
                $this->Authentication->setIdentity($user);
                $this->Flash->success('Vos informations ont été mises à jour.');

                return $this->redirect(['action' => 'account']);
            }
            $this->Flash->error('Vos informations n\'ont pas pu être enregistrées.');
        }
        $this->set(compact('user'));

        return null;
    }

    /**
     * Changement de mot de passe : l'ancien est exigé.
     */
    public function password(): ?Response
    {
        $user = $this->Users->get($this->currentUserId());
        if ($this->request->is(['post', 'put', 'patch'])) {
            if (!$user->checkPassword((string)$this->request->getData('current_password'))) {
                $this->Flash->error('Le mot de passe actuel est incorrect.');
            } else {
                $user = $this->Users->patchEntity($user, $this->request->getData(), [
                    'fields' => ['password'],
                    'validate' => 'password',
                ]);
                if (!$user->hasErrors() && $this->Users->save($user)) {
                    $this->request->getSession()->renew();
                    $this->Authentication->setIdentity($user);
                    $this->Flash->success('Votre mot de passe a été changé.');

                    return $this->redirect(['action' => 'account']);
                }
                $this->Flash->error('Le nouveau mot de passe n\'est pas valide.');
            }
        }
        $this->set(compact('user'));

        return null;
    }

    /**
     * Suppression du compte et de toutes ses nuits, après confirmation par le mot de passe.
     */
    public function delete(): ?Response
    {
        $user = $this->Users->get($this->currentUserId());
        if ($this->request->is(['post', 'delete'])) {
            if (!$user->checkPassword((string)$this->request->getData('password'))) {
                $this->Flash->error('Le mot de passe est incorrect : le compte n\'a pas été supprimé.');
            } elseif ($this->Users->delete($user)) {
                $this->Authentication->logout();
                $this->Flash->success('Votre compte et vos nuits ont été supprimés.');

                return $this->redirect('/');
            } else {
                $this->Flash->error('Le compte n\'a pas pu être supprimé.');
            }
        }
        $this->set(compact('user'));

        return null;
    }
}
