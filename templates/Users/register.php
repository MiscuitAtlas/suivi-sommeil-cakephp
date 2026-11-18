<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Créer un compte');
?>
<div class="narrow">
    <h1>Créer un compte</h1>
    <section class="panel">
        <?= $this->Form->create($user) ?>
        <div class="form-grid two">
            <?= $this->Form->control('first_name', ['label' => 'Prénom', 'autocomplete' => 'given-name']) ?>
            <?= $this->Form->control('last_name', ['label' => 'Nom', 'autocomplete' => 'family-name']) ?>
        </div>
        <?= $this->Form->control('email', ['type' => 'email', 'label' => 'Email', 'autocomplete' => 'email']) ?>
        <?= $this->Form->control('password', [
            'type' => 'password',
            'label' => 'Mot de passe',
            'autocomplete' => 'new-password',
            'value' => '',
            'help' => '8 caractères au minimum.',
        ]) ?>
        <div class="button-row">
            <?= $this->Form->button('Créer mon compte', ['class' => 'button']) ?>
        </div>
        <?= $this->Form->end() ?>
    </section>
    <p class="muted">Déjà inscrit ? <a href="<?= $this->Url->build(['action' => 'login']) ?>">Se connecter</a></p>
</div>
