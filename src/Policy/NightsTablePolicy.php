<?php
declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;

class NightsTablePolicy
{
    /**
     * Toute liste de nuits est limitée aux nuits de l'utilisateur connecté.
     */
    public function scopeIndex(IdentityInterface $user, SelectQuery $query): SelectQuery
    {
        return $query->where(['Nights.user_id' => $user->getIdentifier()]);
    }
}
