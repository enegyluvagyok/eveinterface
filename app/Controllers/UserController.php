<?php
namespace App\Controllers;

use App\Core\PasswordReset;
use App\Core\Request;
use App\Models\Contractor;
use App\Models\Subcontractor;
use App\Models\User;
use App\Services\MailService;
use PDOException;
use Throwable;

final class UserController
{
    public function index(Request $request): void
    {
        view('admin.users.index', ['users' => User::all()]);
    }

    public function create(Request $request): void
    {
        view('admin.users.create', [
            'contractors' => Contractor::all(),
            'subcontractors' => Subcontractor::all(),
        ]);
    }

    public function store(Request $request): never
    {
        $name = trim((string)$request->input('name'));
        $email = trim((string)$request->input('email'));
        $phone = trim((string)$request->input('phone', ''));
        $role = (string)$request->input('role', 'user') === 'admin' ? 'admin' : 'user';
        $contractorIds = array_map('intval', (array)$request->input('contractor_ids', []));
        $subcontractorIds = array_map('intval', (array)$request->input('subcontractor_ids', []));
        $_SESSION['_old'] = compact('name', 'email', 'phone', 'role', 'contractorIds', 'subcontractorIds');

        $errors = [];
        if (mb_strlen($name) < 2) $errors[] = t('validation.name_min');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = t('validation.email_invalid');
        if ($phone !== '' && !preg_match('/^[0-9+\-\s()]{6,30}$/', $phone)) $errors[] = t('validation.phone_invalid');
        if ($errors) { $_SESSION['_errors'] = $errors; redirect('/admin/users/create'); }

        try {
            $throwawayPassword = bin2hex(random_bytes(32));
            $userId = User::create($name, $email, $throwawayPassword, $role, $phone !== '' ? $phone : null);
            User::syncContractors($userId, $contractorIds);
            User::syncSubcontractors($userId, $subcontractorIds);
        } catch (PDOException $e) {
            $_SESSION['_errors'] = [$e->getCode() === '23000' ? t('validation.email_taken') : t('validation.create_failed')];
            redirect('/admin/users/create');
        }

        $token = PasswordReset::issue($userId);
        $inviteUrl = PasswordReset::url($token);
        $this->sendInviteEmail($email, $name, $inviteUrl);

        $_SESSION['_invite_link'] = $inviteUrl;
        $_SESSION['_flash'] = t('flash.user_invited');
        redirect('/admin/users');
    }

    private function sendInviteEmail(string $email, string $name, string $inviteUrl): void
    {
        try {
            $html = '<p>' . e($name) . ',</p><p>' . e(t('mail.invite_body')) . '</p><p><a href="' . e($inviteUrl) . '">' . e($inviteUrl) . '</a></p>';
            (new MailService())->send($email, t('mail.invite_subject'), $html);
        } catch (Throwable $e) {
            error_log('User invite email failed: ' . $e->getMessage());
        }
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->input('id');
        $editUser = User::find($id);
        if (!$editUser) { http_response_code(404); view('errors.404'); return; }

        view('admin.users.edit', [
            'editUser' => $editUser,
            'contractors' => Contractor::all(),
            'subcontractors' => Subcontractor::all(),
            'selectedContractorIds' => User::contractorIds($id),
            'selectedSubcontractorIds' => User::subcontractorIds($id),
        ]);
    }

    public function update(Request $request): never
    {
        $id = (int)$request->input('id');
        if (!User::find($id)) { http_response_code(404); view('errors.404'); exit; }

        $role = (string)$request->input('role', 'user') === 'admin' ? 'admin' : 'user';
        $phone = trim((string)$request->input('phone', ''));
        $contractorIds = array_map('intval', (array)$request->input('contractor_ids', []));
        $subcontractorIds = array_map('intval', (array)$request->input('subcontractor_ids', []));

        if ($phone !== '' && !preg_match('/^[0-9+\-\s()]{6,30}$/', $phone)) {
            $_SESSION['_errors'] = [t('validation.phone_invalid')];
            redirect('/admin/users/edit?id=' . $id);
        }

        User::updateRole($id, $role);
        User::updatePhone($id, $phone !== '' ? $phone : null);
        User::syncContractors($id, $contractorIds);
        User::syncSubcontractors($id, $subcontractorIds);

        $_SESSION['_flash'] = t('flash.user_updated');
        redirect('/admin/users');
    }
}
