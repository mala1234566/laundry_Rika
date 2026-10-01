<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/auth.php';

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string
{
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrfToken()) . '">';
}

function verifyCsrfToken(): bool
{
    startSession();
    $submittedToken = $_POST['csrf_token'] ?? '';
    return is_string($submittedToken)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $submittedToken);
}

function rupiah(int|float|string $amount): string
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

function transactionStatusLabel(string $status): string
{
    return match ($status) {
        'baru' => 'Baru',
        'proses' => 'Diproses',
        'selesai' => 'Selesai',
        'diambil' => 'Diambil',
        default => 'Tidak diketahui',
    };
}