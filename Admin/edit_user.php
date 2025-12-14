<?php
session_start();
require 'config.php';



$_SESSION['role'] = 'admin';


$user_id = $_GET['id'] ?? 1;

$stmt = $conn->prepare("SELECT UserID, UserName, UserEmail, UserRole FROM msuser WHERE UserID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();


$user = $stmt->get_result()->fetch_assoc();

$success = '';
if (isset($_POST['update']) && $_SESSION['role'] === 'admin') {
    $newRole = $_POST['role'];

    if ($newRole != '') {
        $update = $conn->prepare("UPDATE msuser SET UserRole = ? WHERE UserID = ?");
        $update->bind_param("si", $newRole, $user_id);
        $update->execute();
        $success = "User role updated successfully.";
        
        $user['UserRole'] = $newRole;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="edit_user.css">
</head>
<body>

<header class="navbar">
    <div class="logo">anginbnb</div>
    <nav>
        <a href="#">Manage Users</a>
        <a href="#">Manage Properties</a>
        <a href="#">Payment Types</a>
        <a href="#">Categories</a>
        <a href="#">Profile</a>
        <a href="#">Logout</a>
    </nav>
    <span class="welcome">Welcome, admin</span>
</header>

<div class="container">
    <h1>User Details</h1>
    <p class="subtitle">View and manage user information</p>

    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" class="card">
        <div class="row">
            <label>User ID</label>
            <span>#<?= $user['UserID'] ?></span>
        </div>

        <div class="row">
            <label>Name</label>
            <span><?= $user['UserName'] ?></span>
        </div>

        <div class="row">
            <label>Email</label>
            <span><?= $user['UserEmail'] ?></span>
        </div>

        <div class="row">
            <label>Role</label>

            <?php if ($_SESSION['role'] === 'admin'): ?>
                <select name="role" required>
                    <option value="">-- Choose Role --</option>
                    <option value="member" <?= $user['UserRole']=='member'?'selected':'' ?>>Member</option>
                    <option value="admin" <?= $user['UserRole']=='admin'?'selected':'' ?>>Admin</option>
                </select>
            <?php else: ?>
                <span><?= ucfirst($user['UserRole']) ?></span>
            <?php endif; ?>
        </div>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <button name="update" class="btn-primary">Update Role</button>
        <?php endif; ?>
    </form>

    <a href="users.php" class="btn-secondary">Back to Users</a>
</div>

<footer>
    <div class="footer-grid">
        <div>
            <h4>Anginbnb</h4>
            <p>Your home away from home.</p>
        </div>
        <div>
            <h4>Support</h4>
            <p>Help Center</p>
            <p>Contact Us</p>
        </div>
        <div>
            <h4>Community</h4>
            <p>Forum</p>
            <p>Refer Friends</p>
        </div>
        <div>
            <h4>Company</h4>
            <p>About Us</p>
            <p>Careers</p>
        </div>
    </div>
    <p class="copyright">© 2025 Anginbnb</p>
</footer>

</body>
</html>