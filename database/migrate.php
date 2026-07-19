<?php
declare(strict_types=1);

use App\Core\Database;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';
$root = dirname(__DIR__);
Dotenv::createImmutable($root)->safeLoad();
$db = new Database(require $root . '/config/database.php');
foreach (glob(__DIR__ . '/migrations/*.sql') as $file) {
    $db->pdo()->exec(file_get_contents($file));
    echo 'Migrated: ' . basename($file) . PHP_EOL;
}
