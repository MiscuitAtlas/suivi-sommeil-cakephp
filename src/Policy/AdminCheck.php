<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

final class AdminCheck
{
    /**
     * Indique si l'utilisateur connecté a le rôle administrateur.
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    public static function isAdmin(IdentityInterface $identity): bool
    {
        $user = $identity->getOriginalData();

        return $user instanceof User && $user->isAdmin();
    }

    /**
     * Seul un administrateur modifie le menu, et personne sur la démonstration en ligne :
     * le menu y est partagé par tous les visiteurs.
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    public static function canChangeMenu(IdentityInterface $identity): bool
    {
        return self::isAdmin($identity) && !Configure::read('Demo.enabled');
    }
}
