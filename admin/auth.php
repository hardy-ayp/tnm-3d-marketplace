<?php
/**
 * TNM Marketplace — Admin Authentication Guard & Helper
 */
require_once __DIR__ . '/../config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentAdmin = currentAdmin();
