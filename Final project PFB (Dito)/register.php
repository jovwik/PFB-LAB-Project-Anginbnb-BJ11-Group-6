<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anginbnb - Sign Up</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>

    <header>
        <div class="logo">anginbnb</div>
        <nav class="nav-center">
            <a href="index.php">Home</a>
            <a href="properties.php">Properties</a>
        </nav>
        <div class="nav-right">
            <a href="login.php">Login</a>
            <a href="register.php" class="btn-signup">Sign up</a>
        </div>
    </header>

    <main>
        <div class="login-card">
            <div class="card-header">
                <h1>Create Your Account</h1>
            </div>
            <form action="tambah.php" method="POST">
                <div class="input-group">
                    <label for="full-name">Full Name</label>
                    <input type="text" id="full-name" name="full-name" required>
                </div>
                <div class="input-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <p style="font-size: 12px; color: #717171; margin-top: 5px; line-height: 1.4;">At least 8 characters with uppercase, lowercase, number, and special character</p>
                </div>
                
                <button type="submit" class="btn-submit">Sign Up</button>
                
                <div class="form-footer">
                    Already have an account? <a href="login.php">Login</a>
                </div>
            </form>
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
            const form = document.querySelector('form');

            form.addEventListener('submit', function(e) {
                let errors = [];
                const name = document.getElementById('full-name').value.trim();
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value;

                if (name === "") {
                    errors.push("Name: Harus diisi.");
                } else if (name.length < 3 || name.length > 50) {
                    errors.push("Name: Panjang harus antara 3 sampai 50 karakter.");
                } else if (!/^[A-Za-z\s]+$/.test(name)) {
                    errors.push("Name: Hanya boleh berisi huruf dan spasi.");
                }

                if (email === "") {
                    errors.push("Email: Harus diisi.");
                } else {
                    const atCount = (email.match(/@/g) || []).length;
                    if (atCount !== 1) {
                        errors.push("Email: Harus memiliki tepat satu simbol '@'.");
                    }
                    if (!email.includes('.')) {
                        errors.push("Email: Harus memiliki minimal satu titik (.).");
                    }
                }

                if (password === "") {
                    errors.push("Password: Harus diisi.");
                } else {
                    if (password.length < 8) {
                        errors.push("Password: Minimal 8 karakter.");
                    }
                }

                if (errors.length > 0) {
                    e.preventDefault();
                    alert("GAGAL REGISTRASI:\n\n" + errors.join("\n"));
                }
            });
        });
    </script>
</body>
</html>