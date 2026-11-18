<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Menu $menu
 */
$this->assign('title', 'Modifier un lien');
?>
<div class="narrow">
    <h1>Modifier le lien</h1>
    <section class="panel"><?= $this->element('menu_form', ['menu' => $menu, 'submit' => 'Enregistrer']) ?></section>
</div>
