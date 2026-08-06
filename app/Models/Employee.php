<?php
namespace App\Models;

final class Employee
{
    public const CARD_COLORS = ['red', 'green', 'blue'];

    private const LIST_SELECT = <<<SQL
        SELECT e.*, c.name AS contractor_name, s.name AS subcontractor_name, u.name AS created_by_name
        FROM employees e
        JOIN contractors c ON c.id = e.contractor_id
        JOIN subcontractors s ON s.id = e.subcontractor_id
        JOIN users u ON u.id = e.created_by
        SQL;

    /**
     * @param array{date_from?: ?string, date_to?: ?string, contractor_id?: ?int, subcontractor_id?: ?int, q?: ?string,
     *              medical_fitness_status?: ?string, card_color?: ?string, allowed_contractor_ids?: int[], allowed_subcontractor_ids?: int[], created_by?: ?int} $filters
     */
    public static function all(array $filters = [], ?int $limit = null, int $offset = 0): array
    {
        [$where, $params] = self::buildWhere($filters);
        if ($where === null) return [];

        $sql = self::LIST_SELECT . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY e.created_at DESC';
        if ($limit !== null) $sql .= ' LIMIT :limit OFFSET :offset';

        $stmt = app('db')->pdo()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @param array $filters same shape as all() */
    public static function count(array $filters = []): int
    {
        [$where, $params] = self::buildWhere($filters);
        if ($where === null) return 0;

        $sql = 'SELECT COUNT(*) FROM employees e' . ($where ? ' WHERE ' . implode(' AND ', $where) : '');
        $stmt = app('db')->pdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /** @return array{0: ?array<int, string>, 1: array<string, mixed>} null where = no access, short-circuit */
    private static function buildWhere(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where[] = 'e.created_at >= :date_from';
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'e.created_at <= :date_to';
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['contractor_id'])) {
            $where[] = 'e.contractor_id = :contractor_id';
            $params['contractor_id'] = (int)$filters['contractor_id'];
        }
        if (!empty($filters['subcontractor_id'])) {
            $where[] = 'e.subcontractor_id = :subcontractor_id';
            $params['subcontractor_id'] = (int)$filters['subcontractor_id'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(e.fullname LIKE :q_fullname OR e.idcard LIKE :q_idcard)';
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], (string)$filters['q']) . '%';
            $params['q_fullname'] = $like;
            $params['q_idcard'] = $like;
        }
        if (!empty($filters['medical_fitness_status'])) {
            if ($filters['medical_fitness_status'] === 'valid') {
                $where[] = 'e.medical_fitness_until >= CURDATE()';
            } elseif ($filters['medical_fitness_status'] === 'expired') {
                $where[] = '(e.medical_fitness_until < CURDATE() OR e.medical_fitness_until IS NULL)';
            }
        }
        if (!empty($filters['card_color']) && in_array($filters['card_color'], self::CARD_COLORS, true)) {
            $where[] = 'e.card_color = :card_color';
            $params['card_color'] = $filters['card_color'];
        }

        if (array_key_exists('allowed_contractor_ids', $filters)) {
            if (!$filters['allowed_contractor_ids']) return [null, []];
            [$clause, $clauseParams] = self::inClause($filters['allowed_contractor_ids'], 'ac');
            $where[] = "e.contractor_id IN ({$clause})";
            $params += $clauseParams;
        }
        if (array_key_exists('allowed_subcontractor_ids', $filters)) {
            if (!$filters['allowed_subcontractor_ids']) return [null, []];
            [$clause, $clauseParams] = self::inClause($filters['allowed_subcontractor_ids'], 'as');
            $where[] = "e.subcontractor_id IN ({$clause})";
            $params += $clauseParams;
        }
        if (!empty($filters['created_by'])) {
            $where[] = 'e.created_by = :created_by';
            $params['created_by'] = (int)$filters['created_by'];
        }

        return [$where, $params];
    }

    /** @return array{0: string, 1: array<string, int>} */
    private static function inClause(array $ids, string $prefix): array
    {
        $placeholders = [];
        $params = [];
        foreach (array_values($ids) as $i => $id) {
            $key = "{$prefix}{$i}";
            $placeholders[] = ":{$key}";
            $params[$key] = (int)$id;
        }
        return [implode(',', $placeholders), $params];
    }

    /** Distinct fullname/idcard values for the free-text search datalist, scoped like all(). */
    public static function searchSuggestions(array $scope, int $limit = 200): array
    {
        $values = [];
        foreach (['fullname', 'idcard'] as $column) {
            $values = array_merge($values, self::distinctValues($column, $scope, $limit));
        }
        return array_values(array_unique($values));
    }

    private static function distinctValues(string $column, array $scope, int $limit): array
    {
        [$where, $params] = self::buildWhere($scope);
        if ($where === null) return [];

        $sql = "SELECT DISTINCT e.{$column} AS v FROM employees e"
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . " ORDER BY e.{$column} LIMIT {$limit}";
        $stmt = app('db')->pdo()->prepare($sql);
        $stmt->execute($params);
        return array_column($stmt->fetchAll(), 'v');
    }

    public static function find(int $id): ?array
    {
        $stmt = app('db')->pdo()->prepare(self::LIST_SELECT . ' WHERE e.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function update(int $id, array $data): void
    {
        $sql = 'UPDATE employees SET contractor_id = :contractor_id,
                subcontractor_id = :subcontractor_id, fullname = :fullname, idcard = :idcard,
                medical_fitness_until = :medical_fitness_until, card_color = :card_color, updated_at = NOW()';
        $params = [
            'id' => $id,
            'contractor_id' => $data['contractor_id'],
            'subcontractor_id' => $data['subcontractor_id'],
            'fullname' => $data['fullname'],
            'idcard' => $data['idcard'],
            'medical_fitness_until' => $data['medical_fitness_until'],
            'card_color' => $data['card_color'],
        ];
        if (array_key_exists('photo', $data)) {
            $sql .= ', photo = :photo, avatar = :avatar';
            $params['photo'] = $data['photo'];
            $params['avatar'] = $data['avatar'];
        }
        $sql .= ' WHERE id = :id AND imported_at IS NULL';
        $stmt = app('db')->pdo()->prepare($sql);
        $stmt->execute($params);
    }

    public static function create(array $data): int
    {
        $stmt = app('db')->pdo()->prepare(
            'INSERT INTO employees (contractor_id, subcontractor_id, fullname, idcard, medical_fitness_until, card_color, photo, avatar, created_by, created_at, updated_at)
             VALUES (:contractor_id, :subcontractor_id, :fullname, :idcard, :medical_fitness_until, :card_color, :photo, :avatar, :created_by, NOW(), NOW())'
        );
        $stmt->execute([
            'contractor_id' => $data['contractor_id'],
            'subcontractor_id' => $data['subcontractor_id'],
            'fullname' => $data['fullname'],
            'idcard' => $data['idcard'],
            'medical_fitness_until' => $data['medical_fitness_until'],
            'card_color' => $data['card_color'],
            'photo' => $data['photo'] ?? null,
            'avatar' => $data['avatar'] ?? null,
            'created_by' => $data['created_by'],
        ]);
        return (int)app('db')->pdo()->lastInsertId();
    }

    /** @param array $scope same shape as all() — empty = admin, unrestricted */
    public static function pendingForExport(array $scope = []): array
    {
        [$where, $params] = self::buildWhere($scope);
        if ($where === null) return [];

        $where[] = 'e.imported_at IS NULL';
        $sql = self::LIST_SELECT . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY e.created_at';
        $stmt = app('db')->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @param array $scope same shape as all() — empty = admin, unrestricted */
    public static function markImported(array $ids, array $scope = []): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) return;

        $conditions = ['id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')'];
        $params = $ids;

        if (array_key_exists('allowed_contractor_ids', $scope)) {
            if (!$scope['allowed_contractor_ids']) return;
            $conditions[] = 'contractor_id IN (' . implode(',', array_fill(0, count($scope['allowed_contractor_ids']), '?')) . ')';
            $params = array_merge($params, array_map('intval', $scope['allowed_contractor_ids']));
        }
        if (array_key_exists('allowed_subcontractor_ids', $scope)) {
            if (!$scope['allowed_subcontractor_ids']) return;
            $conditions[] = 'subcontractor_id IN (' . implode(',', array_fill(0, count($scope['allowed_subcontractor_ids']), '?')) . ')';
            $params = array_merge($params, array_map('intval', $scope['allowed_subcontractor_ids']));
        }
        if (!empty($scope['created_by'])) {
            $conditions[] = 'created_by = ?';
            $params[] = (int)$scope['created_by'];
        }

        $sql = 'UPDATE employees SET imported_at = NOW() WHERE ' . implode(' AND ', $conditions);
        $stmt = app('db')->pdo()->prepare($sql);
        $stmt->execute($params);
    }
}
