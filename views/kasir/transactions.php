<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/helpers/laundry.php';

requireRole('kasir', 'admin', 'owner');

$pdo = getDB();
$nextStatus = [
    'baru' => 'proses',
    'proses' => 'selesai',
    'selesai' => 'diambil',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['role'] === 'owner') {
        redirect('views/owner/dashboard.php', 'Akun owner hanya dapat melihat transaksi.', 'warning');
    }

    if (!verifyCsrfToken()) {
        redirect('views/kasir/transactions.php', 'Form sudah kedaluwarsa. Silakan coba lagi.', 'warning');
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $packageId = filter_var($_POST['id_paket'] ?? null, FILTER_VALIDATE_INT);
        $memberValue = trim((string) ($_POST['id_member'] ?? ''));
        $memberId = $memberValue === '' ? null : filter_var($memberValue, FILTER_VALIDATE_INT);
        $quantity = filter_var($_POST['qty'] ?? null, FILTER_VALIDATE_FLOAT);
        $note = trim((string) ($_POST['keterangan'] ?? ''));

        if (!$packageId || $quantity === false || $quantity <= 0 || $quantity > 99999) {
            redirect('views/kasir/transactions.php', 'Paket dan jumlah yang valid wajib diisi.', 'danger');
        }
        if ($memberValue !== '' && !$memberId) {
            redirect('views/kasir/transactions.php', 'Member yang dipilih tidak valid.', 'danger');
        }
        if (strlen($note) > 2000) {
            redirect('views/kasir/transactions.php', 'Catatan maksimal 2.000 karakter.', 'danger');
        }

        $packageQuery = $pdo->prepare('SELECT id_paket, harga FROM tb_paket WHERE id_paket = ? LIMIT 1');
        $packageQuery->execute([$packageId]);
        $package = $packageQuery->fetch();
        if (!$package) {
            redirect('views/kasir/transactions.php', 'Paket tidak ditemukan.', 'danger');
        }

        if ($memberId !== null) {
            $memberQuery = $pdo->prepare('SELECT id_member FROM tb_member WHERE id_member = ? LIMIT 1');
            $memberQuery->execute([$memberId]);
            if (!$memberQuery->fetch()) {
                redirect('views/kasir/transactions.php', 'Member tidak ditemukan.', 'danger');
            }
        }

        $unitPrice = (float) $package['harga'];
        $total = round($unitPrice * (float) $quantity, 2);
        $transactionCode = 'TRX-' . date('ymdHis') . '-' . strtoupper(bin2hex(random_bytes(2)));

        try {
            $pdo->beginTransaction();
            $transactionQuery = $pdo->prepare(
                'INSERT INTO tb_transaksi
                    (id_outlet, id_user, id_member, kode_transaksi, tanggal_masuk, status, total_harga, keterangan)
                 VALUES (?, ?, ?, ?, CURDATE(), \'baru\', ?, ?)'
            );
            $transactionQuery->execute([
                $_SESSION['id_outlet'],
                $_SESSION['user_id'],
                $memberId,
                $transactionCode,
                $total,
                $note === '' ? null : $note,
            ]);

            $detailQuery = $pdo->prepare(
                'INSERT INTO tb_detail_transaksi (id_transaksi, id_paket, qty, harga_satuan, subtotal)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $detailQuery->execute([
                $pdo->lastInsertId(),
                $packageId,
                number_format((float) $quantity, 2, '.', ''),
                $unitPrice,
                $total,
            ]);
            $pdo->commit();
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[TRANSACTION ERROR] ' . $error->getMessage());
            redirect('views/kasir/transactions.php', 'Transaksi gagal disimpan. Periksa koneksi database.', 'danger');
        }

        redirect('views/kasir/transactions.php', 'Transaksi ' . $transactionCode . ' berhasil dibuat.', 'success');
    }

    if ($action === 'advance_status') {
        $transactionId = filter_var($_POST['id_transaksi'] ?? null, FILTER_VALIDATE_INT);
        $targetStatus = (string) ($_POST['status'] ?? '');
        if (!$transactionId) {
            redirect('views/kasir/transactions.php', 'Transaksi tidak valid.', 'danger');
        }

        $currentQuery = $pdo->prepare('SELECT status FROM tb_transaksi WHERE id_transaksi = ? LIMIT 1');
        $currentQuery->execute([$transactionId]);
        $currentStatus = $currentQuery->fetchColumn();
        if (!$currentStatus || ($nextStatus[$currentStatus] ?? null) !== $targetStatus) {
            redirect('views/kasir/transactions.php', 'Perubahan status tidak valid.', 'warning');
        }

        $updateQuery = $pdo->prepare(
            'UPDATE tb_transaksi
             SET status = ?,
                 tanggal_selesai = CASE WHEN ? = \'selesai\' THEN CURDATE() ELSE tanggal_selesai END,
                 tanggal_ambil = CASE WHEN ? = \'diambil\' THEN CURDATE() ELSE tanggal_ambil END
             WHERE id_transaksi = ?'
        );
        $updateQuery->execute([$targetStatus, $targetStatus, $targetStatus, $transactionId]);
        redirect('views/kasir/transactions.php', 'Status transaksi berhasil diperbarui.', 'success');
    }

    redirect('views/kasir/transactions.php', 'Aksi tidak dikenal.', 'warning');
}

$packages = $pdo->query('SELECT id_paket, nama_paket, jenis, harga FROM tb_paket ORDER BY nama_paket')->fetchAll();
$members = $pdo->query('SELECT id_member, nama, telepon FROM tb_member ORDER BY nama')->fetchAll();
$transactions = $pdo->query(
    "SELECT t.id_transaksi, t.kode_transaksi, t.tanggal_masuk, t.status, t.total_harga,
            t.tanggal_selesai, t.tanggal_ambil, m.nama AS nama_member, u.nama AS nama_kasir,
            (SELECT GROUP_CONCAT(CONCAT(p.nama_paket, ' x ', d.qty) SEPARATOR ', ')
             FROM tb_detail_transaksi d
             JOIN tb_paket p ON p.id_paket = d.id_paket
             WHERE d.id_transaksi = t.id_transaksi) AS rincian
     FROM tb_transaksi t
     LEFT JOIN tb_member m ON m.id_member = t.id_member
     JOIN tb_user u ON u.id_user = t.id_user
     ORDER BY t.created_at DESC
     LIMIT 50"
)->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Laundry</title>
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
                <h1 class="h3 mb-0">Transaksi laundry</h1>
            </div>
            <a class="btn btn-outline-secondary" href="<?= h(dashboardUrl()) ?>"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
        </div>

        <?php if ($_SESSION['role'] !== 'owner'): ?>
        <section class="card border-0 shadow-sm mb-4" aria-labelledby="new-transaction-title">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h2 class="h5 mb-0" id="new-transaction-title">Buat transaksi</h2>
            </div>
            <div class="card-body px-4 pb-4">
                <?php if (!$packages): ?>
                    <p class="text-muted mb-0">Belum ada paket. Minta admin menambahkan paket terlebih dahulu.</p>
                <?php else: ?>
                    <form method="post" class="row g-3">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="create">
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="id_member">Member <span class="text-muted">(opsional)</span></label>
                            <select class="form-select" id="id_member" name="id_member">
                                <option value="">Pelanggan umum</option>
                                <?php foreach ($members as $member): ?>
                                    <option value="<?= (int) $member['id_member'] ?>"><?= h($member['nama']) ?> · <?= h($member['telepon']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="id_paket">Paket</label>
                            <select class="form-select" id="id_paket" name="id_paket" required>
                                <option value="">Pilih paket</option>
                                <?php foreach ($packages as $package): ?>
                                    <option value="<?= (int) $package['id_paket'] ?>">
                                        <?= h($package['nama_paket']) ?> · <?= h(ucfirst($package['jenis'])) ?> · <?= h(rupiah($package['harga'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label" for="qty">Jumlah</label>
                            <input class="form-control" id="qty" name="qty" type="number" min="0.01" max="99999" step="0.01" value="1" required>
                            <div class="form-text">Pakai kg untuk kiloan, unit untuk satuan.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="keterangan">Catatan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="2" maxlength="2000" placeholder="Catatan khusus untuk cucian"></textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg me-1"></i>Simpan transaksi</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <section class="card border-0 shadow-sm" aria-labelledby="transaction-list-title">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0" id="transaction-list-title">Transaksi terbaru</h2>
                <span class="badge text-bg-light"><?= count($transactions) ?> terbaru</span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-4">Kode / tanggal</th><th>Pelanggan</th><th>Rincian</th><th>Total</th><th>Status</th><th class="pe-4">Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$transactions): ?>
                            <tr><td class="text-center text-muted py-5" colspan="6">Belum ada transaksi.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <?php $next = $nextStatus[$transaction['status']] ?? null; ?>
                            <tr>
                                <td class="ps-4"><strong><?= h($transaction['kode_transaksi']) ?></strong><br><small class="text-muted"><?= h($transaction['tanggal_masuk']) ?></small></td>
                                <td><?= h($transaction['nama_member'] ?: 'Pelanggan umum') ?></td>
                                <td><?= h($transaction['rincian'] ?: '-') ?></td>
                                <td class="text-nowrap"><?= h(rupiah($transaction['total_harga'])) ?></td>
                                <td><span class="badge text-bg-<?= $transaction['status'] === 'selesai' || $transaction['status'] === 'diambil' ? 'success' : ($transaction['status'] === 'proses' ? 'primary' : 'secondary') ?>"><?= h(transactionStatusLabel($transaction['status'])) ?></span></td>
                                <td class="pe-4">
                                    <?php if ($_SESSION['role'] === 'owner'): ?>
                                        <span class="text-muted small">Baca saja</span>
                                    <?php elseif ($next): ?>
                                        <form method="post">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="advance_status">
                                            <input type="hidden" name="id_transaksi" value="<?= (int) $transaction['id_transaksi'] ?>">
                                            <input type="hidden" name="status" value="<?= h($next) ?>">
                                            <button class="btn btn-sm btn-outline-primary text-nowrap" type="submit">Tandai <?= h(transactionStatusLabel($next)) ?></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">Selesai</span>
                                    <?php endif; ?>
                                </td>
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