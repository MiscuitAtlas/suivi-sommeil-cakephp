<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User|null $currentUser
 * @var iterable<\App\Model\Entity\Menu>|null $navMenus
 */
$currentUser ??= null;
$navMenus ??= null;
$here = $this->request->getPath();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h(trim($this->fetch('title') . ' – Suivi du sommeil', ' –')) ?></title>
    <meta name="description" content="Notez vos nuits, comptez vos cycles de sommeil et suivez votre semaine.">
    <link rel="icon" href="<?= $this->Url->build('/img/favicon.svg') ?>" type="image/svg+xml">
    <?= $this->Html->css('app') ?>
    <?= $this->Html->script('app', ['defer' => true]) ?>
</head>
<body>
    <a class="skip-link" href="#contenu">Aller au contenu</a>
    <header class="site-header">
        <div class="site-header-inner">
            <a class="brand" href="<?= $this->Url->build('/') ?>">
                <svg viewBox="0 0 32 32" aria-hidden="true"><path d="M21.5 4.5A12 12 0 1 0 27.5 21 9.5 9.5 0 0 1 21.5 4.5Z" fill="#f2c14e"/></svg>
                Sommeil
            </a>
            <?php if ($currentUser) : ?>
                <div class="user-menu">
                    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'account']) ?>"><?= h($currentUser->first_name) ?></a>
                    <?= $this->Form->postButton('Se déconnecter', ['controller' => 'Users', 'action' => 'logout'], ['class' => 'link-button']) ?>
                </div>
            <?php else : ?>
                <div class="user-menu">
                    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>">Connexion</a>
                    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'register']) ?>">Créer un compte</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <div class="layout<?= $navMenus !== null ? ' with-nav' : '' ?>">
        <?php if ($navMenus !== null) : ?>
            <nav class="main-nav" aria-label="Navigation principale">
                <ul>
                    <?php foreach ($navMenus as $menu) : ?>
                        <li>
                            <a href="<?= $this->Url->build($menu->url) ?>"<?= $here === $menu->url ? ' aria-current="page"' : '' ?>><?= h($menu->label) ?></a>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($currentUser?->isAdmin()) : ?>
                        <li class="nav-admin">
                            <a href="<?= $this->Url->build(['controller' => 'Menus', 'action' => 'index']) ?>"<?= str_starts_with($here, '/admin/menus') ? ' aria-current="page"' : '' ?>>Gérer le menu</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <main id="contenu" tabindex="-1">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </main>
    </div>

    <footer class="site-footer">
        Un cycle de sommeil dure environ 90 minutes. Cette application aide à repérer ses habitudes : elle ne remplace pas un avis médical.
    </footer>
</body>
</html>
