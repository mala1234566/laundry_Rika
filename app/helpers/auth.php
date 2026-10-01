<?php

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('views/auth/login.php', 'Silakan login terlebih dahulu.', 'warning');
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    if (!in_array($_SESSION['role'], $roles, true)) {
        redirect(dashboardUrl(), 'Anda tidak memiliki akses ke halaman tersebut.', 'danger');
    }
}

function loginUser(array $user): void {
    startSession();
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id_user'];
    $_SESSION['nama']      = $user['nama'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['id_outlet'] = $user['id_outlet'];
}

function logoutUser(): void {
    startSession();
    $_SESSION = [];
    session_destroy();
}

function dashboardUrl(): string {
    $role = $_SESSION['role'] ?? '';
    return match ($role) {
        'admin'  => BASE_URL . 'views/admin/dashboard.php',
        'kasir'  => BASE_URL . 'views/kasir/dashboard.php',
        'owner'  => BASE_URL . 'views/owner/dashboard.php',
        default  => BASE_URL . 'views/auth/login.php',
    };
}

function redirect(string $url, string $message = '', string $type = 'info'): never {
    startSession();
    if ($message !== '') {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    }
    $location = str_starts_with($url, BASE_URL) ? $url : BASE_URL . ltrim($url, '/');
    header('Location: ' . $location);
    exit;
}

function getFlash(): ?array {
    startSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
