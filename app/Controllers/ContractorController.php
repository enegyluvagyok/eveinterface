<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\Contractor;
use PDOException;

final class ContractorController
{
    public function index(Request $request): void
    {
        view('admin.contractors.index', ['contractors' => Contractor::all()]);
    }

    public function store(Request $request): never
    {
        $id = (string)$request->input('id');
        $name = trim((string)$request->input('name'));
        $_SESSION['_old'] = compact('id', 'name');

        $errors = [];
        if (!ctype_digit($id) || (int)$id < 1) $errors[] = t('validation.contractor_id_invalid');
        if (mb_strlen($name) < 2) $errors[] = t('validation.name_min');
        if ($errors) { $_SESSION['_errors'] = $errors; redirect('/admin/contractors'); }

        try {
            Contractor::create((int)$id, $name);
        } catch (PDOException $e) {
            $_SESSION['_errors'] = [$e->getCode() === '23000' ? t('validation.id_or_name_taken') : t('validation.create_failed')];
            redirect('/admin/contractors');
        }
        $_SESSION['_flash'] = t('flash.contractor_created');
        redirect('/admin/contractors');
    }
}
