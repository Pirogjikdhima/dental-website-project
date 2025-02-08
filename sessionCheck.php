<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: signin.html");
    exit();
}
$current_page = basename($_SERVER['PHP_SELF']);

switch ($_SESSION['role']) {
    case 'ADMIN':
        if ($current_page !== 'admin.php') {
            header("Location: admin.php");
            exit();
        }
        break;
    case 'USER':
        if ($current_page !== 'user.php') {
            header("Location: user.php");
            exit();
        }
        break;
    default:
        header("Location: signin.html");
        exit();
}
