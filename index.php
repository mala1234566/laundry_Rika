<?php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/app/helpers/auth.php';

startSession();

if (isLoggedIn()) {
    header('Location: ' . dashboardUrl());
} else {
    header('Location: ' . BASE_URL . 'views/auth/login.php');
}
exit;
