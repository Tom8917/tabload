<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {

    $routes->get('reports',                    'Report::getIndex');
    $routes->get('reports/new',                'Report::getNew');
    $routes->post('reports',                   'Report::postCreate');

    $routes->get('reports/(:num)/sections',                  'Report::getSections/$1');
    $routes->post('reports/(:num)/sections/root',            'Report::postSectionsRoot/$1');
    $routes->post('reports/(:num)/sections/(:num)/child',    'Report::postSectionsChild/$1/$2');

    $routes->get('reports/(:num)/sections/(:num)/edit',    'Report::getEditSection/$1/$2');
    $routes->post('reports/(:num)/sections/(:num)/update', 'Report::postUpdateSection/$1/$2');
    $routes->post('reports/(:num)/sections/(:num)/delete', 'Report::postDeleteSection/$1/$2');

    $routes->get('tabload', 'Tabload::getIndex');
});
