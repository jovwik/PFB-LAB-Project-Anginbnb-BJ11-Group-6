<?php
session_start();
include 'conn.php';

$nama = $_POST['full-name']; 
$email = $_POST['email'];
$pass = $_POST['password']; 

$query = "INSERT INTO MsUser (UserName, UserEmail, UserPassword, UserRole) VALUES ('$nama', '$email', '$pass', 'member')";
mysqli_query($koneksi, $query);

$_SESSION['user_nama'] = $nama;
$_SESSION['user_email'] = $email;
$_SESSION['status'] = "login";

header("location:profile.php");
?>