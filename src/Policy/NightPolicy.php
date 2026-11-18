<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Night;
use Authorization\IdentityInterface;

/**
 * Une nuit n'est visible et modifiable que par la personne qui l'a enregistrée.
 */
class NightPolicy
{
    /**
     * Une nuit ne se modifie que par son propriétaire.
     *
     * @param \Authorization\IdentityInterface $user
     * @param \App\Model\Entity\Night $night
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Night $night): bool
    {
        return $night->user_id === $user->getIdentifier();
    }

    /**
     * Une nuit ne se supprime que par son propriétaire.
     *
     * @param \Authorization\IdentityInterface $user
     * @param \App\Model\Entity\Night $night
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Night $night): bool
    {
        return $night->user_id === $user->getIdentifier();
    }
}
