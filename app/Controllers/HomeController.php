<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;

final class HomeController
{
    public function index(Request $request): void { view('home'); }
    public function dashboard(Request $request): void { view('dashboard', ['user' => Auth::user()]); }
}
