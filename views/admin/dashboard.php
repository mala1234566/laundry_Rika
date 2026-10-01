<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/helpers/laundry.php';

requireRole('admin');

$pdo = getDB();
$packageCount = (int) $pdo->query('SELECT COUNT(*) FROM tb_paket')->fetchColumn();
$memberCount = (int) $pdo->query('SELECT COUNT(*) FROM tb_member')->fetchColumn();
$transactionCount = (int) $pdo->query('SELECT COUNT(*) FROM tb_transaksi')->fetchColumn();
$activeCount = (int) $pdo->query("SELECT COUNT(*) FROM tb_transaksi WHERE status IN ('baru', 'proses', 'selesai')")->fetchColumn();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — Aplikasi Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php require_once __DIR__ . '/../partials/navbar.php'; ?>

    <main class="container py-4 py-lg-5">
        <?php if ($flash): ?>
            <div class="alert alert-<?= h($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= h($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <p class="text-uppercase text-muted small fw-semibold mb-1">Administrasi</p>
                <h1 class="h3 mb-1">Dashboard admin</h1>
                <p class="text-muted mb-0">Selamat datang, <?= h($_SESSION['nama']) ?>.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-primary" href="<?= h(BASE_URL) ?>views/admin/catalog.php"><i class="bi bi-people me-1"></i>Member & paket</a>
                <a class="btn btn-primary" href="<?= h(BASE_URL) ?>views/kasir/transactions.php"><i class="bi bi-receipt me-1"></i>Transaksi</a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-2">Paket aktif</p><p class="h3 mb-0"><?= $packageCount ?></p><a class="small" href="<?= h(BASE_URL) ?>views/admin/catalog.php#packages-title">Kelola paket</a></div></div></div>
            <div class="col-12 col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-2">Member</p><p class="h3 mb-0"><?= $memberCount ?></p><a class="small" href="<?= h(BASE_URL) ?>views/admin/catalog.php#members-title">Kelola member</a></div></div></div>
            <div class="col-12 col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-2">Semua transaksi</p><p class="h3 mb-0"><?= $transactionCount ?></p></div></div></div>
            <div class="col-12 col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-2">Perlu diproses/diambil</p><p class="h3 mb-0"><?= $activeCount ?></p></div></div></div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
