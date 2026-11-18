<?php
/**
 * @var \App\View\AppView $this
 * @var bool $demoEnabled
 */
$this->assign('title', 'Connexion');
?>
<div class="narrow">
    <h1>Connexion</h1>
    <section class="panel">
        <?= $this->Form->create(null) ?>
        <?= $this->Form->control('email', ['type' => 'email', 'label' => 'Email', 'autocomplete' => 'email', 'required' => true]) ?>
        <?= $this->Form->control('password', ['type' => 'password', 'label' => 'Mot de passe', 'autocomplete' => 'current-password', 'required' => true]) ?>
        <div class="button-row">
            <?= $this->Form->button('Se connecter', ['class' => 'button']) ?>
        </div>
        <?= $this->Form->end() ?>
    </section>
    <p class="muted">Pas encore de compte ? <a href="<?= $this->Url->build(['action' => 'register']) ?>">Créer un compte</a></p>
    <?php if ($demoEnabled) : ?>
        <section class="panel">
            <h2>Démonstration</h2>
            <p>Un compte avec trois semaines de nuits déjà saisies, rien qu'à vous, supprimé au bout de deux heures.</p>
            <?= $this->Form->postButton('Essayer la démo', ['controller' => 'Demo', 'action' => 'start'], ['class' => 'button']) ?>
        </section>
    <?php endif; ?>
</div>
