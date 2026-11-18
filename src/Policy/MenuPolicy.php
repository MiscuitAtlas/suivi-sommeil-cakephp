<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Menu;
use Authorization\IdentityInterface;

/**
 * Le menu de navigation est géré par les administrateurs uniquement.
 */
class MenuPolicy
{
    /**
     * Seul un administrateur modifie un lien du menu.
     *
     * @param \Authorization\IdentityInterface $user
     * @param \App\Model\Entity\Menu $menu
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Menu $menu): bool
    {
        return AdminCheck::canChangeMenu($user);
    }

    /**
     * Seul un administrateur supprime un lien du menu.
     *
     * @param \Authorization\IdentityInterface $user
     * @param \App\Model\Entity\Menu $menu
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Menu $menu): bool
    {
        return AdminCheck::canChangeMenu($user);
    }

    /**
     * Seul un administrateur change l'ordre des liens.
     *
     * @param \Authorization\IdentityInterface $user
     * @param \App\Model\Entity\Menu $menu
     * @return bool
     */
    public function canMove(IdentityInterface $user, Menu $menu): bool
    {
        return AdminCheck::canChangeMenu($user);
    }
}
