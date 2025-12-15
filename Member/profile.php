<?php
session_start();
require 'config.php'; 

$user_id = $_SESSION['UserID'] ?? null;

$success_message = '';
$error_message = '';
$profile_errors = [];
$password_errors = [];
$user_data = [];
$is_logged_in = false; 

function fetch_user_data($conn, $user_id) {
    $stmt = $conn->prepare("SELECT UserID, UserName, UserEmail, UserPassword, UserRole FROM msuser WHERE UserID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

if ($user_id) {
    $user_data = fetch_user_data($conn, $user_id);
    if ($user_data) {
        $is_logged_in = true;
    } else {
        session_destroy();
    }
}

if ($is_logged_in && isset($_POST['update_profile'])) {
    $name = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($name)) {
        $profile_errors['fullname'] = 'Full Name must be filled.';
    } elseif (strlen($name) > 100) {
        $profile_errors['fullname'] = 'Full Name cannot exceed 100 characters.';
    }
    
    if (empty($email)) {
        $profile_errors['email'] = 'Email Address must be filled.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profile_errors['email'] = 'Email Address format is invalid.';
    } elseif (strlen($email) > 100) {
        $profile_errors['email'] = 'Email Address cannot exceed 100 characters.';
    }

    if (empty($profile_errors['email'])) {
        $stmt = $conn->prepare("SELECT UserID FROM msuser WHERE UserEmail = ? AND UserID != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $profile_errors['email'] = 'This email address is already registered.';
        }
        $stmt->close();
    }
    
    if (empty($profile_errors)) {
        $update_stmt = $conn->prepare("UPDATE msuser SET UserName = ?, UserEmail = ? WHERE UserID = ?");
        $update_stmt->bind_param("ssi", $name, $email, $user_id);

        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully.";
            $user_data = fetch_user_data($conn, $user_id);
            $_SESSION['UserName'] = $user_data['UserName'];
        } else {
            $error_message = 'Failed to update profile: ' . $conn->error;
        }
        $update_stmt->close();
    } else {
        $error_message = 'Please correct the errors in the profile form.';
    }
}

if ($is_logged_in && isset($_POST['update_password'])) {
    $current_pass = $_POST['current_pass'] ?? '';
    $new_pass = $_POST['new_pass'] ?? '';
    $confirm_pass = $_POST['confirm_pass'] ?? '';

    if (empty($current_pass)) {
        $password_errors['current_pass'] = 'Current Password must be filled.';
    } elseif ($current_pass !== $user_data['UserPassword']) {
        $password_errors['current_pass'] = 'Current Password is incorrect.';
    }

    if (empty($new_pass)) {
        $password_errors['new_pass'] = 'New Password must be filled.';
    } elseif (strlen($new_pass) < 8) {
        $password_errors['new_pass'] = 'New Password must be at least 8 characters long.';
    } elseif (strlen($new_pass) > 20) {
        $password_errors['new_pass'] = 'New Password cannot exceed 20 characters.';
    }

    if ($new_pass !== $confirm_pass) {
        $password_errors['confirm_pass'] = 'New Password and Confirm New Password must match.';
    }
    
    if (empty($password_errors)) {
        $update_stmt = $conn->prepare("UPDATE msuser SET UserPassword = ? WHERE UserID = ?");
        $update_stmt->bind_param("si", $new_pass, $user_id);

        if ($update_stmt->execute()) {
            $success_message = "Password updated successfully.";
            $user_data = fetch_user_data($conn, $user_id); 
        } else {
            $error_message = 'Failed to update password: ' . $conn->error;
        }
        $update_stmt->close();
    } else {
         $error_message = 'Please correct the errors in the password form.';
    }
}

if ($is_logged_in && isset($_GET['delete_account']) && $_GET['delete_account'] === 'confirm') {
    $conn->begin_transaction();
    $delete_success = false;

    try {
        $delete_transactions_stmt = $conn->prepare("DELETE FROM mstransaction WHERE UserID = ?");
        $delete_transactions_stmt->bind_param("i", $user_id);
        $delete_transactions_stmt->execute();
        $delete_transactions_stmt->close();

        $delete_user_stmt = $conn->prepare("DELETE FROM msuser WHERE UserID = ?");
        $delete_user_stmt->bind_param("i", $user_id);
        $delete_user_stmt->execute();
        $delete_user_stmt->close();

        $conn->commit();
        $delete_success = true;

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
    }

    if ($delete_success) {
        session_destroy();
        if (isset($_COOKIE['remember_user_id'])) {
            setcookie('remember_user_id', '', time() - 3600, "/");
        }
        header("Location: login.php?deleted=true");
        exit();
    } else {
        $error_message = "Failed to delete account due to a server error.";
    }
}
$conn->close();
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

        <nav class="navcent">
            <a href="index.php">Home</a>
            <a href="properties.php">Properties</a>
            <?php if ($is_logged_in): ?>
                <a href="my_bookings.php">My Bookings</a>
                <a href="profile.php" class="active">Profile</a>
                <a href="logout.php">Logout</a> <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
                
        <div class="navkanan">
            <span class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['UserName'] ?? 'Guest') ?></span>
        </div>
    </header>
    
    

    <main>
        <div class="judulhalaman">
            <h1>My Profile</h1>
            <p>Manage your account settings and preferences</p>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="message success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="profcont">

        <?php if ($is_logged_in): ?>
            
            <section class="card">
                <h3>Profile Information</h3>
                <form method="POST">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="inpgrup">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($_POST['fullname'] ?? $user_data['UserName']) ?>" required>
                        <?php if (isset($profile_errors['fullname'])): ?><span class="error-text"><?= $profile_errors['fullname'] ?></span><?php endif; ?>
                    </div>
                    <div class="inpgrup">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $user_data['UserEmail']) ?>" required>
                        <?php if (isset($profile_errors['email'])): ?><span class="error-text"><?= $profile_errors['email'] ?></span><?php endif; ?>
                    </div>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </form>
            </section>

            <section class="card">
                <h3>Change Password</h3>
                <form method="POST">
                    <input type="hidden" name="update_password" value="1">
                    <div class="inpgrup">
                        <label for="current-pass">Current Password</label>
                        <input type="password" id="current-pass" name="current_pass" required>
                        <?php if (isset($password_errors['current_pass'])): ?><span class="error-text"><?= $password_errors['current_pass'] ?></span><?php endif; ?>
                    </div>
                    <div class="inpgrup">
                        <label for="new-pass">New Password</label>
                        <input type="password" id="new-pass" name="new_pass" required>
                        <?php if (isset($password_errors['new_pass'])): ?><span class="error-text"><?= $password_errors['new_pass'] ?></span><?php endif; ?>
                    </div>
                    <div class="inpgrup">
                        <label for="confirm-pass">Confirm New Password</label>
                        <input type="password" id="confirm-pass" name="confirm_pass" required>
                        <?php if (isset($password_errors['confirm_pass'])): ?><span class="error-text"><?= $password_errors['confirm_pass'] ?></span><?php endif; ?>
                    </div>
                    <button type="submit" class="btn-primary">Update Password</button>
                </form>
            </section>

            <section class="card danger-zone">
                <h3 class="danger-title">Danger Zone</h3>
                <p class="danger-desc">Once you delete your account, there is no going back. Please be certain.</p>
                <button type="button" class="btn-danger" id="delete-account-btn">Delete My Account</button>
            </section>

        <?php else: ?>
            <section class="card login-prompt">
                <h3 class="danger-title" style="color:#ff4d6d;">Login Required</h3>
                <p>You must be logged in to view your profile settings.</p>
                <a href="login.php" class="btn-primary" style="display:inline-block; text-decoration: none;">Go to Login Page</a>
            </section>
        <?php endif; ?>

        </div>
    </main>

    <footer>
        <div class="footcont">
            <div>
                <h4>Anginbnb</h4>
                <p>Your home away from home. Discover unique places to stay around the world.</p>
            </div>
            <div>
                <h4>Support</h4>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Safety Information</a></li>
                    <li><a href="#">Cancellation Options</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            <div>
                <h4>Community</h4>
                <ul>
                    <li><a href="#">Anginbnb Blog</a></li>
                    <li><a href="#">Host Resources</a></li>
                    <li><a href="#">Community Forum</a></li>
                    <li><a href="#">Refer Friends</a></li>
                </ul>
            </div>
            <div>
                <h4>Company</h4>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Press</a></li>
                    <li><a href="#">Investors</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <div>© 2025 Anginbnb, Inc. All rights reserved.</div>
            <div class="rightside">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookies Policy</a>
            </div>
        </div>
    </footer>

    <?php if ($is_logged_in): ?>
    <script>
        document.getElementById('delete-account-btn').addEventListener('click', function() {
            if (confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.')) {
                window.location.href = 'profile.php?delete_account=confirm';
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>