<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:login.php");
    exit;
}

$email_sess = $_SESSION['user_email'];

$query = mysqli_query($koneksi, "SELECT * FROM MsUser WHERE UserEmail = '$email_sess'");
$d = mysqli_fetch_assoc($query);
$id_user = $d['UserID']; 

if (isset($_POST['update_profile'])) {
    
    $nama = $_POST['full-name'];
    $email = $_POST['email'];

    $update = mysqli_query($koneksi, "UPDATE MsUser SET UserName='$nama', UserEmail='$email' WHERE UserID='$id_user'");

    if ($update) {
        $_SESSION['user_nama'] = $nama;
        $_SESSION['user_email'] = $email;
        echo "<script>alert('Profil berhasil diubah!'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Gagal update database!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anginbnb - My Profile</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>

    <header>
        <div class="logo">anginbnb</div>
        <nav class="nav-center">
            <a href="index.php">Home</a>
            <a href="properties.php">Properties</a>
            <a href="mybookings.php">My Bookings</a>
        </nav>
        <div class="nav-right">
            <span style="font-weight: bold; margin-right: 10px;">
                Welcome, <?php echo $_SESSION['user_nama']; ?>
            </span>
            <a href="logout.php" style="color: #ff385c; font-weight: bold;">Logout</a>
        </div>
    </header>

    <main>
        <div class="page-title">
            <h1>My Profile</h1>
            <p>Manage your account settings and preferences</p>
        </div>

        <div class="profile-container">
            
            <section class="card">
                <h3>Profile Information</h3>
                <form action="" method="POST" id="form-profile">
                    <div class="input-group">
                        <label>Full Name</label>
                        <input type="text" name="full-name" value="<?php echo $d['UserName']; ?>" placeholder="Masukkan Nama">
                    </div>
                    <div class="input-group">
                        <label>Email Address</label>
                        <input type="text" id="email" name="email" value="<?php echo $d['UserEmail']; ?>" placeholder="Masukkan Email">
                    </div>
                    <button type="submit" name="update_profile" class="btn-primary">Save Changes</button>
                </form>
            </section>

            <section class="card">
                <h3>Change Password</h3>
                <form action="ganti_password.php" method="POST" id="form-password">
                    <div class="input-group">
                        <label>Current Password</label>
                        <input type="password" id="current-pass" name="old_pass" required>
                    </div>
                    <div class="input-group">
                        <label>New Password</label>
                        <input type="password" id="new-pass" name="new_pass" required>
                    </div>
                    <div class="input-group">
                        <label>Confirm New Password</label>
                        <input type="password" id="confirm-pass" name="confirm_pass" required>
                    </div>
                    <button type="submit" class="btn-primary">Update Password</button>
                </form>
            </section>

            <section class="card danger-zone">
                <h3 class="danger-title">Danger Zone</h3>
                <p class="danger-desc">Once you delete your account, there is no going back.</p>
                <a href="hapus_akun.php" onclick="return confirm('Yakin mau hapus akun?');">
                    <button type="button" class="btn-danger">Delete My Account</button>
                </a>
            </section>

        </div>
    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-brand">
                    <h3>Anginbnb</h3>
                    <p>Your home away from home. Discover unique places to stay around the world.</p>
                </div>
                
                <div class="footer-links">
                    <div class="link-col">
                        <h4>Support</h4>
                        <ul>
                            <li><a href="#">Help Center</a></li>
                            <li><a href="#">Safety Information</a></li>
                            <li><a href="#">Cancellation Options</a></li>
                            <li><a href="#">Contact Us</a></li>
                        </ul>
                    </div>
                    <div class="link-col">
                        <h4>Community</h4>
                        <ul>
                            <li><a href="#">Anginbnb Blog</a></li>
                            <li><a href="#">Host Resources</a></li>
                            <li><a href="#">Community Forum</a></li>
                            <li><a href="#">Refer Friends</a></li>
                        </ul>
                    </div>
                    <div class="link-col">
                        <h4>Company</h4>
                        <ul>
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Careers</a></li>
                            <li><a href="#">Press</a></li>
                            <li><a href="#">Investors</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="copyright">&copy; 2025 Anginbnb, Inc. All rights reserved.</div>
                <div class="legal-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

   <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formProfile = document.getElementById('form-profile');
            const emailInput = document.getElementById('email');

            if (formProfile) {
                formProfile.addEventListener('submit', function(e) {
                    const val = emailInput.value;
                    if (!val.includes('@') || !val.includes('.')) {
                        e.preventDefault(); 
                        alert("Format email salah! Harus pakai '@' dan ada titik '.'");
                    }
                });
            }

            const formPass = document.getElementById('form-password');
            if (formPass) {
                formPass.addEventListener('submit', function(e) {
                    const pass = document.getElementById('new-pass').value;
                    const confirm = document.getElementById('confirm-pass').value;

                    if (pass !== confirm) {
                        e.preventDefault();
                        alert("Password baru dan konfirmasi beda!");
                    } else if (pass.length < 8) {
                        e.preventDefault();
                        alert("Password minimal 8 huruf ya.");
                    }
                });
            }
        });
    </script>
</body>
</html>