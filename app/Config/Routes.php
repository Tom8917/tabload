<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ------------------------------------------------------------
// PUBLIC (non authentifié)
// ------------------------------------------------------------

$routes->get('login',           'Login::getIndex');
$routes->post('login',          'Login::postLogin');
$routes->get('logout',          'Login::logout');

$routes->get('login/register',  'Login::getRegister');
$routes->post('login/register', 'Login::postRegister');

// Optionnel : page d’accueil publique (si tu en as une)
$routes->get('home', 'Home::index');

// ------------------------------------------------------------
// FRONT (auth obligatoire)
// ------------------------------------------------------------

// Accueil front => Dashboard (protégé)
$routes->get('/', 'Dashboard::getIndex', ['filter' => 'auth']);

// Tout ce qui suit est du front, authentifié
$routes->group('', ['filter' => 'auth'], static function ($routes) {

    // Dashboard (si tu veux aussi /dashboard en plus de /)
    $routes->get('dashboard', 'Dashboard::getIndex');

    // -------------------------
    // REPORTS (FRONT ONLY)
    // -------------------------
    $routes->get('reports',                          'Reports::getIndex');
    $routes->get('reports/new',                      'Reports::getNew');
    $routes->post('reports',                         'Reports::postCreate');

    $routes->get('reports/(:num)/sections',                'Reports::getSections/$1');
    $routes->post('reports/(:num)/sections/root',          'Reports::postSectionsRoot/$1');
    $routes->post('reports/(:num)/sections/(:num)/child',  'Reports::postSectionsChild/$1/$2');

    $routes->get('reports/(:num)/sections/(:num)/edit',    'Reports::getEditSection/$1/$2');
    $routes->post('reports/(:num)/sections/(:num)/update', 'Reports::postUpdateSection/$1/$2');
    $routes->post('reports/(:num)/sections/(:num)/delete', 'Reports::postDeleteSection/$1/$2');

    // -------------------------
    // TABLOAD (FRONT ONLY)
    // -------------------------
    $routes->get('tabload', 'Tabload::getIndex');
    // si tu as des POST (import/export etc), ajoute ici :
    // $routes->post('tabload/parse', 'Tabload::postParse');
    // $routes->post('tabload/export', 'Tabload::postExport');

    // -------------------------
    // MODULES "partagés" (front)
    // -------------------------
    $routes->get('events', 'Events::getIndex');
    // $routes->get('events/new', 'Events::getNew');
    // $routes->post('events', 'Events::postCreate');

    $routes->get('cours', 'Cours::getIndex');
    // $routes->get('cours/(:num)', 'Cours::getShow/$1');

    $routes->get('tasks', 'Tasks::getIndex');
    // $routes->post('tasks', 'Tasks::postCreate');

    $routes->get('pages', 'Pages::getIndex');
    // $routes->get('pages/(:num)', 'Pages::getShow/$1');

    $routes->get('profile', 'Profile::getIndex');
    $routes->post('profile', 'Profile::postUpdate');
});


// ------------------------------------------------------------
// ADMIN (auth + admin filter)
// ------------------------------------------------------------

$routes->group('admin', [
    'namespace' => 'App\Controllers\Admin',
    'filter'    => 'admin',
], static function ($routes) {

    // Optionnel : dashboard admin
    $routes->get('/', 'Dashboard::getIndex');

    // -------------------------
    // USERS (ADMIN ONLY)
    // -------------------------
    $routes->get('user',                 'User::getIndex');
    $routes->get('user/new',             'User::getNew');
    $routes->post('user',                'User::postCreate');

    $routes->get('user/(:num)',          'User::getEdit/$1');
    $routes->post('user/(:num)',         'User::postUpdate/$1');
    $routes->post('user/(:num)/delete',  'User::postDelete/$1');

    // Permissions
    $routes->get('userpermission',                'UserPermission::getIndex');
    $routes->get('userpermission/new',            'UserPermission::getNew');
    $routes->post('userpermission',               'UserPermission::postCreate');
    $routes->get('userpermission/(:num)',         'UserPermission::getEdit/$1');
    $routes->post('userpermission/(:num)',        'UserPermission::postUpdate/$1');
    $routes->post('userpermission/(:num)/delete', 'UserPermission::postDelete/$1');

    // -------------------------
    // MODULES "partagés" (admin)
    // IMPORTANT : reports/tabload ne doivent PAS être ici
    // -------------------------
    $routes->get('events', 'Events::getIndex');
    $routes->get('cours',  'Cours::getIndex');
    $routes->get('tasks',  'Tasks::getIndex');
    $routes->get('pages',  'Pages::getIndex');
});
