<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Menu;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Liens du menu de navigation, dans l'ordre choisi par les administrateurs.
 *
 * @method \App\Model\Entity\Menu newEmptyEntity()
 * @method \App\Model\Entity\Menu get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 */
class MenusTable extends Table
{
    /**
     * Comportements de la table des menus.
     *
     * @param array $config
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('menus');
        $this->setDisplayField('label');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    /**
     * Un lien a un libellé et une adresse interne au site.
     *
     * @param \Cake\Validation\Validator $validator
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('label')
            ->maxLength('label', 100, 'L\'intitulé est trop long.')
            ->requirePresence('label', 'create')
            ->notEmptyString('label', 'Indiquez l\'intitulé du lien.');

        // Uniquement des chemins internes : pas de lien « javascript: » ni vers un autre site.
        $validator
            ->scalar('url')
            ->maxLength('url', 255, 'Le lien est trop long.')
            ->requirePresence('url', 'create')
            ->notEmptyString('url', 'Indiquez le lien.')
            ->regex(
                'url',
                '#^/(?!/)[A-Za-z0-9/_\-.?=&%]*$#',
                'Le lien doit être un chemin du site, par exemple /semaine.',
            );

        return $validator;
    }

    /**
     * Un nouveau lien se place en fin de menu.
     */
    public function beforeSave(EventInterface $event, Menu $menu): void
    {
        if ($menu->isNew() && $menu->position === null) {
            $last = $this->find()->select(['max' => $this->find()->func()->max('position')])->first();
            $menu->position = (int)($last?->get('max') ?? 0) + 1;
        }
    }

    /**
     * Échange la position d'un lien avec celle de son voisin.
     *
     * @param string $direction « up » ou « down ».
     */
    public function move(Menu $menu, string $direction): bool
    {
        $neighbour = $this->find()
            ->where(['position ' . ($direction === 'up' ? '<' : '>') => $menu->position])
            ->orderBy(['position' => $direction === 'up' ? 'DESC' : 'ASC'])
            ->first();
        if (!$neighbour instanceof Menu) {
            return false;
        }

        return $this->getConnection()->transactional(function () use ($menu, $neighbour) {
            [$menu->position, $neighbour->position] = [$neighbour->position, $menu->position];

            return (bool)$this->save($menu) && (bool)$this->save($neighbour);
        });
    }
}
