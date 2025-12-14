<?php
session_start();
require 'config.php'; // Asumsikan file config.php berisi koneksi $conn

/* ====== SIMULASI ADMIN LOGIN ====== */
$_SESSION['role'] = 'admin';
if ($_SESSION['role'] !== 'admin') {
    // Redirect jika bukan admin (ini tetap dipertahankan untuk keamanan)
    header("Location: index.php");
    exit();
}

$success_message = '';
$error_message = '';
$errors = [];
$is_valid_access = true; // Flag untuk menentukan apakah konten harus ditampilkan

/* ====== A. GET PROPERTY ID AND FETCH CURRENT DATA ====== */
$property_id = $_GET['id'] ?? null;

// --- PERUBAHAN UTAMA: Ganti Redirect dengan Pengecekan Flag ---
if (!$property_id || !is_numeric($property_id)) {
    $error_message = "Error: Invalid or missing Property ID. Please select a property from the Manage Properties list.";
    $is_valid_access = false;
    $data = []; // Inisialisasi data kosong untuk mencegah error PHP
}

if ($is_valid_access) {
    // 1. Fetch Current Property Data
    $property = null;
    $fetchStmt = $conn->prepare("SELECT PropertyID, PropertyName, PropertyLocation, PropertyPrice, PropertyDescription, PropertyRating, CategoryID FROM msproperty WHERE PropertyID = ?");
    $fetchStmt->bind_param("i", $property_id);
    $fetchStmt->execute();
    $property = $fetchStmt->get_result()->fetch_assoc();
    $fetchStmt->close();

    if (!$property) {
        // Jika properti tidak ditemukan
        $error_message = "Error: Property with ID #$property_id not found in the database.";
        $is_valid_access = false;
        $data = [];
    } else {
        // 2. Fetch Categories for Dropdown (Hanya jika properti valid)
        $categories = [];
        $categoryStmt = $conn->prepare("SELECT CategoryID, CategoryName FROM mscategory ORDER BY CategoryName ASC");
        $categoryStmt->execute();
        $categoryResult = $categoryStmt->get_result();
        while ($row = $categoryResult->fetch_assoc()) {
            $categories[] = $row;
        }
        $categoryStmt->close();

        // Inisialisasi data form dengan data properti saat ini
        $data = $property;
    }
}


/* ====== B. UPDATE PROPERTY LOGIC (Hanya diproses jika akses valid) ====== */
if ($is_valid_access && isset($_POST['update_property'])) {
    // Ambil data dari form
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $rating = trim($_POST['rating'] ?? '');
    $categoryID = $_POST['category'] ?? '';
    $description = trim($_POST['description'] ?? '');

    // Update data array $data dengan POST data untuk mempertahankan input di form jika terjadi error
    $data['PropertyName'] = $name;
    $data['PropertyLocation'] = $location;
    $data['PropertyPrice'] = $price;
    $data['PropertyRating'] = $rating;
    $data['CategoryID'] = $categoryID;
    $data['PropertyDescription'] = $description;

    // --- Validasi Kriteria ---
    // (Kode validasi sama seperti sebelumnya, tidak diubah)
    
    // Name
    if (empty($name)) $errors['name'] = 'Name must be filled.';
    if (strlen($name) > 100) $errors['name'] = 'Name must be less than 100 characters.';
    if (!empty($name) && !preg_match("/^[a-zA-Z\s,.'-]*$/", $name)) $errors['name'] = 'Name must NOT contain numbers and special characters.';

    // Location
    if (empty($location)) $errors['location'] = 'Location must be filled.';
    if (strlen($location) > 255) $errors['location'] = 'Location must be less than 255 characters.';

    // Price
    if (empty($price)) $errors['price'] = 'Price must be filled.';
    if (!is_numeric($price) || $price < 1) $errors['price'] = 'Price must be numeric and at least 1.';

    // Rating
    if (empty($rating)) $errors['rating'] = 'Rating must be filled.';
    if (!is_numeric($rating)) $errors['rating'] = 'Rating must be numeric.';
    if ($rating < 0 || $rating > 10) $errors['rating'] = 'Rating must be between 0 and 10.';
    
    // Category
    if (empty($categoryID)) $errors['category'] = 'Category must be chosen.';

    // Description
    if (empty($description)) $errors['description'] = 'Description must be filled.';
    if (strlen($description) > 2000) $errors['description'] = 'Description must be less than 2000 characters.';


    if (empty($errors)) {
        // Lakukan UPDATE jika validasi berhasil
        
        $updateStmt = $conn->prepare("UPDATE msproperty SET PropertyName=?, PropertyLocation=?, PropertyPrice=?, PropertyDescription=?, PropertyRating=?, CategoryID=? WHERE PropertyID=?");
        
        $price_float = (float)$price; 
        $rating_float = (float)$rating; 
        $property_id_int = (int)$property_id;

        $updateStmt->bind_param("ssisdii", $name, $location, $price_float, $description, $rating_float, $categoryID, $property_id_int);

        if ($updateStmt->execute()) {
            $success_message = "Property **" . htmlspecialchars($name) . "** updated successfully.";
            // Refresh data setelah update
            $temp_data = $conn->prepare("SELECT PropertyID, PropertyName, PropertyLocation, PropertyPrice, PropertyDescription, PropertyRating, CategoryID FROM msproperty WHERE PropertyID = ?");
            $temp_data->bind_param("i", $property_id);
            $temp_data->execute();
            $data = $temp_data->get_result()->fetch_assoc();
            $temp_data->close();
        } else {
            $error_message = 'Failed to update property: ' . $conn->error;
        }
        $updateStmt->close();
    } else {
         $error_message = 'Please correct the errors in the form.';
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Property<?= $is_valid_access ? ' #' . $property_id : '' ?></title>
    <link rel="stylesheet" href="edit_properties.css">
    <link rel="stylesheet" href="manage_properties.css">
</head>
<body>

<header class="navbar">
    <div class="logo">anginbnb</div>
    <nav>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_properties.php">Manage Properties</a>
        <a href="manage_payment_types.php">Payment Types</a>
        <a href="manage_categories.php">Categories</a>
        <a href="#">Profile</a>
        <a href="#">Logout</a>
    </nav>
    <span class="welcome">Welcome, admin</span>
</header>

<div class="container">
    <h1>Edit Property</h1>
    <p class="subtitle">Update property details</p>

    <?php if (!empty($success_message)): ?>
        <div class="success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php 
    if ($is_valid_access): 
    ?>
    
    <div class="card-edit-form-container">
        <h3 class="property-header">
            <?= htmlspecialchars($data['PropertyName']) ?>
        </h3>
        <div class="property-info">
            ID: <?= $data['PropertyID'] ?> | Current Category: 
            <?php 
                // Cari nama kategori dari array categories
                $current_cat_name = array_filter($categories, function($cat) use ($data) {
                    return $cat['CategoryID'] == $data['CategoryID'];
                });
                echo htmlspecialchars(reset($current_cat_name)['CategoryName'] ?? 'N/A');
            ?>
        </div>

        <form method="POST" class="edit-form">
            
            <div class="form-group full-width">
                <label>Property Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($data['PropertyName']) ?>" required>
                <?php if (isset($errors['name'])): ?><div class="input-error"><?= $errors['name'] ?></div><?php endif; ?>
            </div>

            <div class="form-group full-width">
                <label>Location</label>
                <input type="text" name="location" value="<?= htmlspecialchars($data['PropertyLocation']) ?>" required>
                <?php if (isset($errors['location'])): ?><div class="input-error"><?= $errors['location'] ?></div><?php endif; ?>
            </div>

            <div class="form-group full-width">
                <label>Price (minimum 1)</label>
                <input type="number" name="price" min="1" value="<?= htmlspecialchars($data['PropertyPrice']) ?>" required>
                <?php if (isset($errors['price'])): ?><div class="input-error"><?= $errors['price'] ?></div><?php endif; ?>
            </div>

            <div class="form-group full-width">
                <label>Rating (0-10)</label>
                <input type="number" step="0.1" name="rating" min="0" max="10" value="<?= htmlspecialchars($data['PropertyRating']) ?>" required>
                <?php if (isset($errors['rating'])): ?><div class="input-error"><?= $errors['rating'] ?></div><?php endif; ?>
            </div>

            <div class="form-group full-width">
                <label>Category</label>
                <select name="category" required>
                    <option value="">-- Choose Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['CategoryID'] ?>" 
                            <?= ($data['CategoryID'] == $cat['CategoryID']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['CategoryName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category'])): ?><div class="input-error"><?= $errors['category'] ?></div><?php endif; ?>
            </div>

            <div class="form-group full-width">
                <label>Description</label>
                <textarea name="description" rows="5" required><?= htmlspecialchars($data['PropertyDescription']) ?></textarea>
                <?php if (isset($errors['description'])): ?><div class="input-error"><?= $errors['description'] ?></div><?php endif; ?>
            </div>

            <div class="action-buttons full-width">
                <a href="manage_properties.php" class="btn-secondary">Back to List</a>
                <button type="submit" name="update_property" class="btn-primary">Update Property</button>
            </div>
        </form>
    </div>

    <?php 
    else:
    ?>
    <div class="card" style="text-align: center;">
        <p style="margin-bottom: 20px; font-weight: bold;">Failed to load property details.</p>
        <a href="manage_properties.php" class="btn-secondary" style="width: 200px; margin: 0 auto;">Back to List</a>
    </div>
    <?php
    endif;
    ?>
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