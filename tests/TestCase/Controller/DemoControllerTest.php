<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\DemoController;
use App\Service\DemoNights;
use Cake\Core\Configure;
use Cake\I18n\DateTime;

class DemoControllerTest extends AppTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Configure::write('Demo.enabled', true);
    }

    protected function tearDown(): void
    {
        Configure::write('Demo.enabled', false);
        parent::tearDown();
    }

    public function testTheDemoDoesNotExistOutsideTheOnlineDemo(): void
    {
        Configure::write('Demo.enabled', false);
        $this->post('/demo');
        $this->assertResponseCode(404);

        $this->get('/');
        $this->assertResponseNotContains('Essayer la démo');
    }

    public function testEachVisitorGetsTheirOwnAccount(): void
    {
        $this->get('/');
        $this->assertResponseContains('Essayer la démo');

        $this->post('/demo');
        $this->assertRedirect('/tableau-de-bord');
        $this->assertFlashMessageAt(0, sprintf('Compte de démonstration créé avec trois semaines de nuits. Il sera supprimé dans deux heures. Identifiants : %s / %s.', $this->_requestSession->read('Auth.email'), DemoNights::PASSWORD));

        $email = (string)$this->_requestSession->read('Auth.email');
        $this->assertStringEndsWith('@' . DemoController::EMAIL_DOMAIN, $email);
        $users = $this->getTableLocator()->get('Users');
        $user = $users->find()->where(['email' => $email])->firstOrFail();
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->checkPassword(DemoNights::PASSWORD));
        $this->assertSame(19, $users->Nights->find()->where(['user_id' => $user->id])->count());

        // Un deuxième visiteur reçoit un autre compte.
        $this->post('/demo');
        $this->assertNotSame($email, $this->_requestSession->read('Auth.email'));
    }

    public function testTheDemoOnlyAcceptsPost(): void
    {
        $this->get('/demo');
        $this->assertResponseCode(405);
    }

    public function testExpiredDemoAccountsAreRemoved(): void
    {
        $users = $this->getTableLocator()->get('Users');
        $old = $users->newEntity([
            'first_name' => 'Ancien',
            'last_name' => 'Visiteur',
            'email' => 'visiteur-ancien@' . DemoController::EMAIL_DOMAIN,
            'password' => DemoNights::PASSWORD,
        ]);
        $users->saveOrFail($old);
        $old->created = DateTime::now()->modify('-3 hours');
        $users->saveOrFail($old);
        $night = $users->Nights->newEntity(['sleep_date' => '2026-01-10', 'bedtime' => '23:00', 'wake_time' => '06:30']);
        $night->user_id = $old->id;
        $users->Nights->saveOrFail($night);

        $this->post('/demo');

        $this->assertFalse($users->exists(['id' => $old->id]));
        $this->assertFalse($users->Nights->exists(['user_id' => $old->id]));
        // Les vrais comptes ne sont jamais touchés.
        $this->assertSame(3, $users->find()->where(['email NOT LIKE' => '%@' . DemoController::EMAIL_DOMAIN])->count());
    }

    public function testTheNumberOfDemoAccountsIsCapped(): void
    {
        Configure::write('Demo.maxAccounts', 2);
        for ($i = 0; $i < 4; $i++) {
            $this->post('/demo');
        }
        Configure::write('Demo.maxAccounts', 200);

        $count = $this->getTableLocator()->get('Users')->find()
            ->where(['email LIKE' => '%@' . DemoController::EMAIL_DOMAIN])
            ->count();
        $this->assertSame(2, $count);
    }

    public function testTheMenuIsReadOnlyDuringTheDemo(): void
    {
        $this->loginAs(3);
        $this->get('/admin/menus');
        $this->assertResponseOk();
        $this->assertResponseContains('lecture seule');
        $this->assertResponseNotContains('Ajouter un lien');
        $this->assertResponseNotContains('/admin/menus/1/modifier');

        $this->post('/admin/menus/ajouter', ['label' => 'Piège', 'url' => '/piege']);
        $this->assertResponseCode(403);
        $this->post('/admin/menus/2/monter');
        $this->assertResponseCode(403);
        $this->post('/admin/menus/1/supprimer');
        $this->assertResponseCode(403);
    }
}
