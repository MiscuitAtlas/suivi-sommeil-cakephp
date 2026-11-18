<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Base des tests de contrôleurs : jeton CSRF actif et connexion simulée.
 */
abstract class AppTestCase extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = ['app.Users', 'app.Nights', 'app.Menus'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->enableCsrfToken();
        $this->enableRetainFlashMessages();
    }

    protected function loginAs(int $id): void
    {
        $user = $this->getTableLocator()->get('Users')->get($id);
        $this->session(['Auth' => $user]);
    }
}
