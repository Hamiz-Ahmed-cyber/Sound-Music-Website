<?php
/* reviews/reviews_delete.php */
session_start();
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='admin'){header('Location:/music_site/user/login.php');exit;}
require_once '../../includes/db.php';
if(!isset($_GET['id'])||!is_numeric($_GET['id'])){header('Location:reviews_index.php');exit;}

$id = (int)$_GET['id'];
mysqli_query($conn, "DELETE FROM reviews WHERE id=$id");

$redirect = $_SERVER['HTTP_REFERER'] ?? 'reviews_index.php?msg=deleted';
header("Location: $redirect");
exit;
