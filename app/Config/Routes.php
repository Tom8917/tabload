<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Login');
$routes->setDefaultMethod('getIndex');
$routes->setTranslateURIDashes(false);

// Désactive l'auto routing (recommandé si tu veux 0 surprise)
$routes->setAutoRoute(false);

/*
|--------------------------------------------------------------------------
| PUBLIC (sans auth)
|--------------------------------------------------------------------------
*/
$routes->get('login',                'Login::getIndex');
$routes->post('login',               'Login::postLogin');
$routes->get('login/register', 'Login::getRegister');
$routes->post('login/register', 'Login::postRegister');
$routes->get('login/logout', 'Login::getLogout');


/*
|--------------------------------------------------------------------------
| FRONT (auth)
|--------------------------------------------------------------------------
| - "/" = dashboard
| - toutes les pages front ici
*/
$routes->group('', ['filter' => ['auth', 'frontOnly']], static function ($routes) {

    // Dashboard
    $routes->get('/',                'Dashboard::getIndex');
    $routes->get('dashboard',        'Dashboard::getIndex');

    // Cours
    $routes->get('cours',            'Cours::getIndex');
    $routes->get('cours/(:segment)', 'Cours::getShow/$1');

    // Events
    $routes->get('events',           'Events::getIndex');
    $routes->get('events/list',      'Events::getList');

    // Pages
    $routes->get('pages',            'Pages::getIndex');
    $routes->get('pages/(:segment)', 'Pages::getShow/$1');

    // Profile
    // (comme ton Profile attend un ID, on fait une route /profile qui redirige vers /profile/{id})
    $routes->get('profile',          'Profile::getMe');
    $routes->get('profile/(:num)',   'Profile::getIndex/$1');

    // Cours
    $routes->get('media',            'Media::getIndex');

    // REPORTS (Front user)
    $routes->get('report',                 'Report::getIndex');          // liste
    $routes->get('report/new',             'Report::getNew');            // form création
    $routes->post('report',                'Report::postCreate');        // create

    $routes->get('report/(:num)',          'Report::getShow/$1');        // show (détail)
    $routes->get('report/(:num)/edit',     'Report::getEdit/$1');        // form edit
    $routes->post('report/(:num)/update',  'Report::postUpdate/$1');     // update
    $routes->post('report/(:num)/delete',  'Report::postDelete/$1');     // delete

// SECTIONS
    $routes->get('report/(:num)/sections',                'Report::getSections/$1');
    $routes->post('report/(:num)/sections/root',          'Report::postSectionsRoot/$1');
    $routes->post('report/(:num)/sections/(:num)/child',  'Report::postSectionsChild/$1/$2');
    $routes->get('report/(:num)/sections/(:num)/edit',    'Report::getEditSection/$1/$2');
    $routes->post('report/(:num)/sections/(:num)/update', 'Report::postUpdateSection/$1/$2');
    $routes->post('report/(:num)/sections/(:num)/delete', 'Report::postDeleteSection/$1/$2');

    $routes->post('report/sections/upload-image', 'Report::postUploadSectionImage');

    // app/Config/Routes.php (front)
    $routes->get('report/(:num)/meta', 'Report::metaEdit/$1');
    $routes->post('report/(:num)/meta/update', 'Report::metaUpdate/$1');


    $routes->get('tabload',         'Tabload::getIndex');

    $routes->group('media', function($routes) {
        $routes->get('/',               'Media::getIndex');
        $routes->get('folder/(:num)',   'Media::getFolder/$1');

        $routes->post('upload',         'Media::postUpload');

        $routes->post('folder/create',  'Media::postCreateFolder');
        $routes->post('folder/delete/(:num)', 'Media::postDeleteFolder/$1');

        $routes->post('delete/(:num)',  'Media::postDelete/$1');

        $routes->get('folders-tree',    'Media::getFoldersTree');
        $routes->post('move/(:num)',    'Media::postMove/$1');
        $routes->post('copy/(:num)',    'Media::postCopy/$1');
    });
});


// --------------------------------------------------------------------------
// ADMIN (admin)
// --------------------------------------------------------------------------
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'admin'], static function ($routes) {

    // Dashboard admin
    $routes->get('/',         'Dashboard::getIndex');
    $routes->get('dashboard', 'Dashboard::getIndex');

    // Users / permissions etc
    $routes->get('user',         'User::getIndex');
    $routes->get('user/(:num)',  'User::getEdit/$1');
    $routes->post('user/(:num)', 'User::postUpdate/$1');

    $routes->get('userpermission', 'UserPermission::getIndex');

    // ----------------------------------------------------------------------
    // REPORTS (Admin)
    // ----------------------------------------------------------------------
    $routes->get('reports',                 'Report::getIndex');          // liste
    $routes->get('reports/new',             'Report::getNew');            // form création
    $routes->post('reports',                'Report::postCreate');        // create

    $routes->get('reports/(:num)',          'Report::getShow/$1');        // show (détail)
    $routes->get('reports/(:num)/edit',     'Report::getEdit/$1');        // form edit
    $routes->post('reports/(:num)/update',  'Report::postUpdate/$1');     // update
    $routes->post('reports/(:num)/delete',  'Report::postDelete/$1');     // delete

    // --- Sections (Admin)
    $routes->get('reports/(:num)/sections',                'Report::getSections/$1');
    $routes->post('reports/(:num)/sections/root',          'Report::postSectionsRoot/$1');
    $routes->post('reports/(:num)/sections/(:num)/child',  'Report::postSectionsChild/$1/$2');

    $routes->get('reports/(:num)/sections/(:num)/edit',    'Report::getEditSection/$1/$2');
    $routes->post('reports/(:num)/sections/(:num)/update', 'Report::postUpdateSection/$1/$2');
    $routes->post('reports/(:num)/sections/(:num)/delete', 'Report::postDeleteSection/$1/$2');

    // Upload d'image dans une section (même endpoint que front mais côté admin)
    $routes->post('reports/sections/upload-image', 'Report::postUploadSectionImage');

    // ----------------------------------------------------------------------
    // Autres modules admin
    // ----------------------------------------------------------------------
    $routes->get('cours',            'Cours::getIndex');
    $routes->get('cours/(:segment)', 'Cours::getShow/$1');

    $routes->get('events',      'Events::getIndex');
    $routes->get('events/list', 'Events::getList');

    $routes->get('pages',            'Pages::getIndex');
    $routes->get('pages/(:segment)', 'Pages::getShow/$1');

    $routes->get('tabload', 'Tabload::getIndex');

    $routes->group('media', function($routes) {
        $routes->get('/',               'Media::getIndex');
        $routes->get('folder/(:num)',   'Media::getFolder/$1');

        $routes->post('upload',         'Media::postUpload');

        $routes->post('folder/create',  'Media::postCreateFolder');
        $routes->post('folder/delete/(:num)', 'Media::postDeleteFolder/$1');

        $routes->post('delete/(:num)',  'Media::postDelete/$1');

        $routes->get('folders-tree',    'Media::getFoldersTree');
        $routes->post('move/(:num)',    'Media::postMove/$1');
        $routes->post('copy/(:num)',    'Media::postCopy/$1');
    });
});
