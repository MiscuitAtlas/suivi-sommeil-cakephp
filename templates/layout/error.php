<?php
/** @var \App\View\AppView $this */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($this->fetch('title')) ?> – Suivi du sommeil</title>
    <link rel="icon" href="<?= $this->Url->build('/img/favicon.svg') ?>" type="image/svg+xml">
    <?= $this->Html->css('app') ?>
</head>
<body>
    <main id="contenu">
        <?= $this->fetch('content') ?>
    </main>
</body>
</html>
