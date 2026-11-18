<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Menu $menu
 * @var string $submit
 */
?>
<?= $this->Form->create($menu) ?>
<?= $this->Form->control('label', ['label' => 'Intitulé', 'maxlength' => 100]) ?>
<?= $this->Form->control('url', ['label' => 'Lien', 'maxlength' => 255, 'help' => 'Un chemin du site, par exemple /semaine ou /nuits/ajouter.']) ?>
<div class="button-row">
    <?= $this->Form->button($submit, ['class' => 'button']) ?>
    <a class="button secondary" href="<?= $this->Url->build(['action' => 'index']) ?>">Annuler</a>
</div>
<?= $this->Form->end() ?>
