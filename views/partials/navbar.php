<?php
$role = $_SESSION['role'] ?? '';
$navigationLinks = match ($role) {
    'admin' => [
        ['label' => 'Dashboard', 'url' => dashboardUrl()],
        ['label' => 'Member & paket', 'url' => BASE_URL . 'views/admin/catalog.php'],
        ['label' => 'Transaksi', 'url' => BASE_URL . 'views/kasir/transactions.php'],
    ],
    'kasir' => [
        ['label' => 'Dashboard', 'url' => dashboardUrl()],
        ['label' => 'Transaksi', 'url' => BASE_URL . 'views/kasir/transactions.php'],
    ],
    default => [
        ['label' => 'Dashboard', 'url' => dashboardUrl()],
        ['label' => 'Transaksi', 'url' => BASE_URL . 'views/kasir/transactions.php'],
    ],
};
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3">
    <a class="navbar-brand fw-bold" href="<?= h(dashboardUrl()) ?>">
        <i class="bi bi-basket2-fill me-1"></i>Aplikasi Laundry
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Buka navigasi">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNavigation">
        <ul class="navbar-nav me-auto">
            <?php foreach ($navigationLinks as $link): ?>
                <li class="nav-item"><a class="nav-link" href="<?= h($link['url']) ?>"><?= h($link['label']) ?></a></li>
            <?php endforeach; ?>
        </ul>
        <div class="d-flex align-items-center gap-3 py-2 py-lg-0">
            <span class="text-white"><i class="bi bi-person-circle me-1"></i><?= h($_SESSION['nama']) ?> <span class="badge bg-light text-primary ms-1"><?= h($role) ?></span></span>
            <a href="<?= h(BASE_URL) ?>views/auth/logout.php" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
        </div>
    </div>
</nav>
