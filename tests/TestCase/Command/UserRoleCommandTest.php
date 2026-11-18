<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

class UserRoleCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    protected array $fixtures = ['app.Users', 'app.Nights', 'app.Menus'];

    public function testPromote(): void
    {
        $this->exec('user_role ALICE@exemple.fr admin');
        $this->assertExitSuccess();
        $this->assertOutputContains('alice@exemple.fr a maintenant le rôle « admin »');
        $this->assertSame('admin', $this->getTableLocator()->get('Users')->get(1)->role);
    }

    public function testUnknownEmail(): void
    {
        $this->exec('user_role personne@exemple.fr admin');
        $this->assertExitError();
        $this->assertErrorContains('Aucun compte');
    }

    public function testInvalidRole(): void
    {
        $this->exec('user_role alice@exemple.fr superadmin');
        $this->assertExitError();
        $this->assertSame('user', $this->getTableLocator()->get('Users')->get(1)->role);
    }
}
