<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use Authorization\IdentityInterface;

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
}
