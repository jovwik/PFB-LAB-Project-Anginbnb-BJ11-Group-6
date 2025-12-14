<?php
session_start();
require 'config.php'; // Asumsikan file config.php berisi koneksi $conn

/* ====== SIMULASI ADMIN LOGIN ====== */
$_SESSION['role'] = 'admin'; // Halaman ini hanya untuk admin

$success_message = '';
$error_message = '';

/* ====== 1. INSERT NEW CATEGORY LOGIC ====== */
if (isset($_POST['insert_category']) && $_SESSION['role'] === 'admin') {
    $categoryName = trim($_POST['category_name']);

    if (empty($categoryName)) {
        $error_message = 'Category Name must be filled.';
    } else {
        // Cek apakah kategori sudah ada
        $checkStmt = $conn->prepare("SELECT CategoryID FROM mscategory WHERE CategoryName = ?");
        $checkStmt->bind_param("s", $categoryName);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error_message = 'Category "' . htmlspecialchars($categoryName) . '" already exists.';
        } else {
            // Lakukan INSERT
            $insertStmt = $conn->prepare("INSERT INTO mscategory (CategoryName) VALUES (?)");
            $insertStmt->bind_param("s", $categoryName);
            if ($insertStmt->execute()) {
                $success_message = "New category **" . htmlspecialchars($categoryName) . "** inserted successfully.";
            } else {
                $error_message = 'Failed to insert category: ' . $conn->error;
            }
            $insertStmt->close();
        }
        $checkStmt->close();
    }
}

/* ====== 2. DELETE CATEGORY LOGIC ====== */
if (isset($_GET['delete_id']) && $_SESSION['role'] === 'admin') {
    $deleteID = (int)$_GET['delete_id'];
    
    // Perlu dicek relasi FOREIGN KEY (msproperty)
    // Jika ada properti yang masih menggunakan kategori ini, DELETE akan gagal kecuali Anda menggunakan ON DELETE CASCADE
    // Untuk tujuan sederhana ini, kita coba DELETE langsung
    
    $deleteStmt = $conn->prepare("DELETE FROM mscategory WHERE CategoryID = ?");
    $deleteStmt->bind_param("i", $deleteID);
    
    if ($deleteStmt->execute()) {
        if ($deleteStmt->affected_rows > 0) {
             $success_message = "Category with ID #$deleteID deleted successfully.";
        } else {
             $error_message = "Category with ID #$deleteID not found or could not be deleted.";
        }
    } else {
        // Error yang paling umum di sini adalah karena Foreign Key Constraint
        if ($conn->errno == 1451) {
            $error_message = "Cannot delete category #$deleteID because it is still used by one or more properties.";
        } else {
            $error_message = "Failed to delete category: " . $conn->error;
        }
    }
    $deleteStmt->close();
    
    // Alihkan kembali ke halaman bersih (menghapus parameter delete_id dari URL)
    // Ini penting agar pesan sukses/error tidak muncul lagi saat refresh
    if ($success_message || $error_message) {
        $messages = ['success' => $success_message, 'error' => $error_message];
        $_SESSION['flash_message'] = $messages;
        header("Location: manage_categories.php");
        exit();
    }
}

// Ambil pesan flash dari sesi setelah redirect (untuk delete)
if (isset($_SESSION['flash_message'])) {
    $success_message = $_SESSION['flash_message']['success'] ?? '';
    $error_message = $_SESSION['flash_message']['error'] ?? '';
    unset($_SESSION['flash_message']);
}


/* ====== 3. FETCH ALL CATEGORIES ====== */
$categories = [];
$fetchStmt = $conn->prepare("SELECT CategoryID, CategoryName FROM mscategory ORDER BY CategoryID ASC");
$fetchStmt->execute();
$result = $fetchStmt->get_result();

while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
$fetchStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Categories Management</title>
    <link rel="stylesheet" href="manage_categories.css">
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
    <h1>Categories Management</h1>
    <p class="subtitle">Manage all property categories</p>

    <?php if (!empty($success_message)): ?>
        <div class="success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="insert-section card">
        <h3>Create New Category</h3>
        <div class="small-text"><?= count($categories) ?> categories in system</div>
        
        <form method="POST" class="insert-form">
            <input type="text" name="category_name" placeholder="Enter category name..." required 
                   value="<?= isset($_POST['category_name']) && empty($success_message) ? htmlspecialchars($_POST['category_name']) : '' ?>">
            <button type="submit" name="insert_category" class="btn-create">Create</button>
        </form>
    </div>

    <div class="categories-table card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: #777;">No categories found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= $category['CategoryID'] ?></td>
                            <td><?= htmlspecialchars($category['CategoryName']) ?></td>
                            <td>
                                <a href="?delete_id=<?= $category['CategoryID'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete category #<?= $category['CategoryID'] ?>: <?= htmlspecialchars($category['CategoryName']) ?>?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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
    <p class="copyright">© 2025 Anginbnb</p>
    <div class="privacy-links">
        <span>Privacy Policy</span>
        <span>Terms of Service</span>
        <span>Cookie Policy</span>
    </div>
</footer>

</body>
</html>