<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/helpers/laundry.php';

requireRole('admin');

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        redirect('views/admin/catalog.php', 'Form sudah kedaluwarsa. Silakan coba lagi.', 'warning');
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'save_member') {
        $memberId = filter_var($_POST['id_member'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $name = trim((string) ($_POST['nama'] ?? ''));
        $phone = trim((string) ($_POST['telepon'] ?? ''));
        $address = trim((string) ($_POST['alamat'] ?? ''));
        if ($name === '' || $phone === '' || strlen($name) > 100 || strlen($phone) > 20 || strlen($address) > 5000) {
            redirect('views/admin/catalog.php', 'Nama dan telepon wajib diisi. Periksa juga panjang data.', 'danger');
        }

        if ($memberId) {
            $query = $pdo->prepare('UPDATE tb_member SET nama = ?, telepon = ?, alamat = ? WHERE id_member = ?');
            $query->execute([$name, $phone, $address === '' ? null : $address, $memberId]);
            redirect('views/admin/catalog.php', 'Data member berhasil diperbarui.', 'success');
        }

        $query = $pdo->prepare('INSERT INTO tb_member (nama, telepon, alamat) VALUES (?, ?, ?)');
        $query->execute([$name, $phone, $address === '' ? null : $address]);
        redirect('views/admin/catalog.php', 'Member baru berhasil ditambahkan.', 'success');
    }

    if ($action === 'save_package') {
        $packageId = filter_var($_POST['id_paket'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $name = trim((string) ($_POST['nama_paket'] ?? ''));
        $type = (string) ($_POST['jenis'] ?? '');
        $price = filter_var($_POST['harga'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($name === '' || strlen($name) > 100 || !in_array($type, ['kiloan', 'satuan'], true) || $price === false || $price <= 0 || $price > 99999999) {
            redirect('views/admin/catalog.php', 'Nama, jenis, dan harga paket harus valid.', 'danger');
        }

        if ($packageId) {
            $query = $pdo->prepare('UPDATE tb_paket SET nama_paket = ?, jenis = ?, harga = ? WHERE id_paket = ?');
            $query->execute([$name, $type, $price, $packageId]);
            redirect('views/admin/catalog.php', 'Paket berhasil diperbarui.', 'success');
        }

        $query = $pdo->prepare('INSERT INTO tb_paket (nama_paket, jenis, harga) VALUES (?, ?, ?)');
        $query->execute([$name, $type, $price]);
        redirect('views/admin/catalog.php', 'Paket baru berhasil ditambahkan.', 'success');
    }

    redirect('views/admin/catalog.php', 'Aksi tidak dikenal.', 'warning');
}

$editMember = null;
$editMemberId = filter_var($_GET['edit_member'] ?? null, FILTER_VALIDATE_INT);
if ($editMemberId) {
    $query = $pdo->prepare('SELECT id_member, nama, telepon, alamat FROM tb_member WHERE id_member = ? LIMIT 1');
    $query->execute([$editMemberId]);
    $editMember = $query->fetch() ?: null;
}

$editPackage = null;
$editPackageId = filter_var($_GET['edit_package'] ?? null, FILTER_VALIDATE_INT);
if ($editPackageId) {
    $query = $pdo->prepare('SELECT id_paket, nama_paket, jenis, harga FROM tb_paket WHERE id_paket = ? LIMIT 1');
    $query->execute([$editPackageId]);
    $editPackage = $query->fetch() ?: null;
}

$members = $pdo->query('SELECT id_member, nama, telepon, alamat FROM tb_member ORDER BY nama LIMIT 200')->fetchAll();
$packages = $pdo->query('SELECT id_paket, nama_paket, jenis, harga FROM tb_paket ORDER BY nama_paket')->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Member & Paket</title>
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
                <p class="text-uppercase text-muted small fw-semibold mb-1">Pengaturan</p>
                <h1 class="h3 mb-0">Member & paket laundry</h1>
            </div>
            <a class="btn btn-outline-secondary" href="<?= h(dashboardUrl()) ?>"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
        </div>

        <div class="row g-4">
            <section class="col-12 col-xl-6" aria-labelledby="members-title">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h2 class="h5 mb-0" id="members-title">Member</h2>
                    </div>
                    <div class="card-body px-4">
                        <form method="post" class="row g-3 mb-4">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="save_member">
                            <input type="hidden" name="id_member" value="<?= (int) ($editMember['id_member'] ?? 0) ?>">
                            <div class="col-12">
                                <label class="form-label" for="member-name">Nama</label>
                                <input class="form-control" id="member-name" name="nama" maxlength="100" value="<?= h($editMember['nama'] ?? '') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="member-phone">Telepon</label>
                                <input class="form-control" id="member-phone" name="telepon" maxlength="20" value="<?= h($editMember['telepon'] ?? '') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="member-address">Alamat</label>
                                <textarea class="form-control" id="member-address" name="alamat" rows="2" maxlength="5000"><?= h($editMember['alamat'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button class="btn btn-primary" type="submit"><?= $editMember ? 'Simpan perubahan' : 'Tambah member' ?></button>
                                <?php if ($editMember): ?><a class="btn btn-outline-secondary" href="catalog.php">Batal</a><?php endif; ?>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light"><tr><th>Nama</th><th>Telepon</th><th></th></tr></thead>
                                <tbody>
                                    <?php if (!$members): ?><tr><td class="text-muted text-center py-3" colspan="3">Belum ada member.</td></tr><?php endif; ?>
                                    <?php foreach ($members as $member): ?>
                                        <tr>
                                            <td><?= h($member['nama']) ?></td>
                                            <td><?= h($member['telepon']) ?></td>
                                            <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="?edit_member=<?= (int) $member['id_member'] ?>#members-title" aria-label="Edit <?= h($member['nama']) ?>"><i class="bi bi-pencil"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <section class="col-12 col-xl-6" aria-labelledby="packages-title">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h2 class="h5 mb-0" id="packages-title">Paket</h2>
                    </div>
                    <div class="card-body px-4">
                        <form method="post" class="row g-3 mb-4">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="save_package">
                            <input type="hidden" name="id_paket" value="<?= (int) ($editPackage['id_paket'] ?? 0) ?>">
                            <div class="col-12">
                                <label class="form-label" for="package-name">Nama paket</label>
                                <input class="form-control" id="package-name" name="nama_paket" maxlength="100" value="<?= h($editPackage['nama_paket'] ?? '') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="package-type">Jenis</label>
                                <select class="form-select" id="package-type" name="jenis" required>
                                    <option value="kiloan" <?= ($editPackage['jenis'] ?? 'kiloan') === 'kiloan' ? 'selected' : '' ?>>Kiloan</option>
                                    <option value="satuan" <?= ($editPackage['jenis'] ?? '') === 'satuan' ? 'selected' : '' ?>>Satuan</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="package-price">Harga</label>
                                <input class="form-control" id="package-price" name="harga" type="number" min="1" max="99999999" step="1" value="<?= h($editPackage['harga'] ?? '') ?>" required>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button class="btn btn-primary" type="submit"><?= $editPackage ? 'Simpan perubahan' : 'Tambah paket' ?></button>
                                <?php if ($editPackage): ?><a class="btn btn-outline-secondary" href="catalog.php">Batal</a><?php endif; ?>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light"><tr><th>Paket</th><th>Jenis</th><th>Harga</th><th></th></tr></thead>
                                <tbody>
                                    <?php foreach ($packages as $package): ?>
                                        <tr>
                                            <td><?= h($package['nama_paket']) ?></td>
                                            <td><?= h(ucfirst($package['jenis'])) ?></td>
                                            <td class="text-nowrap"><?= h(rupiah($package['harga'])) ?></td>
                                            <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="?edit_package=<?= (int) $package['id_paket'] ?>#packages-title" aria-label="Edit <?= h($package['nama_paket']) ?>"><i class="bi bi-pencil"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>