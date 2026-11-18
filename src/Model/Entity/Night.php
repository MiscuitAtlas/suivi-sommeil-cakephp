<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Service\SleepCycles;
use Cake\ORM\Entity;

/**
 * Une nuit de sommeil, enregistrée à la date du réveil.
 *
 * @property int $id
 * @property int $user_id
 * @property \Cake\I18n\Date $sleep_date
 * @property \Cake\I18n\Time $bedtime
 * @property \Cake\I18n\Time $wake_time
 * @property int $duration Durée en minutes, calculée.
 * @property string|float $cycles Nombre de cycles de 90 minutes, calculé.
 * @property bool $nap_afternoon
 * @property bool $nap_evening
 * @property bool $sport
 * @property int|null $morning_score Forme au réveil, de 0 à 10.
 * @property string|null $comment
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class Night extends Entity
{
    /**
     * L'utilisateur, la durée et les cycles ne viennent jamais du formulaire.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'sleep_date' => true,
        'bedtime' => true,
        'wake_time' => true,
        'nap_afternoon' => true,
        'nap_evening' => true,
        'sport' => true,
        'morning_score' => true,
        'comment' => true,
    ];

    /**
     * Nombre de cycles de la nuit.
     *
     * @return float
     */
    public function cyclesValue(): float
    {
        return (float)$this->cycles;
    }

    /**
     * La nuit atteint l'objectif de cinq cycles.
     *
     * @return bool
     */
    public function isEnough(): bool
    {
        return SleepCycles::isEnough($this->cyclesValue());
    }

    /**
     * Le réveil tombe en fin de cycle.
     *
     * @return bool
     */
    public function endsOnCycle(): bool
    {
        return SleepCycles::endsOnCycle($this->cyclesValue());
    }
}
