<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Night> $nights
 */
$this->assign('title', 'Historique');
$this->Paginator->setTemplates([
    'sort' => '<a href="{{url}}">{{text}}</a>',
    'sortAsc' => '<a class="asc" href="{{url}}" aria-label="{{text}}, tri croissant">{{text}} ↑</a>',
    'sortDesc' => '<a class="desc" href="{{url}}" aria-label="{{text}}, tri décroissant">{{text}} ↓</a>',
]);
?>
<div class="page-head">
    <div>
        <h1>Historique</h1>
        <?php $count = (int)$this->Paginator->counter('{{count}}'); ?>
        <p class="muted"><?= $count ?> <?= $count > 1 ? 'nuits enregistrées' : 'nuit enregistrée' ?></p>
    </div>
    <a class="button" href="<?= $this->Url->build(['action' => 'add']) ?>">Ajouter une nuit</a>
</div>

<?php if ($this->Paginator->param('count') === 0) : ?>
    <section class="panel">
        <h2>Aucune nuit pour l'instant</h2>
        <p>Notez votre heure de coucher et de lever : l'application calcule vos cycles de sommeil.</p>
        <a class="button" href="<?= $this->Url->build(['action' => 'add']) ?>">Enregistrer ma première nuit</a>
    </section>
<?php else : ?>
    <section class="panel">
        <div class="table-wrap">
            <table>
                <caption class="sr-only">Vos nuits, de la plus récente à la plus ancienne</caption>
                <thead>
                    <tr>
                        <th scope="col"><?= $this->Paginator->sort('sleep_date', 'Date du réveil') ?></th>
                        <th scope="col">Coucher</th>
                        <th scope="col">Lever</th>
                        <th scope="col" class="num"><?= $this->Paginator->sort('duration', 'Durée') ?></th>
                        <th scope="col" class="num"><?= $this->Paginator->sort('cycles', 'Cycles') ?></th>
                        <th scope="col" class="num"><?= $this->Paginator->sort('morning_score', 'Forme') ?></th>
                        <th scope="col">La veille</th>
                        <th scope="col"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nights as $night) : ?>
                        <tr>
                            <td><?= h($this->Sleep->day($night->sleep_date, true)) ?></td>
                            <td><?= h($this->Sleep->time($night->bedtime)) ?></td>
                            <td><?= h($this->Sleep->time($night->wake_time)) ?></td>
                            <td class="num"><?= h($this->Sleep->duration($night->duration)) ?></td>
                            <td class="num"><span class="chip <?= $this->Sleep->statusClass($night->cycles) ?>"><?= h($this->Sleep->cycles($night->cycles)) ?></span></td>
                            <td class="num"><?= $night->morning_score === null ? '—' : h($night->morning_score) . '/10' ?></td>
                            <td class="small">
                                <?= h(implode(', ', array_filter([
                                    $night->nap_afternoon ? 'sieste après-midi' : null,
                                    $night->nap_evening ? 'sieste soirée' : null,
                                    $night->sport ? 'sport' : null,
                                ])) ?: '—') ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="button small secondary" href="<?= $this->Url->build(['action' => 'edit', $night->id]) ?>">
                                        Modifier<span class="sr-only"> la nuit du <?= h($this->Sleep->day($night->sleep_date)) ?></span>
                                    </a>
                                    <?= $this->Form->postButton('Supprimer', ['action' => 'delete', $night->id], [
                                        'class' => 'button small danger secondary',
                                        'aria-label' => 'Supprimer la nuit du ' . $this->Sleep->day($night->sleep_date),
                                        'form' => ['data-confirm' => 'Supprimer définitivement la nuit du ' . $this->Sleep->day($night->sleep_date) . ' ?'],
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($this->Paginator->hasPage(2)) : ?>
            <nav aria-label="Pages de l'historique">
                <ul class="pagination">
                    <?= $this->Paginator->prev('← Plus récentes') ?>
                    <?= $this->Paginator->numbers() ?>
                    <?= $this->Paginator->next('Plus anciennes →') ?>
                </ul>
            </nav>
        <?php endif; ?>
    </section>
<?php endif; ?>
