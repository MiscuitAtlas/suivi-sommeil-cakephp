<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class User extends Entity
{
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    /**
     * Seuls ces champs peuvent venir d'un formulaire : le rôle et l'identifiant ne sont jamais
     * modifiables par la saisie de l'utilisateur.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'first_name' => true,
        'last_name' => true,
        'email' => true,
        'password' => true,
    ];

    /**
     * Le mot de passe haché n'apparaît ni dans les exports ni dans le JSON.
     *
     * @var list<string>
     */
    protected array $_hidden = ['password'];

    /**
     * Hache le mot de passe à l'affectation.
     *
     * @param string $password
     * @return string|null
     */
    protected function _setPassword(string $password): ?string
    {
        return $password === '' ? null : (new DefaultPasswordHasher())->hash($password);
    }

    /**
     * Normalise l'email : minuscules, sans espaces autour.
     *
     * @param string $email
     * @return string
     */
    protected function _setEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    /**
     * Indique si l'utilisateur a le rôle administrateur.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Vérifie un mot de passe contre le hachage enregistré.
     *
     * @param string $password
     * @return bool
     */
    public function checkPassword(string $password): bool
    {
        return (new DefaultPasswordHasher())->check($password, (string)$this->getOriginal('password'));
    }
}
