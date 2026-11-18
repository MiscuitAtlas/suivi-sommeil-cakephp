<?php
declare(strict_types=1);

use Cake\Routing\RouteBuilder;

/*
 * Adresses en français, déclarées une par une : aucune route générique
 * n'expose d'action par accident.
 */
return function (RouteBuilder $routes): void {
    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->connect('/', ['controller' => 'Pages', 'action' => 'home']);

        $builder->connect('/connexion', ['controller' => 'Users', 'action' => 'login']);
        $builder->connect('/inscription', ['controller' => 'Users', 'action' => 'register']);
        $builder->connect('/demo', ['controller' => 'Demo', 'action' => 'start']);
        $builder->connect('/deconnexion', ['controller' => 'Users', 'action' => 'logout']);
        $builder->connect('/compte', ['controller' => 'Users', 'action' => 'account']);
        $builder->connect('/compte/mot-de-passe', ['controller' => 'Users', 'action' => 'password']);
        $builder->connect('/compte/supprimer', ['controller' => 'Users', 'action' => 'delete']);

        $builder->connect('/tableau-de-bord', ['controller' => 'Dashboard', 'action' => 'index']);

        $builder->connect('/nuits', ['controller' => 'Nights', 'action' => 'index']);
        $builder->connect('/nuits/ajouter', ['controller' => 'Nights', 'action' => 'add']);
        $builder->connect('/nuits/{id}/modifier', ['controller' => 'Nights', 'action' => 'edit'], ['id' => '\d+', 'pass' => ['id']]);
        $builder->connect('/nuits/{id}/supprimer', ['controller' => 'Nights', 'action' => 'delete'], ['id' => '\d+', 'pass' => ['id']]);
        $builder->connect('/semaine', ['controller' => 'Nights', 'action' => 'week']);

        $builder->connect('/admin/menus', ['controller' => 'Menus', 'action' => 'index']);
        $builder->connect('/admin/menus/ajouter', ['controller' => 'Menus', 'action' => 'add']);
        $builder->connect('/admin/menus/{id}/modifier', ['controller' => 'Menus', 'action' => 'edit'], ['id' => '\d+', 'pass' => ['id']]);
        $builder->connect('/admin/menus/{id}/supprimer', ['controller' => 'Menus', 'action' => 'delete'], ['id' => '\d+', 'pass' => ['id']]);
        $builder->connect('/admin/menus/{id}/{direction}', ['controller' => 'Menus', 'action' => 'move'], [
            'id' => '\d+',
            'direction' => 'monter|descendre',
            'pass' => ['id', 'direction'],
        ]);
    });
};
