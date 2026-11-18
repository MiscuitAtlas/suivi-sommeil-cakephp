<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Menu $menu
 */
$this->assign('title', 'Ajouter un lien');
?>
<div class="narrow">
    <h1>Ajouter un lien au menu</h1>
    <section class="panel"><?= $this->element('menu_form', ['menu' => $menu, 'submit' => 'Ajouter le lien']) ?></section>
</div>
