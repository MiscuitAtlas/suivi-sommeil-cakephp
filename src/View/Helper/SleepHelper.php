<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Service\SleepCycles;
use Cake\I18n\Date;
use Cake\I18n\Time;
use Cake\View\Helper;

/**
 * Mise en forme des données de sommeil, en français.
 */
class SleepHelper extends Helper
{
    /** 5.2 → « 5,2 » */
    public function cycles(float|string|null $cycles): string
    {
        if ($cycles === null || $cycles === '') {
            return '—';
        }
        $value = round((float)$cycles, 1);

        return str_replace('.', ',', (string)($value == (int)$value ? (int)$value : $value));
    }

    /** 450 → « 7 h 30 » */
    public function duration(?int $minutes): string
    {
        return $minutes === null ? '—' : SleepCycles::formatDuration($minutes);
    }

    /** Heure « 22:30 » */
    public function time(?Time $time): string
    {
        return $time === null ? '—' : $time->format('H:i');
    }

    /** « mardi 18 novembre » ou, avec l'année, « mardi 18 novembre 2026 » */
    public function day(Date $date, bool $withYear = false): string
    {
        return $date->i18nFormat($withYear ? 'EEEE d MMMM yyyy' : 'EEEE d MMMM');
    }

    /** « lun. 17 » */
    public function shortDay(Date $date): string
    {
        return $date->i18nFormat('EEE d');
    }

    /** « du 17 au 23 novembre 2026 » */
    public function week(Date $monday): string
    {
        $sunday = $monday->addDays(6);
        $from = $monday->month === $sunday->month ? $monday->i18nFormat('d') : $monday->i18nFormat('d MMMM');

        return sprintf('du %s au %s', $from, $sunday->i18nFormat('d MMMM yyyy'));
    }

    /**
     * Classe CSS de l'état d'une nuit : suffisante (au moins cinq cycles) ou courte.
     */
    public function statusClass(float|string $cycles): string
    {
        return SleepCycles::isEnough((float)$cycles) ? 'is-good' : 'is-short';
    }
}
