<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Login');
$routes->setDefaultMethod('getIndex');
$routes->setTranslateURIDashes(false);

$routes->setAutoRoute(false);

// PUBLIC (sans auth)
$routes->get('login',                'Login::getIndex');
$routes->post('login',               'Login::postLogin');
$routes->get('login/register', 'Login::getRegister');
$routes->post('login/register', 'Login::postRegister');
$routes->get('login/logout', 'Login::getLogout');


// FRONT (auth)
$routes->group('', ['filter' => ['auth', 'frontOnly']], static function ($routes) {

    // Dashboard
    $routes->get('/',                'Dashboard::getIndex');
    $routes->get('dashboard',        'Dashboard::getIndex');

    // Profile
    $routes->get('profile',          'Profile::getMe');
    $routes->get('profile/(:num)',   'Profile::getIndex/$1');

    // REPORTS
    $routes->get('report',                 'Report::getIndex');
    $routes->get('report/new',             'Report::getNew');
    $routes->post('report',                'Report::postCreate');

    $routes->get('report/(:num)',          'Report::getShow/$1');
    $routes->get('report/(:num)/edit',     'Report::getEdit/$1');
    $routes->post('report/(:num)/update',  'Report::postUpdate/$1');
    $routes->post('report/(:num)/delete',  'Report::postDelete/$1');
    $routes->post('report/(:num)/duplicate', 'Report::postDuplicate/$1');

    // SECTIONS
    $routes->get('report/(:num)/sections',                'Report::getSections/$1');
    $routes->post('report/(:num)/sections/root',          'Report::postSectionsRoot/$1');
    $routes->post('report/(:num)/sections/(:num)/child',  'Report::postSectionsChild/$1/$2');
    $routes->get('report/(:num)/sections/(:num)/edit',    'Report::getEditSection/$1/$2');
    $routes->post('report/(:num)/sections/(:num)/update', 'Report::postUpdateSection/$1/$2');
    $routes->post('report/(:num)/sections/(:num)/delete', 'Report::postDeleteSection/$1/$2');

    $routes->post('report/sections/upload-image', 'Report::postUploadSectionImage');

    $routes->post('report/(:num)/sections/meta', 'Report::postUpdateMetaInline/$1');
    $routes->post('report/(:num)/sections/(:num)/move-up', 'Report::postMoveRootUp/$1/$2');
    $routes->post('report/(:num)/sections/(:num)/move-down', 'Report::postMoveRootDown/$1/$2');

    $routes->get('report/(:num)/pdf', 'Report::getPdf/$1');

//    $routes->post('report/(:num)/meta', 'Report::updateMeta/$1');

    $routes->get('tabload',         'Tabload::getIndex');

    $routes->group('media', function($routes) {
        $routes->get('/',                     'Media::getIndex');
        $routes->get('folder/(:num)',         'Media::getFolder/$1');
        $routes->post('upload',               'Media::postUpload');
        $routes->post('folder/create',        'Media::postCreateFolder');
        $routes->post('folder/(:num)/rename', 'Media::postRenameFolder/$1');
        $routes->post('folder/delete/(:num)', 'Media::postDeleteFolder/$1');
        $routes->post('delete/(:num)',        'Media::postDelete/$1');
        $routes->get('folders-tree',          'Media::getFoldersTree');
        $routes->post('move/(:num)',          'Media::postMove/$1');
        $routes->post('copy/(:num)',          'Media::postCopy/$1');
    });
});


// ADMIN
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'adminOnly'], static function ($routes) {

    // Dashboard
    $routes->get('/',         'Dashboard::getIndex');
    $routes->get('dashboard', 'Dashboard::getIndex');

    //User
    $routes->get('user',               'User::getIndex');
    $routes->get('user/new',           'User::getIndex/new');
    $routes->get('user/(:num)',        'User::getIndex/$1');
    $routes->post('user/create',       'User::postCreate');
    $routes->post('user/update',       'User::postUpdate');
    $routes->get('user/delete/(:num)', 'User::getDelete/$1');

    $routes->get('user/deactivate/(:num)', 'User::getDeactivate/$1');
    $routes->get('user/activate/(:num)',   'User::getActivate/$1');

    $routes->post('user/search-user', 'User::postSearchUser');
    $routes->get('userpermission', 'UserPermission::getIndex');
    $routes->post('userpermission/search-permission', 'Userpermission::postSearchPermission');


// TOKENS
    $routes->get('token',               'Token::getIndex');
    $routes->get('token/(:num)',        'Token::getIndex/$1');
    $routes->post('token/update',       'Token::postUpdate');
    $routes->get('token/delete/(:num)', 'Token::getDelete/$1');
    $routes->post('token/search-token', 'Token::postSearchToken');


    // REPORTS
    $routes->get('reports',                 'Report::getIndex');
    $routes->post('reports',                'Report::postCreate');

    $routes->get('reports/(:num)',          'Report::getShow/$1');
    $routes->get('reports/(:num)/edit',     'Report::getEdit/$1');
    $routes->post('reports/(:num)/update',  'Report::postUpdate/$1');
    $routes->post('reports/(:num)/delete',  'Report::postDelete/$1');

    // Sections
    $routes->get('reports/(:num)/sections',                'Report::getSections/$1');
    $routes->post('reports/(:num)/sections/root',          'Report::postSectionsRoot/$1');
    $routes->post('reports/(:num)/sections/(:num)/child',  'Report::postSectionsChild/$1/$2');

    $routes->post('reports/(:num)/sections/(:num)/move-up',   'Report::postMoveRootUp/$1/$2');
    $routes->post('reports/(:num)/sections/(:num)/move-down', 'Report::postMoveRootDown/$1/$2');

    $routes->get('reports/(:num)/sections/(:num)/edit',    'Report::getEditSection/$1/$2');
    $routes->post('reports/(:num)/sections/(:num)/update', 'Report::postUpdateSection/$1/$2');
    $routes->post('reports/(:num)/sections/(:num)/delete', 'Report::postDeleteSection/$1/$2');

    $routes->post('reports/(:num)/comments', 'Report::postUpdateComments/$1');

    $routes->post('reports/(:num)/mark-in-review', 'Report::postMarkInReview/$1');
    $routes->post('reports/(:num)/validate',       'Report::postValidate/$1');
    $routes->post('reports/(:num)/assign-validator','Report::postAssignValidator/$1');

    $routes->post('reports/sections/upload-image', 'Report::postUploadSectionImage');

    $routes->get('tabload', 'Tabload::getIndex');

    // logs
    $routes->get('logs', 'Logs::getIndex');


    $routes->group('media', function($routes) {
        $routes->get('/',                       'Media::getIndex');
        $routes->get('folder/(:num)',           'Media::getFolder/$1');

        $routes->post('upload',                 'Media::postUpload');

        $routes->post('folder/create',          'Media::postCreateFolder');
        $routes->post('folder/(:num)/rename',   'Media::postRenameFolder/$1');
        $routes->post('folder/delete/(:num)',   'Media::postDeleteFolder/$1');

        $routes->post('delete/(:num)',          'Media::postDelete/$1');

        $routes->get('folders-tree',            'Media::getFoldersTree');
        $routes->post('move/(:num)',            'Media::postMove/$1');
        $routes->post('copy/(:num)',            'Media::postCopy/$1');
    });
});


// API
$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {

    $routes->group('login', static function ($routes) {
        $routes->post('setAllRequestLimits', 'Login::postSetAllRequestLimits');
        $routes->post('setRequestLimit',     'Login::postSetRequestLimit');

        $routes->post('register',            'Login::postRegister');
        $routes->post('login',               'Login::postLogin');

        $routes->get('token',                'Login::getToken');
        $routes->get('token/(:num)',         'Login::getToken/$1');
    });
});
