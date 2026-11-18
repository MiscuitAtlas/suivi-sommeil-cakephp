<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 * @var int $code
 */
use Cake\Core\Configure;

$this->setLayout('error');
$titles = [403 => 'Accès refusé', 404 => 'Page introuvable'];
$title = $titles[$code] ?? 'Requête impossible';
$this->assign('title', $title);
if (Configure::read('debug')) {
    $this->setLayout('dev_error');
    $this->assign('templateName', 'error400.php');
}
?>
<div class="error-page">
    <h1><?= h($title) ?></h1>
    <p class="muted">
        <?= $code === 403
            ? 'Cette page ne vous est pas accessible.'
            : ($code === 404 ? 'Cette page n\'existe pas ou a été supprimée.' : 'La requête n\'a pas pu être traitée.') ?>
    </p>
    <a class="button" href="<?= $this->Url->build('/') ?>">Retour à l'accueil</a>
</div>
