<?php
/**
 * Session Management
 * Safely initializes sessions with security settings
 */

// Session security configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

// Enable secure cookies if HTTPS is available
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set session name and lifetime
    session_name(SESSION_NAME);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    
    // Start the session
    session_start();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerate every 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

/**
 * Check if user is logged in as admin
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Require admin authentication
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

/**
 * Set admin session after successful login
 */
function setAdminSession($adminId, $username, $role = 'admin') {
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $adminId;
    $_SESSION['admin_username'] = $username;
    $_SESSION['admin_role'] = $role;
    $_SESSION['admin_login_time'] = time();
}

/**
 * Destroy admin session
 */
function destroyAdminSession() {
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}
