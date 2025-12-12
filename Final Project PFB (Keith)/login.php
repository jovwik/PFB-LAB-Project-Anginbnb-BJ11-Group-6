

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Anginbnb</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <a href="index.php" class="logo">anginbnb</a>
            </div>
            <div class="nav-center">
                <a href="index.php" class="nav-link">Home</a>
                <a href="properties.php" class="nav-link">Properties</a>
            </div>
            <div class="nav-right">
                <a href="login.php" class="nav-link active">Login</a>
                <a href="register.php" class="nav-link">Sign up</a>
            </div>
        </div>
    </nav>

    
    <div class="main-content">
        <div class="login-container">
            <div class="login-box">
                <h2 class="login-title">Welcome back</h2>

               

                <form action="login.php" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                        >
                    </div>

                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Remember me for 7 days</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-signin">Sign in</button>

                    <div class="register-link">
                        Don't have an account? <a href="register.php">Sign up</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>Anginbnb</h3>
                <p>Your home away from home. Discover unique places to stay around the world.</p>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Safety Information</a></li>
                    <li><a href="#">Cancellation Options</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Community</h4>
                <ul>
                    <li><a href="#">Anginbnb Blog</a></li>
                    <li><a href="#">Host Resources</a></li>
                    <li><a href="#">Community Forum</a></li>
                    <li><a href="#">Refer Friends</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Company</h4>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Press</a></li>
                    <li><a href="#">Investors</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 Anginbnb, Inc. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
            </div>
        </div>
    </footer>

    <?php
    include 'db_connect.php';
    $koneksi = mysqli_connect('localhost','root','','student_db');

    if(mysqli_connect_errno()){
        echo'koneksi database gagal : ' . mysqli_connect_error();
    }
    ?>

    

</body>
</html>


