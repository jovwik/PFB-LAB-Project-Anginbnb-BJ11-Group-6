<?php
session_start();
require 'config.php'; 

$error_message = '';
$old_email = '';
$remember_me_checked = false;


if (isset($_SESSION['UserID'])) {
    header("Location: index.php");
    exit();
}


if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    
    $old_email = htmlspecialchars($email);
    $remember_me_checked = $remember_me;

    

    if (empty($email)) {
        $error_message = "Email address must be filled.";
    } elseif (empty($password)) {
        $error_message = "Password must be filled.";
    } else {
        
        $stmt = $conn->prepare("SELECT UserID, UserPassword, UserName FROM msuser WHERE UserEmail = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            if ($password === $user['UserPassword']) {
                

                
                $_SESSION['UserID'] = $user['UserID'];
                $_SESSION['UserName'] = $user['UserName'];
                
               
                if ($remember_me) {
                    
                    $expiration = time() + (7 * 24 * 3600); 
                    setcookie('remember_user_id', $user['UserID'], $expiration, "/");
                } else {
                    
                    if (isset($_COOKIE['remember_user_id'])) {
                        setcookie('remember_user_id', '', time() - 3600, "/");
                    }
                }

                
                header("Location: index.php"); 
                exit();
            } else {
                
                $error_message = "Incorrect email or password. Please try again.";
            }
        } else {
            
            $error_message = "Incorrect email or password. Please try again.";
        }
    }
}


if (!isset($_SESSION['UserID']) && isset($_COOKIE['remember_user_id'])) {
    $cookie_user_id = (int)$_COOKIE['remember_user_id'];
    
    
    $stmt = $conn->prepare("SELECT UserID, UserName FROM msuser WHERE UserID = ?");
    $stmt->bind_param("i", $cookie_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $_SESSION['UserID'] = $user['UserID'];
        $_SESSION['UserName'] = $user['UserName'];
        header("Location: index.php");
        exit();
    } else {
        
        setcookie('remember_user_id', '', time() - 3600, "/");
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Anginbnb</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="manage_properties.css"> 
</head>
<body>

<header class="navbar">
    <div class="logo">anginbnb</div>
    <nav>
        <a href="index.php">Home</a>
        <a href="properties.php">Properties</a>
    </nav>
    <div class="auth-links">
        <a href="login.php" class="active">Login</a>
        <a href="signup.php">Sign up</a>
    </div>
</header>

<div class="login-container">
    <div class="login-card">
        <h2>Welcome back</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?= $old_email ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember_me" name="remember_me" <?= $remember_me_checked ? 'checked' : '' ?>>
                <label for="remember_me">Remember me for 7 days</label>
            </div>

            <button type="submit" name="login" class="btn-signin">Sign in</button>
        </form>

        <p class="signup-link">
            Don't have an account? <a href="signup.php">Sign up</a>
        </p>
    </div>
</div>

<footer>
    <div class="footer-grid">
        <div>
            <h4>Anginbnb</h4>
            <p>Your home away from home. Discover unique places to stay around the world.</p>
        </div>
        <div>
            <h4>Support</h4>
            <p>Help Center</p>
            <p>Contact Us</p>
            <p>Safety Information</p>
            <p>Cancellation Options</p>
        </div>
        <div>
            <h4>Community</h4>
            <p>Anginbnb Blog</p>
            <p>Community Forum</p>
            <p>Host Neighbors</p>
            <p>Refer Friends</p>
        </div>
        <div>
            <h4>Company</h4>
            <p>About Us</p>
            <p>Careers</p>
            <p>Press</p>
            <p>Investors</p>
        </div>
    </div>
    <p class="copyright">© 2025 Anginbnb, Inc. All rights reserved.</p>
    <div class="privacy-links">
        <span>Privacy Policy</span>
        <span>Terms of Service</span>
        <span>Cookie Policy</span>
    </div>
</footer>

</body>
</html>