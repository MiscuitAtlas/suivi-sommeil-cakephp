<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\Fixture\UsersFixture;

class UsersControllerTest extends AppTestCase
{
    public function testPagesRequireLogin(): void
    {
        foreach (['/tableau-de-bord', '/nuits', '/nuits/ajouter', '/semaine', '/compte', '/admin/menus'] as $url) {
            $this->get($url);
            $this->assertRedirectContains('/connexion?redirect=', $url);
        }
    }

    public function testHomeIsPublic(): void
    {
        $this->get('/');
        $this->assertResponseOk();
        $this->assertResponseContains('Dormez par cycles');
        $this->assertHeader('X-Frame-Options', 'DENY');
        $this->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertFalse($this->_response->hasHeader('Strict-Transport-Security'));
    }

    public function testHttpsAddsStrictTransportSecurityAndSecureCookies(): void
    {
        // Le serveur web signale le HTTPS à PHP par la variable HTTPS.
        $_SERVER['HTTPS'] = 'on';
        $this->configRequest(['environment' => ['HTTPS' => 'on']]);
        try {
            $this->get('/connexion');
        } finally {
            unset($_SERVER['HTTPS']);
        }
        $this->assertResponseOk();
        $this->assertHeader('Strict-Transport-Security', 'max-age=31536000');
        $this->assertTrue($this->_response->getCookie('csrfToken')['secure'] ?? false);
    }

    public function testLogin(): void
    {
        $this->post('/connexion', ['email' => 'alice@exemple.fr', 'password' => UsersFixture::PASSWORD]);
        $this->assertRedirect('/tableau-de-bord');
        $this->assertSession(1, 'Auth.id');
    }

    public function testLoginRedirectsToTheRequestedPage(): void
    {
        $this->post('/connexion?redirect=%2Fsemaine', ['email' => 'alice@exemple.fr', 'password' => UsersFixture::PASSWORD]);
        $this->assertRedirect('/semaine');
    }

    public function testLoginIgnoresAnExternalRedirect(): void
    {
        $this->post('/connexion?redirect=https%3A%2F%2Fexemple.com', ['email' => 'alice@exemple.fr', 'password' => UsersFixture::PASSWORD]);
        $this->assertRedirect('/tableau-de-bord');
    }

    public function testFailedLoginGivesTheSameMessageForUnknownEmails(): void
    {
        $this->post('/connexion', ['email' => 'alice@exemple.fr', 'password' => 'mauvais']);
        $this->assertResponseOk();
        $this->assertFlashMessage('Email ou mot de passe incorrect.');
        $this->assertSessionNotHasKey('Auth');

        $this->post('/connexion', ['email' => 'inconnu@exemple.fr', 'password' => 'mauvais']);
        $this->assertFlashMessage('Email ou mot de passe incorrect.');
    }

    public function testRegisterLogsTheUserIn(): void
    {
        $this->post('/inscription', [
            'first_name' => 'Chloé',
            'last_name' => 'Roux',
            'email' => 'chloe@exemple.fr',
            'password' => 'un-mot-de-passe',
            'role' => 'admin',
        ]);
        $this->assertRedirect('/nuits/ajouter');
        $user = $this->getTableLocator()->get('Users')->find()->where(['email' => 'chloe@exemple.fr'])->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertSession($user->id, 'Auth.id');
    }

    public function testRegisterShowsErrors(): void
    {
        $this->post('/inscription', ['first_name' => 'A', 'last_name' => 'B', 'email' => 'Alice@Exemple.fr', 'password' => 'un-mot-de-passe']);
        $this->assertResponseOk();
        $this->assertResponseContains('Un compte existe déjà avec cette adresse email.');

        $this->post('/inscription', ['first_name' => 'A', 'last_name' => 'B', 'email' => 'nouveau@exemple.fr', 'password' => 'court']);
        $this->assertResponseContains('au moins 8 caractères');
        $this->assertResponseNotContains('court"', 'le mot de passe saisi n\'est pas renvoyé dans la page');
    }

    public function testFormsRequireTheCsrfToken(): void
    {
        $this->setUnlockedFields([]);
        $this->_csrfToken = false;
        $this->post('/connexion', ['email' => 'alice@exemple.fr', 'password' => UsersFixture::PASSWORD]);
        $this->assertResponseCode(403);
    }

    public function testLogoutOnlyAcceptsPost(): void
    {
        $this->loginAs(1);
        $this->get('/deconnexion');
        $this->assertResponseCode(405);

        $this->post('/deconnexion');
        $this->assertRedirect('/connexion');
        $this->assertSessionNotHasKey('Auth');
    }

    public function testAccountUpdatesOnlyAllowedFields(): void
    {
        $this->loginAs(1);
        $this->put('/compte', ['first_name' => 'Alicia', 'last_name' => 'Durand', 'email' => 'alice@exemple.fr', 'role' => 'admin', 'id' => 2]);
        $this->assertRedirect('/compte');
        $users = $this->getTableLocator()->get('Users');
        $this->assertSame('Alicia', $users->get(1)->first_name);
        $this->assertSame('user', $users->get(1)->role);
        $this->assertSame('Bruno', $users->get(2)->first_name);
    }

    public function testPasswordChangeRequiresTheCurrentPassword(): void
    {
        $this->loginAs(1);
        $this->put('/compte/mot-de-passe', ['current_password' => 'faux', 'password' => 'nouveau-mot', 'password_confirm' => 'nouveau-mot']);
        $this->assertResponseOk();
        $this->assertFlashMessage('Le mot de passe actuel est incorrect.');

        $this->put('/compte/mot-de-passe', ['current_password' => UsersFixture::PASSWORD, 'password' => 'nouveau-mot', 'password_confirm' => 'autre-mot']);
        $this->assertResponseContains('Les deux mots de passe ne correspondent pas.');

        $this->put('/compte/mot-de-passe', ['current_password' => UsersFixture::PASSWORD, 'password' => 'nouveau-mot', 'password_confirm' => 'nouveau-mot']);
        $this->assertRedirect('/compte');
        $this->assertTrue($this->getTableLocator()->get('Users')->get(1)->checkPassword('nouveau-mot'));
    }

    public function testDeleteAccountRequiresThePassword(): void
    {
        $this->loginAs(1);
        $this->post('/compte/supprimer', ['password' => 'faux']);
        $this->assertResponseOk();
        $this->assertSame(1, $this->getTableLocator()->get('Users')->find()->where(['id' => 1])->count());

        $this->post('/compte/supprimer', ['password' => UsersFixture::PASSWORD]);
        $this->assertRedirect('/');
        $this->assertSame(0, $this->getTableLocator()->get('Users')->find()->where(['id' => 1])->count());
        $this->assertSame(0, $this->getTableLocator()->get('Nights')->find()->where(['user_id' => 1])->count());
    }

    public function testUnknownPageGivesA404(): void
    {
        $this->get('/users/index');
        $this->assertResponseCode(404);
    }
}
