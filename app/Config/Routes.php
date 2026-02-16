<?php

namespace Config;

use CodeIgniter\Config\Services;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

// Keep Auto Routing OFF for safety; we define routes explicitly
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */
$routes->get('/', 'Home::index');

$routes->get('dbpage', 'Home::dbtest');
$routes->get('dbtest', function () {
    try {
        $db = \Config\Database::connect();
        $row = $db->query("SELECT DATABASE() AS db, USER() AS u")->getRowArray();
        return "DB OK. db=" . ($row['db'] ?? '-') . " user=" . ($row['u'] ?? '-');
    } catch (\Throwable $e) {
        return "DB FAIL: " . $e->getMessage();
    }
});



use App\Controllers\AuthController;

$routes->get('login', [AuthController::class, 'loginForm']);
$routes->post('login', [AuthController::class, 'login']);
$routes->get('logout', [AuthController::class, 'logout']);

$routes->get('admin', fn() => view('dash/admin'), ['filter' => 'role:admin']);
$routes->get('editor', fn() => view('dash/editor'), ['filter' => 'role:editor']);
$routes->get('reviewer', fn() => view('dash/reviewer'), ['filter' => 'role:reviewer']);
$routes->get('author', fn() => view('dash/author'), ['filter' => 'role:author,admin,editor,reviewer']);

$routes->get('/', 'SiteController::home');

$routes->get('journals', 'SiteController::journals');
$routes->get('journals/(:segment)', 'SiteController::journal/$1');

$routes->get('conferences', 'SiteController::conferences');
$routes->get('conferences/(:segment)', 'SiteController::conference/$1');

$routes->get('published', 'SiteController::published');
$routes->get('published/(:num)', 'SiteController::publication/$1');

$routes->get('download/(:num)', 'SiteController::download/$1');

$routes->get('about', 'SiteController::about');
$routes->get('contact', 'SiteController::contact');
$routes->get('policies', 'SiteController::policies');

$routes->group('admin', ['filter' => 'role:admin'], static function ($routes) {
    $routes->get('/', 'Admin\DashboardController::index');

    // Journals
    $routes->get('journals', 'Admin\JournalsController::index');
    $routes->get('journals/new', 'Admin\JournalsController::new');
    $routes->post('journals', 'Admin\JournalsController::create');
    $routes->get('journals/(:num)/edit', 'Admin\JournalsController::edit/$1');
    $routes->post('journals/(:num)', 'Admin\JournalsController::update/$1');
    $routes->post('journals/(:num)/delete', 'Admin\JournalsController::delete/$1');

    // Conferences
    $routes->get('conferences', 'Admin\ConferencesController::index');
    $routes->get('conferences/new', 'Admin\ConferencesController::new');
    $routes->post('conferences', 'Admin\ConferencesController::create');
    $routes->get('conferences/(:num)/edit', 'Admin\ConferencesController::edit/$1');
    $routes->post('conferences/(:num)', 'Admin\ConferencesController::update/$1');
    $routes->post('conferences/(:num)/delete', 'Admin\ConferencesController::delete/$1');
});













/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */

// Load the system routes file first (if it exists)
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

// Environment-specific routes (optional)
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}


