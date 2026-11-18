<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Entity\User;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Donne ou retire le rôle d'administrateur : bin/cake user_role alice@exemple.fr admin
 */
class UserRoleCommand extends Command
{
    /**
     * Nom de la commande.
     *
     * @return string
     */
    public static function defaultName(): string
    {
        return 'user_role';
    }

    /**
     * Description affichée dans l'aide.
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Change le rôle d\'un compte (user ou admin).';
    }

    /**
     * Arguments : email du compte et rôle.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser
            ->setDescription(static::getDescription())
            ->addArgument('email', ['help' => 'Adresse email du compte', 'required' => true])
            ->addArgument('role', [
                'help' => 'Nouveau rôle',
                'required' => true,
                'choices' => [User::ROLE_USER, User::ROLE_ADMIN],
            ]);
    }

    /**
     * Change le rôle d'un compte.
     *
     * @param \Cake\Console\Arguments $args
     * @param \Cake\Console\ConsoleIo $io
     * @return int
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $users = $this->fetchTable('Users');
        $email = mb_strtolower(trim((string)$args->getArgument('email')));
        $user = $users->find()->where(['email' => $email])->first();
        if (!$user instanceof User) {
            $io->error(sprintf('Aucun compte avec l\'adresse %s.', $email));

            return static::CODE_ERROR;
        }
        $user->role = (string)$args->getArgument('role');
        $users->saveOrFail($user);
        $io->success(sprintf('%s a maintenant le rôle « %s ».', $email, $user->role));

        return static::CODE_SUCCESS;
    }
}
