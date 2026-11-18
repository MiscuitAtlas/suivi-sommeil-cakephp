<?php
/**
 * @var \App\View\AppView $this
 * @var array<int, string> $example
 * @var bool $demoEnabled
 */
$this->assign('title', '');
?>
<section class="hero">
    <div>
        <h1>Dormez par cycles, pas par heures.</h1>
        <p>Un cycle de sommeil dure environ 90 minutes. Se réveiller à la fin d'un cycle plutôt qu'au milieu change la matinée. Notez vos nuits : l'application compte vos cycles et suit votre semaine.</p>
        <?php if ($demoEnabled) : ?>
            <div class="button-row">
                <?= $this->Form->postButton('Essayer la démo', ['controller' => 'Demo', 'action' => 'start'], ['class' => 'button']) ?>
                <a class="button secondary" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'register']) ?>">Créer un compte</a>
            </div>
            <p class="muted small">La démo crée un compte avec trois semaines de nuits déjà saisies, supprimé au bout de deux heures.</p>
        <?php else : ?>
            <div class="button-row">
                <a class="button" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'register']) ?>">Créer un compte</a>
                <a class="button secondary" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>">Se connecter</a>
            </div>
        <?php endif; ?>
    </div>
    <div class="timeline" role="img" aria-label="Couché à 23 heures : réveil en fin de cycle à <?= h($example[5]) ?> ou à <?= h($example[6]) ?>">
        <p class="muted small">Couché à 23:00, réveil en fin de cycle :</p>
        <?php foreach ($example as $cycles => $time) : ?>
            <div class="timeline-row<?= $cycles === 6 ? ' is-best' : '' ?>">
                <span><?= h($time) ?></span>
                <span class="track"><?php for ($i = 0; $i < $cycles; $i++) : ?><span></span><?php endfor; ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="grid three features">
    <section class="panel">
        <h2>Chaque nuit</h2>
        <p>Heures de coucher et de lever, forme au réveil, siestes et sport de la veille. La durée et les cycles se calculent seuls.</p>
    </section>
    <section class="panel">
        <h2>Chaque semaine</h2>
        <p>Deux objectifs : 42 cycles sur la semaine, et quatre jours d'affilée avec au moins cinq cycles.</p>
    </section>
    <section class="panel">
        <h2>Vos données</h2>
        <p>Chaque compte ne voit que ses propres nuits. Vous pouvez supprimer votre compte et toutes vos données à tout moment.</p>
    </section>
</div>
