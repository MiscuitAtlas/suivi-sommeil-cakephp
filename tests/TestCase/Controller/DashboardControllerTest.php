<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

class DashboardControllerTest extends AppTestCase
{
    public function testDashboardShowsMyLastNight(): void
    {
        $this->loginAs(1);
        $this->get('/tableau-de-bord');
        $this->assertResponseOk();
        $this->assertResponseContains('Bonjour Alice');
        $this->assertResponseContains('Dernière nuit notée');
        $this->assertResponseContains('Noter ma nuit');
        // Navigation chargée depuis la base, sans lien d'administration.
        $this->assertResponseContains('Bilan de la semaine');
        $this->assertResponseNotContains('Gérer le menu');
    }

    public function testDashboardForANewUser(): void
    {
        $this->loginAs(3);
        $this->get('/tableau-de-bord');
        $this->assertResponseOk();
        $this->assertResponseContains('Commencez par votre dernière nuit');
        $this->assertResponseContains('Gérer le menu');
    }

    public function testLoggedInHomeRedirectsToTheDashboard(): void
    {
        $this->loginAs(1);
        $this->get('/');
        $this->assertRedirect('/tableau-de-bord');
    }
}
