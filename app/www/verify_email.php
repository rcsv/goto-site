<?php
require_once("../config/db_info.php");

session_start();

// check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// check if token is provided
if (empty($_GET['token'])) {
    header('Location: login.php');
    exit();
}

// connect to the database
$dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
$username = $db_user;
$password = $db_password;

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// verify the token
$token = $_GET['token'];

$stmt = $pdo->prepare('SELECT id FROM users WHERE token = ?');
$stmt->execute([$token]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: login.php');
    exit();
}

// update the user's email_verified_at field
$stmt = $pdo->prepare('UPDATE users SET email_verified_at = NOW(), token = NULL WHERE id = ?');
$stmt->execute([$user['id']]);

// redirect to the login page
header('Location: login.php');
exit();
