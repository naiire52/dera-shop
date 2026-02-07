<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dera_shop';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

function is_admin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function is_customer_logged_in() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'customer';
}

function require_customer_login() {
    if (!is_customer_logged_in()) {
        $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit;
    }
}
?>
