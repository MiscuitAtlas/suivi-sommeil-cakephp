<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

class MenusControllerTest extends AppTestCase
{
    public function testOnlyAdminsCanManageTheMenu(): void
    {
        $this->loginAs(1);
        foreach (['/admin/menus', '/admin/menus/ajouter', '/admin/menus/1/modifier'] as $url) {
            $this->get($url);
            $this->assertResponseCode(403, $url);
        }
        $this->post('/admin/menus/1/supprimer');
        $this->assertResponseCode(403);
        $this->post('/admin/menus/2/monter');
        $this->assertResponseCode(403);
    }

    public function testAdminCanAddALink(): void
    {
        $this->loginAs(3);
        $this->post('/admin/menus/ajouter', ['label' => 'Mon compte', 'url' => '/compte']);
        $this->assertRedirect('/admin/menus');
        $menu = $this->getTableLocator()->get('Menus')->find()->where(['url' => '/compte'])->firstOrFail();
        $this->assertSame(4, $menu->position);
    }

    public function testOnlyInternalLinksAreAccepted(): void
    {
        $this->loginAs(3);
        foreach (['javascript:alert(1)', 'https://exemple.com', '//exemple.com', 'compte'] as $url) {
            $this->post('/admin/menus/ajouter', ['label' => 'Piège', 'url' => $url]);
            $this->assertResponseOk($url);
            $this->assertResponseContains('Le lien doit être un chemin du site', $url);
        }
        $this->assertSame(3, $this->getTableLocator()->get('Menus')->find()->count());
    }

    public function testMoveUpAndDown(): void
    {
        $this->loginAs(3);
        $this->post('/admin/menus/3/monter');
        $this->assertRedirectContains('/admin/menus');
        $order = $this->getTableLocator()->get('Menus')->find()->orderBy(['position' => 'ASC'])->all()->extract('id')->toList();
        $this->assertSame([1, 3, 2], $order);

        $this->post('/admin/menus/1/descendre');
        $order = $this->getTableLocator()->get('Menus')->find()->orderBy(['position' => 'ASC'])->all()->extract('id')->toList();
        $this->assertSame([3, 1, 2], $order);

        // Déjà en haut : rien ne change.
        $this->post('/admin/menus/3/monter');
        $order = $this->getTableLocator()->get('Menus')->find()->orderBy(['position' => 'ASC'])->all()->extract('id')->toList();
        $this->assertSame([3, 1, 2], $order);
    }

    public function testEditAndDelete(): void
    {
        $this->loginAs(3);
        $this->put('/admin/menus/2/modifier', ['label' => 'Mes nuits', 'url' => '/nuits']);
        $this->assertRedirect('/admin/menus');
        $this->assertSame('Mes nuits', $this->getTableLocator()->get('Menus')->get(2)->label);

        $this->post('/admin/menus/2/supprimer');
        $this->assertRedirect('/admin/menus');
        $this->assertSame(2, $this->getTableLocator()->get('Menus')->find()->count());
    }
}
