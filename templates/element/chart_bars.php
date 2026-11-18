<?php
/**
 * Diagramme en barres dessiné en SVG, sans bibliothèque JavaScript.
 *
 * @var \App\View\AppView $this
 * @var string $title Description du graphique pour les lecteurs d'écran.
 * @var array<string, float|int|null> $values Libellé => valeur (null : pas de donnée).
 * @var float $max Valeur maximale de l'axe.
 * @var float|null $goal Ligne d'objectif, ou null.
 * @var string $goalLabel
 * @var callable|null $classFor Classe CSS d'une barre selon sa valeur.
 * @var callable|null $format Mise en forme des valeurs affichées.
 * @var string|null $caption
 * @var int|null $width Largeur du dessin : plus elle est petite, plus le texte paraît grand.
 * @var int|null $height
 */
$goal ??= null;
$goalLabel ??= '';
$classFor ??= null;
$format ??= fn($v) => (string)$v;
$caption ??= null;

$width ??= 640;
$height ??= 220;
$top = 22;
$bottom = 30;
$left = 8;
$plot = $height - $top - $bottom;
$count = max(1, count($values));
$slot = ($width - $left * 2) / $count;
$barWidth = min(56, $slot * 0.62);
$y = fn(float $v) => $top + $plot - ($v / $max) * $plot;
?>
<figure class="chart">
    <svg viewBox="0 0 <?= $width ?> <?= $height ?>" role="img" aria-label="<?= h($title) ?>">
        <line class="axis" x1="0" x2="<?= $width ?>" y1="<?= $top + $plot ?>" y2="<?= $top + $plot ?>"/>
        <?php $i = 0; foreach ($values as $label => $value) : ?>
            <?php $cx = $left + $slot * $i + $slot / 2; ?>
            <?php if ($value !== null) : ?>
                <?php $barTop = $y(min((float)$value, $max)); ?>
                <rect class="bar <?= $classFor ? h($classFor($value)) : '' ?>" x="<?= round($cx - $barWidth / 2, 1) ?>" y="<?= round($barTop, 1) ?>"
                      width="<?= round($barWidth, 1) ?>" height="<?= round($top + $plot - $barTop, 1) ?>" rx="4"/>
                <text class="value" x="<?= round($cx, 1) ?>" y="<?= round($barTop - 6, 1) ?>" text-anchor="middle"><?= h($format($value)) ?></text>
            <?php else : ?>
                <text x="<?= round($cx, 1) ?>" y="<?= $top + $plot - 6 ?>" text-anchor="middle">—</text>
            <?php endif; ?>
            <text x="<?= round($cx, 1) ?>" y="<?= $height - 10 ?>" text-anchor="middle"><?= h($label) ?></text>
            <?php $i++; endforeach; ?>
        <?php if ($goal !== null) : ?>
            <line class="goal-line" x1="0" x2="<?= $width ?>" y1="<?= round($y($goal), 1) ?>" y2="<?= round($y($goal), 1) ?>"/>
            <text x="<?= $width - 4 ?>" y="<?= round($y($goal) - 6, 1) ?>" text-anchor="end"><?= h($goalLabel) ?></text>
        <?php endif; ?>
    </svg>
    <?php if ($caption) : ?>
        <figcaption><?= h($caption) ?></figcaption>
    <?php endif; ?>
</figure>
