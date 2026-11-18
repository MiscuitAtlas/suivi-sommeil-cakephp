<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use App\Test\Fixture\UsersFixture;
use Cake\TestSuite\TestCase;

class UsersTableTest extends TestCase
{
    protected array $fixtures = ['app.Users', 'app.Nights', 'app.Menus'];

    private UsersTable $Users;

    protected function setUp(): void
    {
        parent::setUp();
        $this->Users = $this->getTableLocator()->get('Users');
    }

    public function testPasswordIsHashedAndHidden(): void
    {
        $user = $this->Users->newEntity([
            'first_name' => 'Chloé',
            'last_name' => 'Roux',
            'email' => '  Chloe@Exemple.FR ',
            'password' => 'un-mot-de-passe',
        ]);
        $this->Users->saveOrFail($user);
        $this->assertSame('chloe@exemple.fr', $user->email);
        $this->assertNotSame('un-mot-de-passe', $user->password);
        $this->assertTrue(password_verify('un-mot-de-passe', $user->password));
        $this->assertArrayNotHasKey('password', $user->toArray());
    }

    public function testRoleCannotBeSetFromAForm(): void
    {
        $user = $this->Users->newEntity([
            'first_name' => 'Chloé',
            'last_name' => 'Roux',
            'email' => 'chloe@exemple.fr',
            'password' => 'un-mot-de-passe',
            'role' => 'admin',
        ]);
        $this->Users->saveOrFail($user);
        $this->assertSame('user', $this->Users->get($user->id)->role);
    }

    public function testValidation(): void
    {
        $user = $this->Users->newEntity(['first_name' => '', 'last_name' => 'X', 'email' => 'pas-un-email', 'password' => 'court']);
        $this->assertNotEmpty($user->getError('first_name'));
        $this->assertNotEmpty($user->getError('email'));
        $this->assertNotEmpty($user->getError('password'));
    }

    public function testEmailIsUnique(): void
    {
        $user = $this->Users->newEntity(['first_name' => 'A', 'last_name' => 'B', 'email' => 'ALICE@exemple.fr', 'password' => 'un-mot-de-passe']);
        $this->assertFalse($this->Users->save($user));
        $this->assertNotEmpty($user->getError('email'));
    }

    public function testCheckPassword(): void
    {
        $user = $this->Users->get(1);
        $this->assertTrue($user->checkPassword(UsersFixture::PASSWORD));
        $this->assertFalse($user->checkPassword('autre chose'));
    }
}
