<?php

namespace App\Services;

use App\Repositories\AdminRepository;

class AuthService
{
    public function __construct(private AdminRepository $adminRepository) {}

    public function login(string $username, string $password): bool
    {
        if (empty($username) || empty($password)) {
            return false;
        }

        $admin = $this->adminRepository->findByUsername($username);

        // run password_verify even on a miss to avoid timing attacks
        $hash = $admin['password_hash'] ?? '$2y$10$invalidhashpadding000000000000000000000000000000000000';

        if (!password_verify($password, $hash) || $admin === null) {
            return false;
        }

        // regenerate session ID (so others can't reuse it)
        session_regenerate_id(true);

        $_SESSION['logged_in']  = true;
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['username']   = $admin['username'];
        $_SESSION['created_at'] = time();

        return true;
    }

    public function logout(): void
    {
        // reset all session variables
        $_SESSION = [];

        // delete the session cookie (with past expiration)
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
}