<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Changer de mot de passe');
?>
<div class="narrow">
    <h1>Changer de mot de passe</h1>
    <section class="panel">
        <?= $this->Form->create($user) ?>
        <?= $this->Form->control('current_password', ['type' => 'password', 'label' => 'Mot de passe actuel', 'autocomplete' => 'current-password', 'required' => true]) ?>
        <?= $this->Form->control('password', ['type' => 'password', 'label' => 'Nouveau mot de passe', 'autocomplete' => 'new-password', 'value' => '', 'help' => '8 caractères au minimum.']) ?>
        <?= $this->Form->control('password_confirm', ['type' => 'password', 'label' => 'Confirmer le nouveau mot de passe', 'autocomplete' => 'new-password', 'value' => '']) ?>
        <div class="button-row">
            <?= $this->Form->button('Changer le mot de passe', ['class' => 'button']) ?>
            <a class="button secondary" href="<?= $this->Url->build(['action' => 'account']) ?>">Annuler</a>
        </div>
        <?= $this->Form->end() ?>
    </section>
</div>
