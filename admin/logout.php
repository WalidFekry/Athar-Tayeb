<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

destroyAdminSession();
redirect(ADMIN_URL . '/login.php');
