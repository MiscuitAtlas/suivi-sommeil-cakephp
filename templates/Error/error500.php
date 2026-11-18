<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 */
use Cake\Core\Configure;

$this->setLayout('error');
$this->assign('title', 'Erreur du serveur');
if (Configure::read('debug')) {
    $this->setLayout('dev_error');
    $this->assign('templateName', 'error500.php');
}
?>
<div class="error-page">
    <h1>Une erreur est survenue</h1>
    <p class="muted">Le problème a été enregistré. Réessayez dans quelques instants.</p>
    <a class="button" href="<?= $this->Url->build('/') ?>">Retour à l'accueil</a>
</div>
