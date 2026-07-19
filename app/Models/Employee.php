<?php
namespace App\Models;

final class Employee
{
    private const LIST_SELECT = <<<SQL
        SELECT e.*, c.name AS contractor_name, s.name AS subcontractor_name, u.name AS created_by_name
        FROM employees e
        JOIN contractors c ON c.id = e.contractor_id
        JOIN subcontractors s ON s.id = e.subcontractor_id
        JOIN users u ON u.id = e.created_by
        SQL;

    /**
     * @param array{date_from?: ?string, date_to?: ?string, contractor_id?: ?int, subcontractor_id?: ?int, q?: ?string,
     *              allowed_contractor_ids?: int[], allowed_subcontractor_ids?: int[]} $filters
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
            $where[] = '(e.employee_code LIKE :q OR e.fullname LIKE :q OR e.idcard LIKE :q)';
            $params['q'] = '%' . str_replace(['%', '_'], ['\%', '\_'], (string)$filters['q']) . '%';
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

    /** Distinct employee_code/fullname/idcard values for the free-text search datalist, scoped like all(). */
    public static function searchSuggestions(array $scope, int $limit = 200): array
    {
        $values = [];
        foreach (['employee_code', 'fullname', 'idcard'] as $column) {
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
        $sql = 'UPDATE employees SET employee_code = :employee_code, contractor_id = :contractor_id,
                subcontractor_id = :subcontractor_id, fullname = :fullname, idcard = :idcard, updated_at = NOW()';
        $params = [
            'id' => $id,
            'employee_code' => $data['employee_code'],
            'contractor_id' => $data['contractor_id'],
            'subcontractor_id' => $data['subcontractor_id'],
            'fullname' => $data['fullname'],
            'idcard' => $data['idcard'],
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
            'INSERT INTO employees (employee_code, contractor_id, subcontractor_id, fullname, idcard, photo, avatar, created_by, created_at, updated_at)
             VALUES (:employee_code, :contractor_id, :subcontractor_id, :fullname, :idcard, :photo, :avatar, :created_by, NOW(), NOW())'
        );
        $stmt->execute([
            'employee_code' => $data['employee_code'],
            'contractor_id' => $data['contractor_id'],
            'subcontractor_id' => $data['subcontractor_id'],
            'fullname' => $data['fullname'],
            'idcard' => $data['idcard'],
            'photo' => $data['photo'] ?? null,
            'avatar' => $data['avatar'] ?? null,
            'created_by' => $data['created_by'],
        ]);
        return (int)app('db')->pdo()->lastInsertId();
    }

    public static function pendingForExport(): array
    {
        $stmt = app('db')->pdo()->query(self::LIST_SELECT . ' WHERE e.imported_at IS NULL ORDER BY e.created_at');
        return $stmt->fetchAll();
    }

    public static function markImported(array $ids): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) return;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = app('db')->pdo()->prepare("UPDATE employees SET imported_at = NOW() WHERE id IN ({$placeholders})");
        $stmt->execute($ids);
    }
}
