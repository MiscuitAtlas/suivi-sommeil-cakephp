<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Lien du menu de navigation, géré par les administrateurs.
 *
 * @property int $id
 * @property int $position
 * @property string $label
 * @property string $url
 */
class Menu extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'label' => true,
        'url' => true,
    ];
}
