<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Collection\CollectionInterface<\App\Model\Entity\Menu> $menus
 * @var bool $canChange
 */
$this->assign('title', 'Menu de navigation');
$items = $menus->toList();
$last = count($items) - 1;
?>
<div class="page-head">
    <div>
        <h1>Menu de navigation</h1>
        <p class="muted">Les liens s'affichent dans cet ordre pour tous les utilisateurs.</p>
    </div>
    <?php if ($canChange) : ?>
        <a class="button" href="<?= $this->Url->build(['action' => 'add']) ?>">Ajouter un lien</a>
    <?php endif; ?>
</div>
<section class="panel">
    <?php if (!$canChange) : ?>
        <p class="notice">Sur la démonstration en ligne, le menu est partagé par tous les visiteurs : il reste en lecture seule.</p>
    <?php endif; ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th scope="col">Ordre</th>
                    <th scope="col">Intitulé</th>
                    <th scope="col">Lien</th>
                    <?php if ($canChange) : ?>
                        <th scope="col"><span class="sr-only">Actions</span></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $menu) : ?>
                    <tr id="menu-<?= $menu->id ?>">
                        <td><?= $i + 1 ?></td>
                        <th scope="row"><?= h($menu->label) ?></th>
                        <td><code><?= h($menu->url) ?></code></td>
                        <?php if ($canChange) : ?>
                        <td>
                            <div class="actions">
                                <?php if ($i > 0) : ?>
                                    <?= $this->Form->postButton('↑', ['action' => 'move', $menu->id, 'monter'], ['class' => 'button small secondary', 'aria-label' => 'Monter « ' . $menu->label . ' »']) ?>
                                <?php endif; ?>
                                <?php if ($i < $last) : ?>
                                    <?= $this->Form->postButton('↓', ['action' => 'move', $menu->id, 'descendre'], ['class' => 'button small secondary', 'aria-label' => 'Descendre « ' . $menu->label . ' »']) ?>
                                <?php endif; ?>
                                <a class="button small secondary" href="<?= $this->Url->build(['action' => 'edit', $menu->id]) ?>">Modifier<span class="sr-only"> « <?= h($menu->label) ?> »</span></a>
                                <?= $this->Form->postButton('Supprimer', ['action' => 'delete', $menu->id], [
                                    'class' => 'button small danger secondary',
                                    'aria-label' => 'Supprimer « ' . $menu->label . ' »',
                                    'form' => ['data-confirm' => 'Retirer « ' . $menu->label . ' » du menu ?'],
                                ]) ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
