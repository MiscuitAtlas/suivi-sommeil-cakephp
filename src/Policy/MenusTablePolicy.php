<?php
declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Table;

class MenusTablePolicy
{
    /**
     * Seul un administrateur voit la gestion du menu.
     *
     * @param \Authorization\IdentityInterface $user
     * @param \Cake\ORM\Table $menus
     * @return bool
     */
    public function canIndex(IdentityInterface $user, Table $menus): bool
    {
        return AdminCheck::isAdmin($user);
    }

    /**
     * Seul un administrateur ajoute un lien au menu.
     *
     * @param \Authorization\IdentityInterface $user
     * @param \Cake\ORM\Table $menus
     * @return bool
     */
    public function canAdd(IdentityInterface $user, Table $menus): bool
    {
        return AdminCheck::canChangeMenu($user);
    }
}
