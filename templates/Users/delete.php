<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Supprimer mon compte');
?>
<div class="narrow">
    <h1>Supprimer mon compte</h1>
    <section class="panel">
        <p>Votre compte <strong><?= h($user->email) ?></strong> et toutes vos nuits seront effacés. Cette action est définitive.</p>
        <?= $this->Form->create(null, ['data-confirm' => 'Supprimer définitivement votre compte et toutes vos nuits ?']) ?>
        <?= $this->Form->control('password', ['type' => 'password', 'label' => 'Confirmez avec votre mot de passe', 'autocomplete' => 'current-password', 'required' => true]) ?>
        <div class="button-row">
            <?= $this->Form->button('Supprimer définitivement', ['class' => 'button danger']) ?>
            <a class="button secondary" href="<?= $this->Url->build(['action' => 'account']) ?>">Annuler</a>
        </div>
        <?= $this->Form->end() ?>
    </section>
</div>
