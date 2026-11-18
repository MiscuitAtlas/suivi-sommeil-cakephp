<?php
declare(strict_types=1);

namespace App;

use App\Middleware\HostHeaderMiddleware;
use App\Middleware\SecurityHeadersMiddleware;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Identifier\PasswordIdentifier;
use Authentication\Middleware\AuthenticationMiddleware;
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Policy\OrmResolver;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Event\EventManagerInterface;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;
use function Cake\Core\env;

class Application extends BaseApplication implements
    AuthenticationServiceProviderInterface,
    AuthorizationServiceProviderInterface
{
    /**
     * Charge la configuration et les plugins.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        parent::bootstrap();

        // Une table sans classe dédiée déclenche une erreur au lieu d'être créée silencieusement.
        FactoryLocator::add('Table', (new TableLocator())->allowFallbackClass(false));
    }

    /**
     * Déclare la pile de middlewares de l'application.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue
     * @return \Cake\Http\MiddlewareQueue
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))
            ->add(new HostHeaderMiddleware())
            ->add(new SecurityHeadersMiddleware())
            ->add(new AssetMiddleware(['cacheTime' => Configure::read('Asset.cacheTime')]))
            ->add(new RoutingMiddleware($this))
            ->add(new BodyParserMiddleware())
            // Cookie réservé au HTTPS quand le site est servi en HTTPS.
            ->add(new CsrfProtectionMiddleware(['httponly' => true, 'secure' => (bool)env('HTTPS')]))
            ->add(new AuthenticationMiddleware($this))
            // Chaque action doit vérifier les droits ou dire explicitement qu'elle n'en a pas besoin.
            ->add(new AuthorizationMiddleware($this, ['requireAuthorizationCheck' => true]));

        return $middlewareQueue;
    }

    /**
     * Authentification par session ou par formulaire (email et mot de passe).
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Authentication\AuthenticationServiceInterface
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $loginRoute = ['controller' => 'Users', 'action' => 'login', 'plugin' => null, 'prefix' => false];
        $service = new AuthenticationService([
            'unauthenticatedRedirect' => Router::url($loginRoute),
            'queryParam' => 'redirect',
        ]);

        $fields = [
            PasswordIdentifier::CREDENTIAL_USERNAME => 'email',
            PasswordIdentifier::CREDENTIAL_PASSWORD => 'password',
        ];
        $service->loadAuthenticator('Authentication.Session');
        $service->loadAuthenticator('Authentication.Form', [
            'identifier' => ['className' => 'Authentication.Password', 'fields' => $fields],
            'fields' => $fields,
            'loginUrl' => Router::url($loginRoute),
        ]);

        return $service;
    }

    /**
     * Autorisation par politiques d'accès, résolues depuis l'ORM.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Authorization\AuthorizationServiceInterface
     */
    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        return new AuthorizationService(new OrmResolver());
    }

    /**
     * Déclare les services du conteneur d'injection.
     *
     * @param \Cake\Core\ContainerInterface $container
     * @return void
     */
    public function services(ContainerInterface $container): void
    {
    }

    /**
     * Déclare les écouteurs d'événements globaux.
     *
     * @param \Cake\Event\EventManagerInterface $eventManager
     * @return \Cake\Event\EventManagerInterface
     */
    public function events(EventManagerInterface $eventManager): EventManagerInterface
    {
        return $eventManager;
    }
}
