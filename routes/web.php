<?php
use App\Controllers\AuthController;
use App\Controllers\ContractorController;
use App\Controllers\EmployeeController;
use App\Controllers\HomeController;
use App\Controllers\LocaleController;
use App\Controllers\SubcontractorController;
use App\Controllers\UserController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;

$router->get('/', [HomeController::class, 'index']);
$router->get('/lang', [LocaleController::class, 'set']);
$router->get('/dashboard', [HomeController::class, 'dashboard'], [AuthMiddleware::class]);
$router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class, CsrfMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword'], [GuestMiddleware::class]);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword'], [GuestMiddleware::class, CsrfMiddleware::class]);
$router->get('/reset-password', [AuthController::class, 'showResetPassword'], [GuestMiddleware::class]);
$router->post('/reset-password', [AuthController::class, 'resetPassword'], [GuestMiddleware::class, CsrfMiddleware::class]);

$admin = [AuthMiddleware::class, AdminMiddleware::class];
$router->get('/admin/users', [UserController::class, 'index'], $admin);
$router->get('/admin/users/create', [UserController::class, 'create'], $admin);
$router->post('/admin/users', [UserController::class, 'store'], [...$admin, CsrfMiddleware::class]);
$router->get('/admin/users/edit', [UserController::class, 'edit'], $admin);
$router->post('/admin/users/edit', [UserController::class, 'update'], [...$admin, CsrfMiddleware::class]);
$router->get('/admin/contractors', [ContractorController::class, 'index'], $admin);
$router->post('/admin/contractors', [ContractorController::class, 'store'], [...$admin, CsrfMiddleware::class]);
$router->get('/admin/subcontractors', [SubcontractorController::class, 'index'], $admin);
$router->post('/admin/subcontractors', [SubcontractorController::class, 'store'], [...$admin, CsrfMiddleware::class]);

$router->get('/employees', [EmployeeController::class, 'index'], [AuthMiddleware::class]);
$router->get('/employees/create', [EmployeeController::class, 'create'], [AuthMiddleware::class]);
$router->post('/employees', [EmployeeController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/employees/edit', [EmployeeController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/employees/edit', [EmployeeController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/employees/photo', [EmployeeController::class, 'photo'], [AuthMiddleware::class]);
