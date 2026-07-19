<?php
declare(strict_types=1);

use App\Core\App;
use App\Core\Database;
use App\Models\User;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';
$root = dirname(__DIR__);
Dotenv::createImmutable($root)->safeLoad();

$config = [];
foreach (glob($root . '/config/*.php') as $file) {
    $config[basename($file, '.php')] = require $file;
}
App::instance()->set('config', $config);
App::instance()->set('db', new Database($config['database']));

function prompt(string $label): string
{
    fwrite(STDOUT, $label);
    return trim((string)fgets(STDIN));
}

$name = $argv[1] ?? prompt('Admin neve: ');
$email = $argv[2] ?? prompt('Admin e-mail-címe: ');
$password = $argv[3] ?? prompt('Admin jelszava (min. 12 karakter): ');

$errors = [];
if (mb_strlen($name) < 2) $errors[] = 'A név legalább 2 karakter legyen.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Érvénytelen e-mail-cím.';
if (strlen($password) < 12) $errors[] = 'A jelszó legalább 12 karakter legyen.';

if ($errors) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

if (User::findByEmail($email)) {
    fwrite(STDERR, 'Ez az e-mail-cím már foglalt.' . PHP_EOL);
    exit(1);
}

$id = User::create($name, $email, $password, 'admin');
echo "Admin létrehozva (id: {$id}, e-mail: {$email})." . PHP_EOL;
