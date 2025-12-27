<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Redirect root to projects (which requires auth)
$routes->get('/', 'ProjectController::index', ['filter' => 'auth']);

$routes->group('', ['filter' => 'auth'], function($routes) {
    
    // ========================================
    // MASTER DATA - Roles & Permissions
    // ========================================
    $routes->group('roles', function($routes) {
        $routes->get('/', 'RoleController::index');
        $routes->get('create', 'RoleController::create');
        $routes->post('/', 'RoleController::store');
        $routes->get('(:num)', 'RoleController::show/$1');
        $routes->get('(:num)/edit', 'RoleController::edit/$1');
        $routes->post('(:num)', 'RoleController::update/$1');
        $routes->post('(:num)/delete', 'RoleController::delete/$1');
    });

    $routes->group('permissions', function($routes) {
        $routes->get('/', 'PermissionController::index');
        $routes->get('create', 'PermissionController::create');
        $routes->post('/', 'PermissionController::store');
        $routes->get('(:num)', 'PermissionController::show/$1');
        $routes->get('(:num)/edit', 'PermissionController::edit/$1');
        $routes->post('(:num)', 'PermissionController::update/$1');
        $routes->post('(:num)/delete', 'PermissionController::delete/$1');
    });

    // ========================================
    // WORKSPACES
    // ========================================
    $routes->group('workspaces', function($routes) {
        $routes->get('/', 'WorkspaceController::index');
        $routes->get('create', 'WorkspaceController::create');
        $routes->post('/', 'WorkspaceController::store');
        $routes->get('(:num)', 'WorkspaceController::show/$1');
        $routes->get('(:num)/edit', 'WorkspaceController::edit/$1');
        $routes->post('(:num)', 'WorkspaceController::update/$1');
        $routes->post('(:num)/delete', 'WorkspaceController::delete/$1');
        $routes->post('(:num)/users', 'WorkspaceController::addUser/$1');
        $routes->post('(:num)/users/(:num)/remove', 'WorkspaceController::removeUser/$1/$2');
    });

    // ========================================
    // PROJECTS
    // ========================================
    $routes->group('projects', function($routes) {
        $routes->get('/', 'ProjectController::index');
        $routes->get('create', 'ProjectController::create');
        $routes->post('/', 'ProjectController::store');
        $routes->get('(:num)', 'ProjectController::show/$1');
        $routes->get('(:num)/edit', 'ProjectController::edit/$1');
        $routes->post('(:num)', 'ProjectController::update/$1');
        $routes->post('(:num)/delete', 'ProjectController::delete/$1');
        $routes->post('(:num)/users', 'ProjectController::addUser/$1');
        $routes->post('(:num)/users/(:num)/remove', 'ProjectController::removeUser/$1/$2');
    });

    // ========================================
    // ISSUES / TASKS
    // ========================================
    $routes->group('issues', function($routes) {
        $routes->get('/', 'IssueController::index');
        $routes->get('create', 'IssueController::create');
        $routes->post('/', 'IssueController::store');
        $routes->get('(:num)', 'IssueController::show/$1');
        $routes->get('(:num)/edit', 'IssueController::edit/$1');
        $routes->post('(:num)', 'IssueController::update/$1');
        $routes->post('(:num)/delete', 'IssueController::delete/$1');
        $routes->post('(:num)/move', 'IssueController::move/$1');
        $routes->post('(:num)/assign', 'IssueController::assign/$1');
    });

    // ========================================
    // LABELS
    // ========================================
    $routes->group('labels', function($routes) {
        $routes->post('/', 'LabelController::store');
        $routes->post('(:num)', 'LabelController::update/$1');
        $routes->post('(:num)/delete', 'LabelController::delete/$1');
        $routes->post('(:num)/issues/(:num)', 'LabelController::addToIssue/$1/$2');
        $routes->post('(:num)/issues/(:num)/remove', 'LabelController::removeFromIssue/$1/$2');
    });

    // ========================================
    // COMMENTS
    // ========================================
    $routes->group('comments', function($routes) {
        $routes->post('/', 'CommentController::store');
        $routes->post('(:num)', 'CommentController::update/$1');
        $routes->post('(:num)/delete', 'CommentController::delete/$1');
        $routes->get('issue/(:num)', 'CommentController::getByIssue/$1');
    });

    // ========================================
    // BOARDS (Legacy - for backward compatibility)
    // ========================================
    $routes->get('boards', 'BoardController::index');
    $routes->get('boards/(:num)', 'BoardController::show/$1');
    $routes->post('boards', 'BoardController::create');
    $routes->get('boards/(:num)/edit', 'BoardController::edit/$1');
    $routes->post('boards/(:num)/update', 'BoardController::update/$1');
    $routes->post('boards/delete/(:num)', 'BoardController::delete/$1');
    
    // ========================================
    // CARDS (Legacy - for backward compatibility)
    // ========================================
    $routes->post('cards/create', 'CardController::create');
    $routes->get('cards/(:num)/edit', 'CardController::edit/$1');
    $routes->post('cards/(:num)/update', 'CardController::update/$1');
    $routes->post('cards/(:num)/delete', 'CardController::delete/$1');
    $routes->post('cards/move', 'CardController::move');
    
    // ========================================
    // COLUMNS
    // ========================================
    $routes->post('columns/create', 'ColumnController::create');
    $routes->get('columns/(:num)/edit', 'ColumnController::edit/$1');
    $routes->post('columns/(:num)/update', 'ColumnController::update/$1');
    $routes->post('columns/(:num)/delete', 'ColumnController::delete/$1');

    // ========================================
    // ACTIVITY LOGS
    // ========================================
    $routes->get('activity-logs', 'ActivityLogController::index');

});

$routes->get('/login', 'AuthController::loginForm');
$routes->get('/logout', 'AuthController::logout');
$routes->get('/register', 'AuthController::registerForm');

$routes->post('/login', 'AuthController::login');
$routes->post('/register', 'AuthController::register');
$routes->get('/verify-email/(:any)', 'AuthController::verifyEmail/$1');

$routes->get('/test-email', function () {
    $email = \Config\Services::email();
    $email->setTo('test@kanban.local');
    $email->setSubject('Mailpit Test');
    $email->setMessage('This is a test email.');

    if (! $email->send()) {
        return '<pre>' . print_r($email->printDebugger(['headers']), true) . '</pre>';
    }

    return 'Email sent!';
});



