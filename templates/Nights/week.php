<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Service\WeekSummary $summary
 * @var \Cake\I18n\Date $previous
 * @var \Cake\I18n\Date|null $next
 * @var bool $isCurrent
 */
use App\Service\SleepCycles;

$this->assign('title', 'Bilan de la semaine');
$cycleGoalPercent = min(100, round($summary->totalCycles / SleepCycles::WEEK_GOAL * 100));
$streakPercent = min(100, round($summary->longestStreak / SleepCycles::STREAK_GOAL * 100));
$cyclesByDay = [];
$scoresByDay = [];
foreach ($summary->days as $date => $night) {
    $label = (new \Cake\I18n\Date($date))->i18nFormat('EEE d');
    $cyclesByDay[$label] = $night?->cyclesValue();
    $scoresByDay[$label] = $night?->morning_score;
}
?>
<div class="page-head">
    <div>
        <h1><?= $isCurrent ? 'Cette semaine' : 'Bilan de la semaine' ?></h1>
        <p class="muted">Semaine <?= h($this->Sleep->week($summary->monday)) ?></p>
    </div>
    <nav class="week-nav" aria-label="Changer de semaine">
        <a class="button secondary small" href="<?= $this->Url->build(['action' => 'week', '?' => ['du' => $previous->format('Y-m-d')]]) ?>">← Semaine précédente</a>
        <?php if ($next) : ?>
            <a class="button secondary small" href="<?= $this->Url->build(['action' => 'week', '?' => ['du' => $next->format('Y-m-d')]]) ?>">Semaine suivante →</a>
        <?php endif; ?>
    </nav>
</div>

<div class="grid two">
    <section class="panel" aria-labelledby="objectifs">
        <h2 id="objectifs">Objectifs</h2>
        <div class="goal">
            <div class="goal-head">
                <span><?= SleepCycles::WEEK_GOAL ?> cycles dans la semaine</span>
                <span><?= h($this->Sleep->cycles($summary->totalCycles)) ?> / <?= SleepCycles::WEEK_GOAL ?></span>
            </div>
            <progress class="meter<?= $summary->reachesCycleGoal() ? ' is-done' : '' ?>" max="<?= SleepCycles::WEEK_GOAL ?>" value="<?= min(SleepCycles::WEEK_GOAL, $summary->totalCycles) ?>"><?= $cycleGoalPercent ?> %</progress>
        </div>
        <div class="goal">
            <div class="goal-head">
                <span><?= SleepCycles::STREAK_GOAL ?> jours d'affilée à <?= SleepCycles::NIGHT_GOAL ?> cycles ou plus</span>
                <span><?= $summary->longestStreak ?> / <?= SleepCycles::STREAK_GOAL ?></span>
            </div>
            <progress class="meter<?= $summary->reachesStreakGoal() ? ' is-done' : '' ?>" max="<?= SleepCycles::STREAK_GOAL ?>" value="<?= min(SleepCycles::STREAK_GOAL, $summary->longestStreak) ?>"><?= $streakPercent ?> %</progress>
        </div>
    </section>
    <section class="panel" aria-labelledby="moyennes">
        <h2 id="moyennes">En moyenne</h2>
        <ul class="facts">
            <li>Nuits notées : <strong><?= $summary->nightCount ?> / 7</strong></li>
            <li>Durée : <strong><?= h($this->Sleep->duration($summary->averageDuration)) ?></strong></li>
            <li>Forme au réveil : <strong><?= $summary->averageScore === null ? '—' : h(str_replace('.', ',', (string)$summary->averageScore)) . ' / 10' ?></strong></li>
            <li>Jours avec sieste : <strong><?= $summary->napCount ?></strong></li>
            <li>Jours avec sport : <strong><?= $summary->sportCount ?></strong></li>
        </ul>
    </section>
</div>

<section class="panel">
    <h2>Cycles par nuit</h2>
    <?= $this->element('chart_bars', [
        'title' => 'Nombre de cycles par nuit, comparé à l\'objectif de ' . SleepCycles::NIGHT_GOAL . ' cycles',
        'values' => $cyclesByDay,
        'max' => max([8.0, ...array_values(array_map('floatval', array_filter($cyclesByDay, fn($v) => $v !== null)))]),
        'goal' => (float)SleepCycles::NIGHT_GOAL,
        'goalLabel' => SleepCycles::NIGHT_GOAL . ' cycles',
        'classFor' => fn($v) => SleepCycles::isEnough((float)$v) ? 'is-good' : 'is-short',
        'format' => fn($v) => $this->Sleep->cycles($v),
    ]) ?>
</section>

<section class="panel">
    <h2>Détail de la semaine</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th scope="col">Jour</th>
                    <th scope="col">Coucher</th>
                    <th scope="col">Lever</th>
                    <th scope="col" class="num">Durée</th>
                    <th scope="col" class="num">Cycles</th>
                    <th scope="col" class="num">Forme</th>
                    <th scope="col">Commentaire</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($summary->days as $date => $night) : ?>
                    <?php $day = new \Cake\I18n\Date($date); ?>
                    <?php if ($night === null) : ?>
                        <tr class="is-empty">
                            <th scope="row"><?= h($this->Sleep->day($day)) ?></th>
                            <td colspan="6">
                                <?php if ($day->lessThanOrEquals(\Cake\I18n\Date::today())) : ?>
                                    Pas de nuit notée. <a href="<?= $this->Url->build(['action' => 'add', '?' => ['date' => $date]]) ?>">Ajouter<span class="sr-only"> la nuit du <?= h($this->Sleep->day($day)) ?></span></a>
                                <?php else : ?>
                                    À venir
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else : ?>
                        <tr>
                            <th scope="row"><a href="<?= $this->Url->build(['action' => 'edit', $night->id]) ?>"><?= h($this->Sleep->day($day)) ?></a></th>
                            <td><?= h($this->Sleep->time($night->bedtime)) ?></td>
                            <td><?= h($this->Sleep->time($night->wake_time)) ?></td>
                            <td class="num"><?= h($this->Sleep->duration($night->duration)) ?></td>
                            <td class="num"><span class="chip <?= $this->Sleep->statusClass($night->cycles) ?>"><?= h($this->Sleep->cycles($night->cycles)) ?></span></td>
                            <td class="num"><?= $night->morning_score === null ? '—' : h($night->morning_score) . '/10' ?></td>
                            <td class="small"><?= h($night->comment ?: '—') ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
