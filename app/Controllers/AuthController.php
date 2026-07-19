<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\PasswordReset;
use App\Core\Request;
use App\Models\User;
use App\Services\MailService;
use Throwable;

final class AuthController
{
    public function showLogin(Request $request): void { view('auth.login'); }

    public function login(Request $request): never
    {
        $_SESSION['_old'] = ['email' => (string)$request->input('email', '')];
        if (!Auth::attempt((string)$request->input('email'), (string)$request->input('password'))) {
            $_SESSION['_errors'] = [t('validation.invalid_credentials')];
            redirect('/login');
        }
        redirect('/dashboard');
    }

    public function logout(Request $request): never
    {
        Auth::logout();
        redirect('/');
    }

    public function showForgotPassword(Request $request): void { view('auth.forgot-password'); }

    public function forgotPassword(Request $request): never
    {
        $email = trim((string)$request->input('email'));
        $_SESSION['_old'] = compact('email');

        $user = filter_var($email, FILTER_VALIDATE_EMAIL) ? User::findByEmail($email) : null;
        if ($user) {
            $token = PasswordReset::issue((int)$user['id']);
            $this->sendResetEmail($user, $token, t('mail.reset_subject'), t('mail.reset_body'));
        }

        $_SESSION['_flash'] = t('auth.forgot_password_sent');
        redirect('/forgot-password');
    }

    public function showResetPassword(Request $request): void
    {
        $token = (string)$request->input('token', '');
        if (!PasswordReset::verify($token)) {
            $_SESSION['_errors'] = [t('validation.token_invalid_or_expired')];
            redirect('/forgot-password');
        }
        view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request): never
    {
        $token = (string)$request->input('token', '');
        $user = PasswordReset::verify($token);
        if (!$user) {
            $_SESSION['_errors'] = [t('validation.token_invalid_or_expired')];
            redirect('/forgot-password');
        }

        $password = (string)$request->input('password');
        $confirmation = (string)$request->input('password_confirmation');

        $errors = [];
        if (strlen($password) < 12) $errors[] = t('validation.password_min');
        if ($password !== $confirmation) $errors[] = t('validation.password_mismatch');
        if ($errors) {
            $_SESSION['_errors'] = $errors;
            redirect('/reset-password?token=' . urlencode($token));
        }

        User::updatePassword((int)$user['id'], password_hash($password, PASSWORD_DEFAULT));
        PasswordReset::consume((int)$user['id']);
        Auth::loginAs((int)$user['id']);
        $_SESSION['_flash'] = t('flash.password_reset_success');
        redirect('/dashboard');
    }

    private function sendResetEmail(array $user, string $token, string $subject, string $bodyIntro): void
    {
        try {
            $url = PasswordReset::url($token);
            $html = '<p>' . e($bodyIntro) . '</p><p><a href="' . e($url) . '">' . e($url) . '</a></p>';
            (new MailService())->send((string)$user['email'], $subject, $html);
        } catch (Throwable $e) {
            error_log('Password reset email failed: ' . $e->getMessage());
        }
    }
}
