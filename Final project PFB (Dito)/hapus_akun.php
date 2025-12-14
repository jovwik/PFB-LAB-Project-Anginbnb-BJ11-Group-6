<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:login.php");
    exit;
}

$email = $_SESSION['user_email'];

$query = "DELETE FROM MsUser WHERE UserEmail = '$email'";
$delete = mysqli_query($koneksi, $query);

if ($delete) {
    session_unset();
    session_destroy();

    if (isset($_COOKIE['id'])) {
        setcookie('id', '', time() - 3600);
    }
    if (isset($_COOKIE['key'])) {
        setcookie('key', '', time() - 3600);
    }

    
    echo "<script>alert('Akun berhasil dihapus. Sampai jumpa!'); window.location='register.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus akun.'); window.location='profile.php';</script>";
}
?>