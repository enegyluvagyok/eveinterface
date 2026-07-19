<?php
declare(strict_types=1);

use App\Core\App;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Core\View;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
Dotenv::createImmutable($root)->safeLoad();

$config = [];
foreach (glob($root . '/config/*.php') as $file) {
    $config[basename($file, '.php')] = require $file;
}

$app = App::instance();
$app->set('config', $config);
date_default_timezone_set($config['app']['timezone']);

session_name($_ENV['SESSION_NAME'] ?? 'php_mvc_session');
session_save_path($root . '/storage/sessions');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOL),
    'httponly' => true,
    'samesite' => $_ENV['SESSION_SAMESITE'] ?? 'Lax',
]);
ini_set('session.use_strict_mode', '1');
session_start();

$app->set('db', new Database($config['database']));
$app->set('view', new View($root . '/app/Views'));
$app->set('locale', \App\Core\Locale::resolve());

$router = new Router();
require $root . '/routes/web.php';
require $root . '/routes/api.php';

try {
    $router->dispatch(new Request());
} catch (Throwable $e) {
    error_log($e->__toString());
    if ($config['app']['debug']) throw $e;
    http_response_code(500);
    view('errors.500');
}
