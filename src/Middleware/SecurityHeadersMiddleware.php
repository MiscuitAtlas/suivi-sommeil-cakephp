<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Core\Configure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * En-têtes de sécurité ajoutés à chaque réponse. L'application n'utilise aucun script ni style
 * en ligne et ne charge rien depuis un autre site : la politique de contenu peut être stricte.
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    /**
     * Ajoute les en-têtes de sécurité à chaque réponse.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request)
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'DENY')
            ->withHeader('Referrer-Policy', 'same-origin')
            ->withHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // DebugKit injecte ses propres scripts : la politique n'est appliquée qu'en production.
        if (!Configure::read('debug')) {
            $response = $response->withHeader(
                'Content-Security-Policy',
                "default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; " .
                "form-action 'self'; frame-ancestors 'none'; base-uri 'self'; object-src 'none'",
            );
        }

        return $response;
    }
}
