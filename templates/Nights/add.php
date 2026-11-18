<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Night $night
 * @var \App\Model\Entity\Night|null $existing
 */
$this->assign('title', 'Ajouter une nuit');
?>
<div class="page-head">
    <div>
        <h1>Ajouter une nuit</h1>
        <p class="muted">La durée et le nombre de cycles sont calculés automatiquement.</p>
    </div>
</div>
<section class="panel">
    <?php if ($existing !== null) : ?>
        <p class="notice">
            La nuit du <?= $this->Sleep->day($existing->sleep_date) ?> est déjà enregistrée
            (<?= $this->Sleep->cycles($existing->cycles) ?> cycles).
            <a href="<?= $this->Url->build(['action' => 'edit', $existing->id]) ?>">La modifier</a>
            ou choisissez une autre date.
        </p>
    <?php endif; ?>
    <?= $this->element('night_form', ['night' => $night, 'submit' => 'Enregistrer la nuit']) ?>
</section>
