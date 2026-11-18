<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Mon compte');
?>
<div class="page-head">
    <h1>Mon compte</h1>
</div>
<div class="grid two">
    <section class="panel">
        <h2>Mes informations</h2>
        <?= $this->Form->create($user) ?>
        <div class="form-grid two">
            <?= $this->Form->control('first_name', ['label' => 'Prénom', 'autocomplete' => 'given-name']) ?>
            <?= $this->Form->control('last_name', ['label' => 'Nom', 'autocomplete' => 'family-name']) ?>
        </div>
        <?= $this->Form->control('email', ['type' => 'email', 'label' => 'Email', 'autocomplete' => 'email']) ?>
        <div class="button-row">
            <?= $this->Form->button('Enregistrer', ['class' => 'button']) ?>
        </div>
        <?= $this->Form->end() ?>
    </section>
    <div>
        <section class="panel">
            <h2>Mot de passe</h2>
            <p class="muted">Le mot de passe actuel vous sera demandé.</p>
            <a class="button secondary" href="<?= $this->Url->build(['action' => 'password']) ?>">Changer mon mot de passe</a>
        </section>
        <section class="panel">
            <h2>Supprimer mon compte</h2>
            <p class="muted">Votre compte et toutes vos nuits seront effacés définitivement.</p>
            <a class="button danger secondary" href="<?= $this->Url->build(['action' => 'delete']) ?>">Supprimer mon compte</a>
        </section>
    </div>
</div>
