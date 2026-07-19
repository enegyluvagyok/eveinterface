<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Contractor;
use App\Models\Employee;
use App\Models\Subcontractor;
use App\Models\User;
use RuntimeException;

final class EmployeeController
{
    private const UPLOAD_DIR = 'employees';
    private const AVATAR_WIDTH = 160;
    private const PER_PAGE = 20;

    public function index(Request $request): void
    {
        $scope = User::accessScope(Auth::user());

        $filters = [
            'date_from' => (string)$request->input('date_from', '') ?: null,
            'date_to' => (string)$request->input('date_to', '') ?: null,
            'contractor_id' => (string)$request->input('contractor_id', '') ?: null,
            'subcontractor_id' => (string)$request->input('subcontractor_id', '') ?: null,
            'q' => trim((string)$request->input('q', '')) ?: null,
            ...$scope,
        ];

        $total = Employee::count($filters);
        $totalPages = max(1, (int)ceil($total / self::PER_PAGE));
        $page = max(1, min($totalPages, (int)$request->input('page', 1)));
        $offset = ($page - 1) * self::PER_PAGE;

        view('employees.index', [
            'employees' => Employee::all($filters, self::PER_PAGE, $offset),
            'contractors' => $this->scopedContractors($scope),
            'subcontractors' => $this->scopedSubcontractors($scope),
            'suggestions' => Employee::searchSuggestions($scope),
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'perPage' => self::PER_PAGE,
        ]);
    }

    public function photo(Request $request): never
    {
        $requested = (string)$request->input('path', '');
        $baseDir = realpath(dirname(__DIR__, 2) . '/storage/uploads/' . self::UPLOAD_DIR);
        $resolved = $baseDir ? realpath($baseDir . '/' . basename($requested)) : false;

        if (!$baseDir || !$resolved || !str_starts_with($resolved, $baseDir)) {
            http_response_code(404);
            exit;
        }

        $ext = strtolower(pathinfo($resolved, PATHINFO_EXTENSION));
        Response::file($resolved, $ext === 'png' ? 'image/png' : 'image/jpeg');
    }

    public function create(Request $request): void
    {
        $scope = User::accessScope(Auth::user());
        view('employees.create', [
            'contractors' => $this->scopedContractors($scope),
            'subcontractors' => $this->scopedSubcontractors($scope),
        ]);
    }

    public function store(Request $request): never
    {
        $scope = User::accessScope(Auth::user());
        $employeeCode = trim((string)$request->input('employee_code'));
        $contractorId = (string)$request->input('contractor_id');
        $subcontractorId = (string)$request->input('subcontractor_id');
        $fullname = trim((string)$request->input('fullname'));
        $idcard = trim((string)$request->input('idcard'));
        $_SESSION['_old'] = compact('employeeCode', 'contractorId', 'subcontractorId', 'fullname', 'idcard');

        $errors = [];
        if ($employeeCode === '') $errors[] = t('validation.card_code_required');
        if (!ctype_digit($contractorId) || !Contractor::find((int)$contractorId) || !$this->inScope((int)$contractorId, $scope['allowed_contractor_ids'] ?? null)) {
            $errors[] = t('validation.contractor_invalid');
        }
        if (!ctype_digit($subcontractorId) || !Subcontractor::find((int)$subcontractorId) || !$this->inScope((int)$subcontractorId, $scope['allowed_subcontractor_ids'] ?? null)) {
            $errors[] = t('validation.subcontractor_invalid');
        }
        if (mb_strlen($fullname) < 2) $errors[] = t('validation.fullname_min');
        if ($idcard === '') $errors[] = t('validation.idcard_required');

        $photoPath = null;
        $avatarPath = null;
        if (!$errors) {
            try {
                [$photoPath, $avatarPath] = $this->storePhoto($_FILES['photo'] ?? null);
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if ($errors) { $_SESSION['_errors'] = $errors; redirect('/employees/create'); }

        Employee::create([
            'employee_code' => $employeeCode,
            'contractor_id' => (int)$contractorId,
            'subcontractor_id' => (int)$subcontractorId,
            'fullname' => $fullname,
            'idcard' => $idcard,
            'photo' => $photoPath,
            'avatar' => $avatarPath,
            'created_by' => Auth::id(),
        ]);
        $_SESSION['_flash'] = t('flash.employee_saved');
        redirect('/employees');
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->input('id');
        $employee = Employee::find($id);
        if (!$employee) { http_response_code(404); view('errors.404'); return; }

        $scope = User::accessScope(Auth::user());
        if ($blocked = $this->editBlockReason($employee, $scope)) {
            http_response_code(403);
            view('errors.403', ['message' => $blocked]);
            return;
        }

        view('employees.edit', [
            'employee' => $employee,
            'contractors' => $this->scopedContractors($scope),
            'subcontractors' => $this->scopedSubcontractors($scope),
        ]);
    }

    public function update(Request $request): never
    {
        $id = (int)$request->input('id');
        $employee = Employee::find($id);
        if (!$employee) { http_response_code(404); view('errors.404'); exit; }

        $scope = User::accessScope(Auth::user());
        if ($blocked = $this->editBlockReason($employee, $scope)) {
            http_response_code(403);
            view('errors.403', ['message' => $blocked]);
            exit;
        }

        $employeeCode = trim((string)$request->input('employee_code'));
        $contractorId = (string)$request->input('contractor_id');
        $subcontractorId = (string)$request->input('subcontractor_id');
        $fullname = trim((string)$request->input('fullname'));
        $idcard = trim((string)$request->input('idcard'));
        $_SESSION['_old'] = compact('employeeCode', 'contractorId', 'subcontractorId', 'fullname', 'idcard');

        $errors = [];
        if ($employeeCode === '') $errors[] = t('validation.card_code_required');
        if (!ctype_digit($contractorId) || !Contractor::find((int)$contractorId) || !$this->inScope((int)$contractorId, $scope['allowed_contractor_ids'] ?? null)) {
            $errors[] = t('validation.contractor_invalid');
        }
        if (!ctype_digit($subcontractorId) || !Subcontractor::find((int)$subcontractorId) || !$this->inScope((int)$subcontractorId, $scope['allowed_subcontractor_ids'] ?? null)) {
            $errors[] = t('validation.subcontractor_invalid');
        }
        if (mb_strlen($fullname) < 2) $errors[] = t('validation.fullname_min');
        if ($idcard === '') $errors[] = t('validation.idcard_required');

        $photoData = [];
        if (!$errors) {
            try {
                [$photoPath, $avatarPath] = $this->storePhoto($_FILES['photo'] ?? null);
                if ($photoPath !== null) {
                    $photoData = ['photo' => $photoPath, 'avatar' => $avatarPath];
                }
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if ($errors) { $_SESSION['_errors'] = $errors; redirect('/employees/edit?id=' . $id); }

        Employee::update($id, [
            'employee_code' => $employeeCode,
            'contractor_id' => (int)$contractorId,
            'subcontractor_id' => (int)$subcontractorId,
            'fullname' => $fullname,
            'idcard' => $idcard,
            ...$photoData,
        ]);
        $_SESSION['_flash'] = t('flash.employee_updated');
        redirect('/employees');
    }

    /** @return ?string translated reason the edit is blocked, or null if allowed */
    private function editBlockReason(array $employee, array $scope): ?string
    {
        if ($employee['imported_at'] !== null) return t('employees.already_imported');
        if (!$this->inScope((int)$employee['contractor_id'], $scope['allowed_contractor_ids'] ?? null)) return t('errors.403_desc');
        if (!$this->inScope((int)$employee['subcontractor_id'], $scope['allowed_subcontractor_ids'] ?? null)) return t('errors.403_desc');
        return null;
    }

    private function scopedContractors(array $scope): array
    {
        if (!array_key_exists('allowed_contractor_ids', $scope)) return Contractor::all();
        $allowed = $scope['allowed_contractor_ids'];
        return array_values(array_filter(Contractor::all(), fn($c) => in_array((int)$c['id'], $allowed, true)));
    }

    private function scopedSubcontractors(array $scope): array
    {
        if (!array_key_exists('allowed_subcontractor_ids', $scope)) return Subcontractor::all();
        $allowed = $scope['allowed_subcontractor_ids'];
        return array_values(array_filter(Subcontractor::all(), fn($s) => in_array((int)$s['id'], $allowed, true)));
    }

    /** @param ?int[] $allowed null = unrestricted (admin) */
    private function inScope(int $id, ?array $allowed): bool
    {
        return $allowed === null || in_array($id, $allowed, true);
    }

    /** @return array{0: ?string, 1: ?string} relative storage paths [photo, avatar] */
    private function storePhoto(?array $file): array
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(t('validation.photo_upload_failed'));
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException(t('validation.photo_size'));
        }
        $info = getimagesize($file['tmp_name']);
        if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            throw new RuntimeException(t('validation.photo_format'));
        }

        $dir = dirname(__DIR__, 2) . '/storage/uploads/' . self::UPLOAD_DIR;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(t('validation.storage_dir_failed'));
        }

        $ext = $info[2] === IMAGETYPE_PNG ? 'png' : 'jpg';
        $basename = bin2hex(random_bytes(16));
        $photoRelative = self::UPLOAD_DIR . '/' . $basename . '.' . $ext;
        $avatarRelative = self::UPLOAD_DIR . '/' . $basename . '_avatar.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $basename . '.' . $ext)) {
            throw new RuntimeException(t('validation.photo_save_failed'));
        }

        $this->generateAvatar($dir . '/' . $basename . '.' . $ext, $dir . '/' . $basename . '_avatar.' . $ext, $info[2]);

        return [$photoRelative, $avatarRelative];
    }

    private function generateAvatar(string $sourcePath, string $targetPath, int $imageType): void
    {
        $source = $imageType === IMAGETYPE_PNG ? imagecreatefrompng($sourcePath) : imagecreatefromjpeg($sourcePath);
        if (!$source) return;

        $width = imagesx($source);
        $height = imagesy($source);
        $targetWidth = self::AVATAR_WIDTH;
        $targetHeight = (int)round($height * ($targetWidth / $width));

        $avatar = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($imageType === IMAGETYPE_PNG) {
            imagealphablending($avatar, false);
            imagesavealpha($avatar, true);
        }
        imagecopyresampled($avatar, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        if ($imageType === IMAGETYPE_PNG) {
            imagepng($avatar, $targetPath);
        } else {
            imagejpeg($avatar, $targetPath, 85);
        }

        imagedestroy($source);
        imagedestroy($avatar);
    }
}
