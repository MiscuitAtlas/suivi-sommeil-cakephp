<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use App\Model\Table\UsersTable;
use App\Service\DemoNights;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\I18n\Date;
use Cake\I18n\DateTime;

/**
 * Démonstration en ligne : chaque visiteur reçoit son propre compte, avec trois
 * semaines de nuits déjà saisies. Les visiteurs ne voient jamais les données des autres.
 */
class DemoController extends AppController
{
    /**
     * Adresse des comptes de démonstration (domaine réservé, qui ne reçoit jamais d'email).
     */
    public const EMAIL_DOMAIN = 'demo.invalid';

    protected UsersTable $Users;

    /**
     * La démonstration est ouverte aux visiteurs.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Users = $this->fetchTable('Users');
        $this->Authentication->allowUnauthenticated(['start']);
    }

    /**
     * La page n'existe que sur la démonstration en ligne.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authorization->skipAuthorization();
        if (!Configure::read('Demo.enabled')) {
            throw new NotFoundException();
        }
    }

    /**
     * Crée un compte de démonstration et connecte le visiteur.
     *
     * @return \Cake\Http\Response|null
     */
    public function start(): ?Response
    {
        $this->request->allowMethod(['post']);
        $this->removeExpiredAccounts();

        /** @var \App\Model\Entity\User $user */
        $user = $this->Users->newEntity([
            'first_name' => 'Camille',
            'last_name' => 'Martin',
            'email' => sprintf('visiteur-%s@%s', bin2hex(random_bytes(4)), self::EMAIL_DOMAIN),
            'password' => DemoNights::PASSWORD,
        ]);
        // Administrateur pour voir la gestion du menu, qui reste en lecture seule.
        $user->role = User::ROLE_ADMIN;

        $saved = $this->Users->getConnection()->transactional(function () use ($user): bool {
            if (!$this->Users->save($user)) {
                return false;
            }
            $nights = [];
            foreach (DemoNights::generate(Date::today()) as $data) {
                $night = $this->Users->Nights->newEntity($data);
                $night->user_id = $user->id;
                $nights[] = $night;
            }

            return $this->Users->Nights->saveMany($nights) !== false;
        });
        if (!$saved) {
            $this->Flash->error('La démonstration n\'a pas pu démarrer. Réessayez dans un instant.');

            return $this->redirect('/');
        }

        $this->Authentication->setIdentity($user);
        $this->Flash->success(sprintf(
            'Compte de démonstration créé avec trois semaines de nuits. Il sera supprimé dans deux heures. '
            . 'Identifiants : %s / %s.',
            $user->email,
            DemoNights::PASSWORD,
        ));

        return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
    }

    /**
     * Supprime les comptes de démonstration expirés, et les plus anciens au-delà de la limite.
     *
     * @return void
     */
    protected function removeExpiredAccounts(): void
    {
        $demoAccounts = $this->Users->find()
            ->where(['Users.email LIKE' => '%@' . self::EMAIL_DOMAIN])
            ->orderBy(['Users.created' => 'DESC', 'Users.id' => 'DESC']);
        $limit = DateTime::now()->modify('-' . Configure::read('Demo.lifetime', '2 hours'));
        $keep = max(0, (int)Configure::read('Demo.maxAccounts', 200) - 1);

        $expired = [];
        foreach ($demoAccounts->all() as $index => $account) {
            if ($index >= $keep || $account->created < $limit) {
                $expired[] = $account;
            }
        }
        if ($expired) {
            $this->Users->deleteMany($expired);
        }
    }
}
