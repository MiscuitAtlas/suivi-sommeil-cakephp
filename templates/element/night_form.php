<?php
/**
 * Formulaire commun à l'ajout et à la modification d'une nuit.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Night $night
 * @var string $submit
 */
?>
<?= $this->Form->create($night, ['novalidate' => false]) ?>
<div class="form-grid three">
    <?= $this->Form->control('sleep_date', [
        'type' => 'date',
        'label' => 'Date du réveil',
        'max' => \Cake\I18n\Date::today()->format('Y-m-d'),
        'help' => 'La nuit du 17 au 18 s\'enregistre au 18.',
    ]) ?>
    <?= $this->Form->control('bedtime', [
        'type' => 'time',
        'label' => 'Heure du coucher',
        'value' => $night->bedtime?->format('H:i'),
        'step' => 60,
        'data-bedtime' => true,
    ]) ?>
    <?= $this->Form->control('wake_time', [
        'type' => 'time',
        'label' => 'Heure du lever',
        'value' => $night->wake_time?->format('H:i'),
        'step' => 60,
    ]) ?>
</div>
<p class="suggestion" data-wake-suggestions aria-live="polite" hidden></p>

<div class="form-grid two">
    <?= $this->Form->control('morning_score', [
        'type' => 'select',
        'label' => 'Forme au réveil',
        'options' => array_combine(range(0, 10), array_map(fn($n) => $n . ' / 10', range(0, 10))),
        'empty' => 'Non renseignée',
    ]) ?>
    <fieldset>
        <legend>La veille</legend>
        <?= $this->Form->control('nap_afternoon', ['type' => 'checkbox', 'label' => 'Sieste l\'après-midi']) ?>
        <?= $this->Form->control('nap_evening', ['type' => 'checkbox', 'label' => 'Sieste en soirée']) ?>
        <?= $this->Form->control('sport', ['type' => 'checkbox', 'label' => 'Activité sportive']) ?>
    </fieldset>
</div>

<?= $this->Form->control('comment', [
    'type' => 'textarea',
    'label' => 'Commentaire',
    'maxlength' => 1000,
    'rows' => 3,
    'placeholder' => 'Réveils dans la nuit, café tardif, écran avant de dormir…',
]) ?>

<div class="button-row">
    <?= $this->Form->button($submit, ['class' => 'button']) ?>
    <a class="button secondary" href="<?= $this->Url->build(['controller' => 'Nights', 'action' => 'index']) ?>">Annuler</a>
</div>
<?= $this->Form->end() ?>
