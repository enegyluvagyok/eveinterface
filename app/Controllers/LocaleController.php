<?php
namespace App\Controllers;

use App\Core\Locale;
use App\Core\Request;

final class LocaleController
{
    public function set(Request $request): never
    {
        Locale::set((string)$request->input('to', ''));
        $redirect = (string)$request->input('redirect', '/');
        if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = '/';
        }
        redirect($redirect);
    }
}
