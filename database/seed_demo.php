<?php
declare(strict_types=1);

use App\Core\App;
use App\Core\Database;
use App\Models\Contractor;
use App\Models\Employee;
use App\Models\Subcontractor;
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
$db = new Database($config['database']);
App::instance()->set('db', $db);
$pdo = $db->pdo();

const CONTRACTOR_COUNT = 20;
const SUBCONTRACTOR_COUNT = 20;
const USER_COUNT = 10;
const EMPLOYEE_COUNT = 100;
const MOCK_PASSWORD = 'MockUser1234!';

$keepAdminId = $pdo->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id LIMIT 1")->fetchColumn();
if ($keepAdminId === false) {
    fwrite(STDERR, 'Nincs admin felhasználó. Előbb futtasd: composer seed:admin' . PHP_EOL);
    exit(1);
}
$keepAdminId = (int)$keepAdminId;

echo 'Meglévő adatok törlése…' . PHP_EOL;
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE employees');
$pdo->exec('TRUNCATE user_contractors');
$pdo->exec('TRUNCATE user_subcontractors');
$pdo->exec('TRUNCATE subcontractors');
$pdo->exec('TRUNCATE contractors');
$pdo->prepare('DELETE FROM users WHERE id <> :id')->execute(['id' => $keepAdminId]);
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

$companyWords = [
    'Kovács', 'Nagy', 'Szabó', 'Tóth', 'Horváth', 'Kiss', 'Molnár', 'Németh', 'Varga', 'Balogh',
    'Papp', 'Takács', 'Juhász', 'Lakatos', 'Mészáros', 'Oláh', 'Simon', 'Rácz', 'Fekete', 'Fehér',
    'Duna Szerelő', 'Tisza Ipari', 'Pannon Logisztika', 'Kelet Karbantartó', 'Nyugati Építő',
    'Alföld Gépész', 'Dél-Budai Villanyszerelő', 'Északi Acélszerkezet', 'Zöld Mező Kertészeti', 'Vasút-Technika',
];
$companySuffixes = ['Kft.', 'Bt.', 'Zrt.', 'Kkt.'];

$firstNames = ['Zoltán', 'László', 'Péter', 'István', 'József', 'Gábor', 'Sándor', 'Ferenc', 'Tamás', 'Attila', 'Mária', 'Erzsébet', 'Katalin', 'Zsuzsanna', 'Ildikó', 'Éva', 'Andrea', 'Judit', 'Anna', 'Krisztina'];
$lastNames = ['Kovács', 'Szabó', 'Nagy', 'Tóth', 'Horváth', 'Varga', 'Kiss', 'Molnár', 'Németh', 'Farkas', 'Balogh', 'Papp', 'Takács', 'Juhász', 'Lakatos', 'Mészáros', 'Oláh', 'Simon', 'Rácz', 'Fekete'];

/** @param array<int, string> $used */
function uniqueCompanyName(array $words, array $suffixes, array &$used): string
{
    do {
        $name = $words[array_rand($words)] . ' ' . $suffixes[array_rand($suffixes)];
    } while (isset($used[$name]));
    $used[$name] = true;
    return $name;
}

function randomPersonName(array $first, array $last): string
{
    return $last[array_rand($last)] . ' ' . $first[array_rand($first)];
}

function randomPhone(): string
{
    return '+3630' . random_int(1000000, 9999999);
}

/** @param int[] $ids @return int[] */
function pickRandom(array $ids, int $min, int $max): array
{
    $count = min(count($ids), random_int($min, $max));
    $keys = (array)array_rand($ids, $count);
    return array_values(array_intersect_key($ids, array_flip($keys)));
}

echo 'Fővállalkozók létrehozása…' . PHP_EOL;
$usedContractorNames = [];
$contractorIds = [];
for ($i = 1; $i <= CONTRACTOR_COUNT; $i++) {
    Contractor::create($i, uniqueCompanyName($companyWords, $companySuffixes, $usedContractorNames));
    $contractorIds[] = $i;
}

echo 'Alvállalkozók létrehozása…' . PHP_EOL;
$usedSubcontractorNames = [];
$subcontractorIds = [];
for ($i = 1; $i <= SUBCONTRACTOR_COUNT; $i++) {
    Subcontractor::create($i, uniqueCompanyName($companyWords, $companySuffixes, $usedSubcontractorNames));
    $subcontractorIds[] = $i;
}

echo 'Felhasználók létrehozása…' . PHP_EOL;
$creatorScopes = [];
$remainingUsers = USER_COUNT - 1;
$extraAdmins = 1;
for ($i = 1; $i <= $remainingUsers; $i++) {
    $isAdmin = $i <= $extraAdmins;
    $role = $isAdmin ? 'admin' : 'user';
    $name = ($isAdmin ? 'Teszt Adminisztrátor ' : 'Teszt Kapcsolattartó ') . $i;
    $email = ($isAdmin ? 'admin' : 'user') . $i . '@eve-mock.local';
    $userId = User::create($name, $email, MOCK_PASSWORD, $role, randomPhone());

    if (!$isAdmin) {
        $assignedContractors = pickRandom($contractorIds, 1, 4);
        $assignedSubcontractors = pickRandom($subcontractorIds, 1, 4);
        User::syncContractors($userId, $assignedContractors);
        User::syncSubcontractors($userId, $assignedSubcontractors);
        $creatorScopes[$userId] = ['contractors' => $assignedContractors, 'subcontractors' => $assignedSubcontractors];
    }
}

echo 'Alkalmazottak létrehozása…' . PHP_EOL;
$creatorIds = array_keys($creatorScopes);
$insert = $pdo->prepare(
    'INSERT INTO employees (contractor_id, subcontractor_id, fullname, idcard, medical_fitness_until, card_color, photo, avatar, created_by, imported_at, created_at, updated_at)
     VALUES (:contractor_id, :subcontractor_id, :fullname, :idcard, :medical_fitness_until, :card_color, NULL, NULL, :created_by, :imported_at, :created_at, :updated_at)'
);
$cardColors = Employee::CARD_COLORS;
for ($i = 1; $i <= EMPLOYEE_COUNT; $i++) {
    $creatorId = $creatorIds[array_rand($creatorIds)];
    $scope = $creatorScopes[$creatorId];
    $contractorId = $scope['contractors'][array_rand($scope['contractors'])];
    $subcontractorId = $scope['subcontractors'][array_rand($scope['subcontractors'])];

    $createdAt = date('Y-m-d H:i:s', time() - random_int(0, 90 * 86400));
    $imported = random_int(1, 4) === 1;
    $importedAt = $imported ? date('Y-m-d H:i:s', strtotime($createdAt) + random_int(3600, 5 * 86400)) : null;

    $insert->execute([
        'contractor_id' => $contractorId,
        'subcontractor_id' => $subcontractorId,
        'fullname' => randomPersonName($firstNames, $lastNames),
        'idcard' => chr(random_int(65, 90)) . chr(random_int(65, 90)) . random_int(100000, 999999),
        'medical_fitness_until' => date('Y-m-d', time() + random_int(-180, 365) * 86400),
        'card_color' => $cardColors[array_rand($cardColors)],
        'created_by' => $creatorId,
        'imported_at' => $importedAt,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);
}

echo PHP_EOL . 'Kész: ' . CONTRACTOR_COUNT . ' fővállalkozó, ' . SUBCONTRACTOR_COUNT . ' alvállalkozó, '
    . USER_COUNT . ' felhasználó, ' . EMPLOYEE_COUNT . ' alkalmazott.' . PHP_EOL;
echo 'A mock felhasználók jelszava: ' . MOCK_PASSWORD . ' (pl. admin1@eve-mock.local, user1@eve-mock.local … user8@eve-mock.local)' . PHP_EOL;
