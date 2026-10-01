<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/helpers/laundry.php';

requireRole('kasir', 'admin', 'owner');

$transactionId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$transactionId) {
    redirect(dashboardUrl(), 'Transaksi tidak ditemukan.', 'warning');
}

$pdo = getDB();
$headerQuery = $pdo->prepare(
    'SELECT t.id_transaksi, t.kode_transaksi, t.tanggal_masuk, t.tanggal_selesai,
            t.tanggal_ambil, t.status, t.total_harga, t.keterangan,
            m.nama AS nama_member, m.telepon AS telepon_member,
            u.nama AS nama_kasir, o.nama_outlet, o.alamat AS alamat_outlet, o.telepon AS telepon_outlet
     FROM tb_transaksi t
     LEFT JOIN tb_member m ON m.id_member = t.id_member
     JOIN tb_user u ON u.id_user = t.id_user
     JOIN tb_outlet o ON o.id_outlet = t.id_outlet
     WHERE t.id_transaksi = ?
     LIMIT 1'
);
$headerQuery->execute([$transactionId]);
$transaction = $headerQuery->fetch();
if (!$transaction) {
    redirect(dashboardUrl(), 'Transaksi tidak ditemukan.', 'warning');
}

$detailQuery = $pdo->prepare(
    'SELECT p.nama_paket, p.jenis, d.qty, d.harga_satuan, d.subtotal
     FROM tb_detail_transaksi d
     JOIN tb_paket p ON p.id_paket = d.id_paket
     WHERE d.id_transaksi = ?
     ORDER BY d.id_detail'
);
$detailQuery->execute([$transactionId]);
$details = $detailQuery->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk <?= h($transaction['kode_transaksi']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #eef1f3; color: #17212b; }
        .receipt { max-width: 760px; margin: 32px auto; background: #fff; border: 1px solid #dce2e7; }
        .receipt-header { border-bottom: 2px solid #17212b; }
        .receipt-total { border-top: 2px solid #17212b; }
        @media print {
            @page { margin: 12mm; }
            body { background: #fff; }
            .receipt { max-width: none; margin: 0; border: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <main class="receipt p-4 p-md-5">
        <div class="no-print d-flex justify-content-between mb-4">
            <a class="btn btn-outline-secondary" href="<?= h(BASE_URL) ?>views/kasir/transactions.php"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
            <button class="btn btn-primary" type="button" onclick="window.print()"><i class="bi bi-printer me-1"></i>Cetak / Simpan PDF</button>
        </div>

        <header class="receipt-header pb-3 mb-4 d-flex flex-wrap justify-content-between gap-3">
            <div>
                <p class="text-uppercase small fw-semibold text-muted mb-1">Struk laundry</p>
                <h1 class="h3 mb-1"><?= h($transaction['nama_outlet']) ?></h1>
                <div><?= nl2br(h($transaction['alamat_outlet'])) ?></div>
                <div><?= h($transaction['telepon_outlet']) ?></div>
            </div>
            <div class="text-md-end">
                <div class="fw-semibold"><?= h($transaction['kode_transaksi']) ?></div>
                <div>Tanggal masuk: <?= h($transaction['tanggal_masuk']) ?></div>
                <div>Status: <?= h(transactionStatusLabel($transaction['status'])) ?></div>
            </div>
        </header>

        <section class="row g-3 mb-4">
            <div class="col-12 col-sm-6">
                <div class="small text-muted">Pelanggan</div>
                <div class="fw-semibold"><?= h($transaction['nama_member'] ?: 'Pelanggan umum') ?></div>
                <?php if ($transaction['telepon_member']): ?><div><?= h($transaction['telepon_member']) ?></div><?php endif; ?>
            </div>
            <div class="col-12 col-sm-6 text-sm-end">
                <div class="small text-muted">Kasir</div>
                <div class="fw-semibold"><?= h($transaction['nama_kasir']) ?></div>
                <?php if ($transaction['tanggal_selesai']): ?><div class="small">Selesai: <?= h($transaction['tanggal_selesai']) ?></div><?php endif; ?>
                <?php if ($transaction['tanggal_ambil']): ?><div class="small">Diambil: <?= h($transaction['tanggal_ambil']) ?></div><?php endif; ?>
            </div>
        </section>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Paket</th><th class="text-end">Jumlah</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                    <?php foreach ($details as $detail): ?>
                        <tr>
                            <td><?= h($detail['nama_paket']) ?><div class="small text-muted"><?= h(ucfirst($detail['jenis'])) ?></div></td>
                            <td class="text-end text-nowrap"><?= h($detail['qty']) ?> <?= $detail['jenis'] === 'kiloan' ? 'kg' : 'unit' ?></td>
                            <td class="text-end text-nowrap"><?= h(rupiah($detail['harga_satuan'])) ?></td>
                            <td class="text-end text-nowrap"><?= h(rupiah($detail['subtotal'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot><tr class="receipt-total"><th colspan="3" class="text-end pt-3">Total</th><th class="text-end pt-3 text-nowrap"><?= h(rupiah($transaction['total_harga'])) ?></th></tr></tfoot>
            </table>
        </div>

        <?php if ($transaction['keterangan']): ?>
            <section class="border-top pt-3 mt-4">
                <div class="small text-muted">Catatan</div>
                <div><?= nl2br(h($transaction['keterangan'])) ?></div>
            </section>
        <?php endif; ?>

        <footer class="text-center small text-muted border-top pt-3 mt-5">Terima kasih telah menggunakan layanan kami.</footer>
    </main>
</body>
</html>