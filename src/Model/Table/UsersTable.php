<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @property \App\Model\Table\NightsTable&\Cake\ORM\Association\HasMany $Nights
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 */
class UsersTable extends Table
{
    public const PASSWORD_MIN_LENGTH = 8;

    /**
     * Associations et comportements de la table des utilisateurs.
     *
     * @param array $config
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('users');
        $this->setDisplayField('first_name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->hasMany('Nights', ['dependent' => true, 'cascadeCallbacks' => false]);
    }

    /**
     * L'adresse email est comparée sans tenir compte des majuscules ni des espaces autour.
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        if (isset($data['email']) && is_string($data['email'])) {
            $data['email'] = mb_strtolower(trim($data['email']));
        }
    }

    /**
     * Règles de saisie du profil.
     *
     * @param \Cake\Validation\Validator $validator
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('first_name')
            ->maxLength('first_name', 100, 'Le prénom est trop long.')
            ->requirePresence('first_name', 'create')
            ->notEmptyString('first_name', 'Indiquez votre prénom.');

        $validator
            ->scalar('last_name')
            ->maxLength('last_name', 100, 'Le nom est trop long.')
            ->requirePresence('last_name', 'create')
            ->notEmptyString('last_name', 'Indiquez votre nom.');

        $validator
            ->email('email', false, 'Cette adresse email n\'est pas valide.')
            ->maxLength('email', 191, 'Cette adresse email est trop longue.')
            ->requirePresence('email', 'create')
            ->notEmptyString('email', 'Indiquez votre adresse email.');

        return $this->validationPassword($validator);
    }

    /**
     * Règles du mot de passe, aussi utilisées seules lors d'un changement de mot de passe.
     */
    public function validationPassword(Validator $validator): Validator
    {
        $validator
            ->scalar('password')
            ->requirePresence('password', 'create')
            ->notEmptyString('password', 'Choisissez un mot de passe.')
            ->minLength(
                'password',
                self::PASSWORD_MIN_LENGTH,
                sprintf('Le mot de passe doit contenir au moins %d caractères.', self::PASSWORD_MIN_LENGTH),
            )
            // bcrypt ignore tout ce qui dépasse 72 octets.
            ->add('password', 'bcrypt', [
                'rule' => fn($value) => strlen((string)$value) <= 72,
                'message' => 'Le mot de passe est trop long (72 octets au maximum).',
            ])
            ->add('password_confirm', 'same', [
                'rule' => fn($value, $context) => $value === ($context['data']['password'] ?? null),
                'message' => 'Les deux mots de passe ne correspondent pas.',
            ]);

        return $validator;
    }

    /**
     * Un email ne sert qu'à un seul compte.
     *
     * @param \Cake\ORM\RulesChecker $rules
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(
            $rules->isUnique(['email'], 'Un compte existe déjà avec cette adresse email.'),
            ['errorField' => 'email'],
        );

        return $rules;
    }
}
