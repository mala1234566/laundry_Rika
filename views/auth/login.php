<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/auth.php';

startSession();

// Sudah login → langsung ke dashboard
if (isLoggedIn()) {
    header('Location: ' . dashboardUrl());
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = getDB()->prepare(
            'SELECT id_user, id_outlet, nama, username, password, role
             FROM tb_user
             WHERE username = ?
             LIMIT 1'
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            loginUser($user);
            redirect(dashboardUrl(), 'Selamat datang, ' . $user['nama'] . '!', 'success');
        } else {
            $error = 'Username atau password salah.';
        }
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Aplikasi Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        .btn-primary { background: #0d6efd; }
        .brand-icon { font-size: 2.5rem; color: #0d6efd; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="col-11 col-sm-8 col-md-5 col-lg-4">

        <div class="text-center mb-4">
            <i class="bi bi-basket2-fill brand-icon"></i>
            <h4 class="fw-bold mt-2 mb-0">Aplikasi Laundry</h4>
            <small class="text-muted">Masuk ke akun Anda</small>
        </div>

        <div class="card p-4">

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show py-2" role="alert">
                    <?= htmlspecialchars($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input
                            type="text"
                            name="username"
                            class="form-control"
                            placeholder="Masukkan username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            autofocus
                            required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            required>
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </button>
            </form>
        </div>

        <p class="text-center text-muted mt-3" style="font-size:.8rem;">
            &copy; <?= date('Y') ?> Aplikasi Pengelolaan Laundry
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon  = document.getElementById('eyeIcon');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
</body>
</html>
