<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Service\WeekSummary;
use Cake\I18n\Date;

class NightsControllerTest extends AppTestCase
{
    public function testIndexShowsOnlyMyNights(): void
    {
        $this->loginAs(1);
        $this->get('/nuits');
        $this->assertResponseOk();
        $this->assertResponseContains('2 nuits enregistrées');
        $this->assertResponseContains('/nuits/1/modifier');
        $this->assertResponseNotContains('/nuits/3/modifier');
    }

    public function testAddUsesTheLoggedInUser(): void
    {
        $this->loginAs(1);
        $this->post('/nuits/ajouter', [
            'sleep_date' => Date::today()->format('Y-m-d'),
            'bedtime' => '23:00',
            'wake_time' => '06:30',
            'morning_score' => '8',
            'user_id' => 2,
            'cycles' => 99,
        ]);
        $this->assertRedirect('/tableau-de-bord');
        $this->assertFlashMessage(sprintf('Nuit du %s enregistrée : 5 cycles.', Date::today()->i18nFormat('d MMMM')));
        $night = $this->getTableLocator()->get('Nights')->find()->orderByDesc('id')->firstOrFail();
        $this->assertSame(1, $night->user_id);
        $this->assertEquals(5.0, (float)$night->cycles);
    }

    public function testAddShowsErrors(): void
    {
        $this->loginAs(1);
        $this->post('/nuits/ajouter', ['sleep_date' => Date::today()->subDays(1)->format('Y-m-d'), 'bedtime' => '23:00', 'wake_time' => '06:30']);
        $this->assertResponseOk();
        $this->assertResponseContains('Une nuit est déjà enregistrée à cette date');
    }

    public function testAddPrefillsTheDateFromTheWeekPage(): void
    {
        $this->loginAs(1);
        $date = Date::today()->subDays(3)->format('Y-m-d');
        $this->get('/nuits/ajouter?date=' . $date);
        $this->assertResponseContains('value="' . $date . '"');
        // Le texte d'aide est relié au champ, et les heures se saisissent sans les secondes.
        $this->assertResponseContains('aria-describedby="sleep-date-help"');
        $this->assertResponseContains('<span class="help" id="sleep-date-help">');
        $this->assertResponseNotContains(' help="');
        $this->assertResponseContains('step="60"');

        $this->get('/nuits/ajouter?date=2026-02-31');
        $this->assertResponseContains('value="' . Date::today()->format('Y-m-d') . '"');
    }

    public function testAddPointsToTheNightAlreadyRecorded(): void
    {
        $this->loginAs(1);
        $this->get('/nuits/ajouter?date=' . Date::today()->subDays(1)->format('Y-m-d'));
        $this->assertResponseOk();
        $this->assertResponseContains('est déjà enregistrée');
        $this->assertResponseContains('/nuits/1/modifier');
        // Bruno a aussi une nuit à cette date : elle ne concerne pas Alice.
        $this->assertResponseNotContains('/nuits/3/modifier');

        $this->get('/nuits/ajouter?date=' . Date::today()->subDays(3)->format('Y-m-d'));
        $this->assertResponseNotContains('est déjà enregistrée');
    }

    public function testEditMyNight(): void
    {
        $this->loginAs(1);
        $this->get('/nuits/1/modifier');
        $this->assertResponseOk();
        $this->put('/nuits/1/modifier', ['wake_time' => '08:00', 'comment' => 'Grasse matinée']);
        $this->assertRedirect('/nuits');
        $night = $this->getTableLocator()->get('Nights')->get(1);
        $this->assertSame(540, $night->duration);
        $this->assertSame('Grasse matinée', $night->comment);
    }

    public function testCannotSeeOrChangeSomeoneElsesNight(): void
    {
        $this->loginAs(1);
        $this->get('/nuits/3/modifier');
        $this->assertResponseCode(403);

        $this->put('/nuits/3/modifier', ['comment' => 'piraté']);
        $this->assertResponseCode(403);

        $this->post('/nuits/3/supprimer');
        $this->assertResponseCode(403);

        $night = $this->getTableLocator()->get('Nights')->get(3);
        $this->assertNull($night->comment);
    }

    public function testDelete(): void
    {
        $this->loginAs(1);
        $this->get('/nuits/1/supprimer');
        $this->assertResponseCode(405);

        $this->post('/nuits/1/supprimer');
        $this->assertRedirect('/nuits');
        $this->assertSame(0, $this->getTableLocator()->get('Nights')->find()->where(['id' => 1])->count());
    }

    public function testUnknownNightGivesA404(): void
    {
        $this->loginAs(1);
        $this->get('/nuits/999/modifier');
        $this->assertResponseCode(404);
    }

    public function testWeekSummary(): void
    {
        $this->loginAs(1);
        $this->get('/semaine');
        $this->assertResponseOk();
        $this->assertResponseContains('Cette semaine');
        $this->assertResponseContains('Semaine précédente');
        $this->assertResponseNotContains('Semaine suivante');
    }

    public function testWeekNavigation(): void
    {
        $this->loginAs(1);
        $monday = WeekSummary::mondayOf(Date::today())->subDays(14);
        $this->get('/semaine?du=' . $monday->addDays(3)->format('Y-m-d'));
        $this->assertResponseOk();
        $this->assertResponseContains('Bilan de la semaine');
        $this->assertResponseContains('du=' . $monday->addDays(7)->format('Y-m-d'));
        $this->assertResponseContains('du=' . $monday->subDays(7)->format('Y-m-d'));
    }

    public function testWeekIgnoresAnInvalidDate(): void
    {
        $this->loginAs(1);
        $this->get('/semaine?du=n-importe-quoi');
        $this->assertResponseOk();
        $this->assertResponseContains('Cette semaine');
    }
}
