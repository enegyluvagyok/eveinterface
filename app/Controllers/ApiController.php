<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Employee;
use App\Models\User;
use App\Services\JwtService;

final class ApiController
{
    public function token(Request $request): never
    {
        $user = User::findByEmail((string)$request->input('email'));
        if (!$user || !password_verify((string)$request->input('password'), $user['password_hash'])) {
            Response::json(['error' => 'Invalid credentials'], 401);
        }
        Response::json([
            'token_type' => 'Bearer',
            'access_token' => (new JwtService())->issue((int)$user['id']),
            'expires_in' => config('security.jwt_ttl', 3600),
        ]);
    }

    public function me(Request $request): never
    {
        $user = User::find((int)($_SERVER['API_USER_ID'] ?? 0));
        if (!$user) Response::json(['error' => 'User not found'], 404);
        Response::json(['data' => $user]);
    }

    public function employees(Request $request): never
    {
        $rows = array_map(function (array $row): array {
            $row['photo'] = $row['photo'] ? $this->fileUrl($row['photo']) : null;
            $row['avatar'] = $row['avatar'] ? $this->fileUrl($row['avatar']) : null;
            return $row;
        }, Employee::pendingForExport());

        Response::json(['data' => $rows]);
    }

    public function employeeFile(Request $request): never
    {
        $requested = (string)$request->input('path', '');
        $baseDir = realpath(dirname(__DIR__, 2) . '/storage/uploads/employees');
        $resolved = $baseDir ? realpath($baseDir . '/' . basename($requested)) : false;

        if (!$baseDir || !$resolved || !str_starts_with($resolved, $baseDir)) {
            Response::json(['error' => 'File not found'], 404);
        }

        $ext = strtolower(pathinfo($resolved, PATHINFO_EXTENSION));
        $contentType = $ext === 'png' ? 'image/png' : 'image/jpeg';
        Response::file($resolved, $contentType);
    }

    public function employeesAck(Request $request): never
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids)) Response::json(['error' => 'ids must be an array'], 422);
        Employee::markImported($ids);
        Response::json(['updated' => count($ids)]);
    }

    private function fileUrl(string $relativePath): string
    {
        return rtrim(config('app.url'), '/') . '/api/employees/file?path=' . urlencode(basename($relativePath));
    }
}
