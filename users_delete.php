<?php
/* users/delete.php */

session_start();

/* 🔐 Check admin access */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /music_site/user/login.php');
    exit;
}

/* 🔍 Validate ID */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: users_index.php?msg=invalid');
    exit;
}

$id = (int) $_GET['id'];


if ($id === (int) $_SESSION['user_id']) {
    header('Location: users_index.php?msg=self_delete_not_allowed');
    exit;
}

/* 🔗 DB connection */
require_once '../../includes/db.php';

/* 🗑️ Delete user reviews first (avoid foreign key issues) */
mysqli_query($conn, "DELETE FROM reviews WHERE user_id = $id");

/* 🗑️ Delete user */
$result = mysqli_query($conn, "DELETE FROM users WHERE id = $id");

$redirect = $_SERVER['HTTP_REFERER'] ?? 'users_index.php?msg=deleted';
if ($result) {
    header("Location: $redirect");
} else {
    $redirectErr = $_SERVER['HTTP_REFERER'] ?? 'users_index.php?msg=error';
    header("Location: $redirectErr");
}

exit;
?>