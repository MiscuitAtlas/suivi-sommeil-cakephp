<?php
/**
 * État d'une nuit sous forme de pastilles.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Night $night
 */
?>
<ul class="chips">
    <li class="chip <?= $night->isEnough() ? 'is-good' : 'is-short' ?>">
        <?= $night->isEnough() ? 'Nuit suffisante' : 'Nuit courte' ?>
    </li>
    <?php if ($night->endsOnCycle()) : ?>
        <li class="chip is-good">Réveil en fin de cycle</li>
    <?php else : ?>
        <li class="chip">Réveil en milieu de cycle</li>
    <?php endif; ?>
</ul>
