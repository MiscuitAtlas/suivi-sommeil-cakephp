<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Night $night
 */
$this->assign('title', 'Modifier une nuit');
?>
<div class="page-head">
    <div>
        <h1>Modifier la nuit</h1>
        <p class="muted">Nuit se terminant le <?= h($this->Sleep->day($night->getOriginal('sleep_date'), true)) ?>.</p>
    </div>
</div>
<section class="panel">
    <?= $this->element('night_form', ['night' => $night, 'submit' => 'Enregistrer les modifications']) ?>
</section>
<section class="panel">
    <h2>Supprimer cette nuit</h2>
    <p class="muted">La suppression est définitive.</p>
    <?= $this->Form->postButton('Supprimer la nuit', ['action' => 'delete', $night->id], [
        'class' => 'button danger secondary',
        'form' => ['data-confirm' => 'Supprimer définitivement cette nuit ?'],
    ]) ?>
</section>
