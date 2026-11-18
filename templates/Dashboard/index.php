<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Night|null $lastNight
 * @var bool $hasToday
 * @var \App\Service\WeekSummary $week
 * @var list<\App\Model\Entity\Night> $recent
 * @var int $total
 * @var \App\Model\Entity\User $currentUser
 */
use App\Service\SleepCycles;

$this->assign('title', 'Tableau de bord');
$cyclesByDay = [];
foreach ($week->days as $date => $night) {
    $cyclesByDay[(new \Cake\I18n\Date($date))->i18nFormat('EEE')] = $night?->cyclesValue();
}
?>
<div class="page-head">
    <div>
        <h1>Bonjour <?= h($currentUser->first_name) ?></h1>
        <p class="muted"><?= h(ucfirst($this->Sleep->day(\Cake\I18n\Date::today(), true))) ?></p>
    </div>
    <?php if (!$hasToday) : ?>
        <a class="button" href="<?= $this->Url->build(['controller' => 'Nights', 'action' => 'add']) ?>">Noter ma nuit</a>
    <?php endif; ?>
</div>

<?php if ($lastNight === null) : ?>
    <section class="panel night-hero">
        <h2>Commencez par votre dernière nuit</h2>
        <p class="muted">Indiquez l'heure du coucher et du lever : l'application calcule vos cycles de 90 minutes et vous dit si vous vous êtes réveillé en fin de cycle.</p>
        <a class="button" href="<?= $this->Url->build(['controller' => 'Nights', 'action' => 'add']) ?>">Noter ma nuit</a>
    </section>
<?php else : ?>
    <div class="grid two">
        <section class="panel night-hero" aria-labelledby="derniere-nuit">
            <h2 id="derniere-nuit"><?= $hasToday ? 'Votre nuit' : 'Dernière nuit notée' ?></h2>
            <p class="muted"><?= h(ucfirst($this->Sleep->day($lastNight->sleep_date))) ?></p>
            <p class="cycles-big"><?= h($this->Sleep->cycles($lastNight->cycles)) ?> <small>cycles</small></p>
            <ul class="facts">
                <li><?= h($this->Sleep->time($lastNight->bedtime)) ?> → <?= h($this->Sleep->time($lastNight->wake_time)) ?></li>
                <li><?= h($this->Sleep->duration($lastNight->duration)) ?> de sommeil</li>
                <?php if ($lastNight->morning_score !== null) : ?>
                    <li>Forme : <?= h($lastNight->morning_score) ?>/10</li>
                <?php endif; ?>
            </ul>
            <?= $this->element('night_chips', ['night' => $lastNight]) ?>
            <p class="small"><a href="<?= $this->Url->build(['controller' => 'Nights', 'action' => 'edit', $lastNight->id]) ?>">Modifier cette nuit</a></p>
        </section>

        <section class="panel" aria-labelledby="semaine">
            <h2 id="semaine">Cette semaine</h2>
            <div class="goal">
                <div class="goal-head">
                    <span>Cycles</span>
                    <span><?= h($this->Sleep->cycles($week->totalCycles)) ?> / <?= SleepCycles::WEEK_GOAL ?></span>
                </div>
                <progress class="meter<?= $week->reachesCycleGoal() ? ' is-done' : '' ?>" max="<?= SleepCycles::WEEK_GOAL ?>" value="<?= min(SleepCycles::WEEK_GOAL, $week->totalCycles) ?>"></progress>
            </div>
            <div class="goal">
                <div class="goal-head">
                    <span>Jours d'affilée à <?= SleepCycles::NIGHT_GOAL ?> cycles</span>
                    <span><?= $week->longestStreak ?> / <?= SleepCycles::STREAK_GOAL ?></span>
                </div>
                <progress class="meter<?= $week->reachesStreakGoal() ? ' is-done' : '' ?>" max="<?= SleepCycles::STREAK_GOAL ?>" value="<?= min(SleepCycles::STREAK_GOAL, $week->longestStreak) ?>"></progress>
            </div>
            <?= $this->element('chart_bars', [
                'title' => 'Cycles par nuit cette semaine',
                'width' => 360,
                'height' => 190,
                'values' => $cyclesByDay,
                'max' => 8.0,
                'goal' => (float)SleepCycles::NIGHT_GOAL,
                'goalLabel' => SleepCycles::NIGHT_GOAL . ' cycles',
                'classFor' => fn($v) => SleepCycles::isEnough((float)$v) ? 'is-good' : 'is-short',
                'format' => fn($v) => $this->Sleep->cycles($v),
            ]) ?>
            <p class="small"><a href="<?= $this->Url->build(['controller' => 'Nights', 'action' => 'week']) ?>">Voir le bilan de la semaine</a></p>
        </section>
    </div>

    <section class="panel">
        <h2>Dernières nuits</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Date du réveil</th>
                        <th scope="col" class="num">Cycles</th>
                        <th scope="col" class="num">Durée</th>
                        <th scope="col">Horaires</th>
                        <th scope="col" class="num">Forme</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $night) : ?>
                        <tr>
                            <th scope="row"><a href="<?= $this->Url->build(['controller' => 'Nights', 'action' => 'edit', $night->id]) ?>"><?= h($this->Sleep->day($night->sleep_date)) ?></a></th>
                            <td class="num"><span class="chip <?= $this->Sleep->statusClass($night->cycles) ?>"><?= h($this->Sleep->cycles($night->cycles)) ?></span></td>
                            <td class="num"><?= h($this->Sleep->duration($night->duration)) ?></td>
                            <td class="nowrap"><?= h($this->Sleep->time($night->bedtime)) ?> → <?= h($this->Sleep->time($night->wake_time)) ?></td>
                            <td class="num"><?= $night->morning_score === null ? '—' : h($night->morning_score) . '/10' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total > count($recent)) : ?>
            <p class="small"><a href="<?= $this->Url->build(['controller' => 'Nights', 'action' => 'index']) ?>">Voir les <?= $total ?> nuits</a></p>
        <?php endif; ?>
    </section>
<?php endif; ?>
