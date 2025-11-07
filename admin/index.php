<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

// Redirect if already logged in
if (!isAdminLoggedIn()) {
    redirect(ADMIN_URL . '/login.php');
}else{
    redirect(ADMIN_URL . '/dashboard.php');
}

?>