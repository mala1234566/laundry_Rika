<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/helpers/laundry.php';

requireRole('owner');

$pdo = getDB();
$monthStart = (string) $pdo->query("SELECT DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetchColumn();
$monthDate = strtotime($monthStart);
$monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$monthLabel = $monthNames[(int) date('n', $monthDate)] . ' ' . date('Y', $monthDate);
$monthQuery = $pdo->prepare(
    'SELECT COUNT(*) AS jumlah_transaksi, COALESCE(SUM(total_harga), 0) AS omzet
     FROM tb_transaksi
     WHERE tanggal_masuk >= ? AND tanggal_masuk < DATE_ADD(?, INTERVAL 1 MONTH)'
);
$monthQuery->execute([$monthStart, $monthStart]);
$monthSummary = $monthQuery->fetch();

$activeCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM tb_transaksi WHERE status IN ('baru', 'proses', 'selesai')"
)->fetchColumn();
$memberCount = (int) $pdo->query('SELECT COUNT(*) FROM tb_member')->fetchColumn();
$statusCounts = $pdo->query('SELECT status, COUNT(*) AS jumlah FROM tb_transaksi GROUP BY status')->fetchAll();
$recentTransactions = $pdo->query(
    'SELECT t.kode_transaksi, t.tanggal_masuk, t.status, t.total_harga,
            m.nama AS nama_member, u.nama AS nama_kasir
     FROM tb_transaksi t
     LEFT JOIN tb_member m ON m.id_member = t.id_member
     JOIN tb_user u ON u.id_user = t.id_user
     ORDER BY t.created_at DESC
     LIMIT 10'
)->fetchAll();
$statusTotals = array_fill_keys(['baru', 'proses', 'selesai', 'diambil'], 0);
foreach ($statusCounts as $statusCount) {
    $statusTotals[$statusCount['status']] = (int) $statusCount['jumlah'];
}
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
    <main class="container py-4 py-lg-5">
        <?php if ($flash): ?>
            <div class="alert alert-<?= h($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= h($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <p class="text-uppercase text-muted small fw-semibold mb-1">Laporan · <?= h($monthLabel) ?></p>
                <h1 class="h3 mb-1">Ringkasan laundry</h1>
                <p class="text-muted mb-0">Selamat datang, <?= h($_SESSION['nama']) ?>.</p>
            </div>
            <a class="btn btn-outline-primary" href="<?= h(BASE_URL) ?>views/kasir/transactions.php"><i class="bi bi-receipt me-1"></i>Lihat transaksi</a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-2">Omzet bulan ini</p><p class="h4 mb-0"><?= h(rupiah($monthSummary['omzet'])) ?></p></div></div></div>
            <div class="col-12 col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-2">Transaksi bulan ini</p><p class="h4 mb-0"><?= (int) $monthSummary['jumlah_transaksi'] ?></p></div></div></div>
            <div class="col-12 col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-2">Masih berjalan</p><p class="h4 mb-0"><?= $activeCount ?></p></div></div></div>
            <div class="col-12 col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-2">Member terdaftar</p><p class="h4 mb-0"><?= $memberCount ?></p></div></div></div>
        </div>

        <section class="card border-0 shadow-sm mb-4" aria-labelledby="status-title">
            <div class="card-header bg-white border-0 pt-4 px-4"><h2 class="h5 mb-0" id="status-title">Status pesanan</h2></div>
            <div class="card-body px-4"><div class="row g-3">
                <?php foreach ($statusTotals as $status => $count): ?>
                    <div class="col-6 col-lg-3"><div class="border rounded-2 p-3"><div class="text-muted small"><?= h(transactionStatusLabel($status)) ?></div><div class="fs-4 fw-semibold"><?= $count ?></div></div></div>
                <?php endforeach; ?>
            </div></div>
        </section>

        <section class="card border-0 shadow-sm" aria-labelledby="recent-title">
            <div class="card-header bg-white border-0 pt-4 px-4"><h2 class="h5 mb-0" id="recent-title">Transaksi terbaru</h2></div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th class="ps-4">Kode</th><th>Tanggal</th><th>Member</th><th>Kasir</th><th>Status</th><th class="pe-4">Total</th></tr></thead>
                    <tbody>
                        <?php if (!$recentTransactions): ?><tr><td class="text-center text-muted py-5" colspan="6">Belum ada transaksi.</td></tr><?php endif; ?>
                        <?php foreach ($recentTransactions as $transaction): ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?= h($transaction['kode_transaksi']) ?></td>
                                <td><?= h($transaction['tanggal_masuk']) ?></td>
                                <td><?= h($transaction['nama_member'] ?: 'Pelanggan umum') ?></td>
                                <td><?= h($transaction['nama_kasir']) ?></td>
                                <td><?= h(transactionStatusLabel($transaction['status'])) ?></td>
                                <td class="pe-4 text-nowrap"><?= h(rupiah($transaction['total_harga'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
