<?php
session_start();
include 'conn.php';

$email = $_SESSION['user_email'];
$pass_lama = $_POST['old_pass'];
$pass_baru = $_POST['new_pass'];
$konfirmasi = $_POST['confirm_pass'];

if($pass_baru == $konfirmasi) {
    $cek = mysqli_query($koneksi, "SELECT * FROM MsUser WHERE UserEmail='$email' AND UserPassword='$pass_lama'");
    
    if(mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "UPDATE MsUser SET UserPassword='$pass_baru' WHERE UserEmail='$email'");
        echo "<script>alert('Password berhasil diganti!'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Password lama salah!'); window.location='profile.php';</script>";
    }
} else {
    echo "<script>alert('Konfirmasi password tidak cocok!'); window.location='profile.php';</script>";
}
?>