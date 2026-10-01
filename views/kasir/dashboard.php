<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/helpers/laundry.php';

requireRole('kasir');

$pdo = getDB();
$todayQuery = $pdo->query('SELECT COUNT(*) AS jumlah, COALESCE(SUM(total_harga), 0) AS omzet FROM tb_transaksi WHERE tanggal_masuk = CURDATE()');
$todaySummary = $todayQuery->fetch();
$readyCount = (int) $pdo->query("SELECT COUNT(*) FROM tb_transaksi WHERE status = 'selesai'")->fetchColumn();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir — Aplikasi Laundry</title>
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
                <p class="text-uppercase text-muted small fw-semibold mb-1">Operasional</p>
                <h1 class="h3 mb-1">Dashboard kasir</h1>
                <p class="text-muted mb-0">Selamat datang, <?= h($_SESSION['nama']) ?>.</p>
            </div>
            <a class="btn btn-primary" href="<?= h(BASE_URL) ?>views/kasir/transactions.php"><i class="bi bi-plus-lg me-1"></i>Buat transaksi</a>
        </div>

        <div class="row g-3">
            <div class="col-12 col-sm-6 col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-2">Transaksi hari ini</p><p class="h3 mb-0"><?= (int) $todaySummary['jumlah'] ?></p></div></div></div>
            <div class="col-12 col-sm-6 col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-2">Nilai transaksi hari ini</p><p class="h3 mb-0"><?= h(rupiah($todaySummary['omzet'])) ?></p></div></div></div>
            <div class="col-12 col-sm-6 col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-2">Siap diambil</p><p class="h3 mb-0"><?= $readyCount ?></p><a class="small" href="<?= h(BASE_URL) ?>views/kasir/transactions.php">Buka daftar transaksi</a></div></div></div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
