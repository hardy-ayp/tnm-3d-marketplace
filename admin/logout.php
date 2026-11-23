<?php
/**
 * 3D Apparel Marketplace — Standalone Admin Logout Handler
 */

require_once __DIR__ . '/../config.php';

// Clear admin session
if (isset($_SESSION['tnm_admin_user'])) {
    unset($_SESSION['tnm_admin_user']);
}
if (isset($_SESSION['tnm_blog_auth'])) {
    unset($_SESSION['tnm_blog_auth']);
}

// Redirect cleanly to login page
header('Location: login.php?msg=logged_out');
exit;
