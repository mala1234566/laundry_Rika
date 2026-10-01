<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/helpers/auth.php';

requireRole('owner');

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner — Aplikasi Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php require_once __DIR__ . '/../partials/navbar.php'; ?>

    <div class="container py-5">

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-1">
                    <i class="bi bi-bar-chart-line me-2 text-warning"></i>
                    Dashboard Owner
                </h4>
                <p class="text-muted mb-0">
                    Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>!
                    Anda login sebagai <span class="badge bg-warning text-dark">Owner</span>.
                </p>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
