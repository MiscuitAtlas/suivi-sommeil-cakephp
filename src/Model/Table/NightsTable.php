<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Night;
use App\Service\SleepCycles;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\I18n\Date;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\Night newEmptyEntity()
 * @method \App\Model\Entity\Night get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Night patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 */
class NightsTable extends Table
{
    /**
     * Associations et comportements de la table des nuits.
     *
     * @param array $config
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('nights');
        $this->setDisplayField('sleep_date');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Users', ['foreignKey' => 'user_id', 'joinType' => 'INNER']);
    }

    /**
     * Règles de saisie d'une nuit.
     *
     * @param \Cake\Validation\Validator $validator
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->date('sleep_date', ['ymd'], 'Indiquez une date valide.')
            ->requirePresence('sleep_date', 'create')
            ->notEmptyDate('sleep_date', 'Indiquez la date du réveil.')
            ->add('sleep_date', 'notInFuture', [
                // La validation porte sur la saisie brute (« 2026-11-18 ») : les dates se comparent comme du texte.
                'rule' => fn($value) => ($value instanceof Date ? $value->format('Y-m-d') : (string)$value)
                    <= Date::today()->format('Y-m-d'),
                'message' => 'La date ne peut pas être dans le futur.',
            ]);

        foreach (['bedtime' => 'l\'heure du coucher', 'wake_time' => 'l\'heure du lever'] as $field => $label) {
            $validator
                ->time($field, 'Indiquez ' . $label . ' au format HH:MM.')
                ->requirePresence($field, 'create')
                ->notEmptyTime($field, 'Indiquez ' . $label . '.');
        }

        $validator
            ->boolean('nap_afternoon')
            ->boolean('nap_evening')
            ->boolean('sport');

        $validator
            ->allowEmptyString('morning_score')
            ->integer('morning_score', 'La forme au réveil est une note de 0 à 10.')
            ->range('morning_score', [0, 10], 'La forme au réveil est une note de 0 à 10.');

        $validator
            ->allowEmptyString('comment')
            ->maxLength('comment', 1000, 'Le commentaire ne doit pas dépasser 1 000 caractères.');

        return $validator;
    }

    /**
     * Règles métier : propriétaire existant, une nuit par date, durée plausible.
     *
     * @param \Cake\ORM\RulesChecker $rules
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add(
            $rules->isUnique(
                ['user_id', 'sleep_date'],
                'Une nuit est déjà enregistrée à cette date : modifiez-la plutôt.',
            ),
            ['errorField' => 'sleep_date'],
        );
        $rules->add(function (Night $night) {
            if (!$night->bedtime || !$night->wake_time) {
                return true;
            }
            $duration = SleepCycles::durationMinutes($night->bedtime, $night->wake_time);

            return $duration >= SleepCycles::MIN_DURATION && $duration <= SleepCycles::MAX_DURATION;
        }, 'duration', [
            'errorField' => 'wake_time',
            'message' => 'La nuit doit durer entre 1 heure et 16 heures.',
        ]);

        return $rules;
    }

    /**
     * La durée et le nombre de cycles sont toujours recalculés à partir des heures.
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $timesChanged = $entity->isNew() || $entity->isDirty('bedtime') || $entity->isDirty('wake_time');
        if ($entity instanceof Night && $timesChanged) {
            $entity->duration = SleepCycles::durationMinutes($entity->bedtime, $entity->wake_time);
            $entity->cycles = SleepCycles::cycles($entity->duration);
        }
    }

    /**
     * Nuits d'un utilisateur, de la plus récente à la plus ancienne.
     */
    public function findOwnedBy(SelectQuery $query, int $userId): SelectQuery
    {
        return $query->where(['Nights.user_id' => $userId])->orderBy(['Nights.sleep_date' => 'DESC']);
    }

    /**
     * Nuits d'un utilisateur entre deux dates incluses, dans l'ordre chronologique.
     */
    public function findBetween(SelectQuery $query, int $userId, Date $from, Date $to): SelectQuery
    {
        return $query
            ->where([
                'Nights.user_id' => $userId,
                'Nights.sleep_date >=' => $from,
                'Nights.sleep_date <=' => $to,
            ])
            ->orderBy(['Nights.sleep_date' => 'ASC']);
    }
}
