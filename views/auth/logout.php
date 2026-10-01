<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/helpers/auth.php';

logoutUser();
redirect('views/auth/login.php', 'Anda telah berhasil logout.', 'info');
