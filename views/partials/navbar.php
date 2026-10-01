<?php
// views/partials/navbar.php
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3">
    <span class="navbar-brand fw-bold">
        <i class="bi bi-basket2-fill me-1"></i> Aplikasi Laundry
    </span>
    <div class="ms-auto d-flex align-items-center gap-3">
        <span class="text-white">
            <i class="bi bi-person-circle me-1"></i>
            <?= htmlspecialchars($_SESSION['nama']) ?>
            <span class="badge bg-light text-primary ms-1"><?= htmlspecialchars($_SESSION['role']) ?></span>
        </span>
        <a href="<?= BASE_URL ?>views/auth/logout.php" class="btn btn-sm btn-outline-light">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
    </div>
</nav>
