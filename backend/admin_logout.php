<?php
/**
 * EditPro - Admin Logout Handler
 */

// Start session
session_start();

// Destroy session and redirect to login
session_destroy();
header('Location: admin_login.php');
exit;

