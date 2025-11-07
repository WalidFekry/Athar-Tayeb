<?php
/**
 * Maintenance Mode Check
 * Include this at the top of public pages to check if maintenance mode is enabled
 */

// Check if maintenance mode is enabled
$stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
$maintenanceMode = $stmt->fetchColumn();

// If maintenance mode is ON and user is not an admin
if ($maintenanceMode == '1' && !isAdminLoggedIn()) {
    header('Location: ' . BASE_URL . '/maintenance.php');
    exit;
}
