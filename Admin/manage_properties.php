<?php
session_start();
require 'config.php'; // Asumsikan file config.php berisi koneksi $conn

/* ====== SIMULASI ADMIN LOGIN ====== */
$_SESSION['role'] = 'admin';
if ($_SESSION['role'] !== 'admin') {
    // Redirect jika bukan admin
    header("Location: index.php");
    exit();
}

// Inisialisasi pesan
$success_message = '';
$error_message = '';
$errors = [];

/* ====== 1. FETCH CATEGORIES (for Insert Form) ====== */
$categories = [];
$categoryStmt = $conn->prepare("SELECT CategoryID, CategoryName FROM mscategory ORDER BY CategoryName ASC");
$categoryStmt->execute();
$categoryResult = $categoryStmt->get_result();
while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row;
}
$categoryStmt->close();

/* ====== 2. INSERT NEW PROPERTY LOGIC ====== */
if (isset($_POST['insert_property'])) {
    // Ambil data dari form
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $rating = trim($_POST['rating'] ?? '');
    $categoryID = $_POST['category'] ?? '';
    $description = trim($_POST['description'] ?? '');

    // --- Validasi Kriteria ---
    
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
        // Lakukan INSERT jika validasi berhasil
        // Catatan: Kolom PropertyRating di DB adalah DECIMAL(2,2). 
        // Nilai Rating 9.0 atau 10.0 mungkin perlu disesuaikan atau ubah tipe kolom DB menjadi DECIMAL(3,1) atau DECIMAL(3,2) untuk menampung 10.0. 
        // Saya asumsikan di sini DB bisa menerima float/decimal.
        
        $insertStmt = $conn->prepare("INSERT INTO msproperty (PropertyName, PropertyLocation, PropertyPrice, PropertyDescription, PropertyRating, CategoryID) VALUES (?, ?, ?, ?, ?, ?)");
        
        // Harga dan Rating harus float/double untuk bind_param
        $price_float = (float)$price; 
        $rating_float = (float)$rating; 

        $insertStmt->bind_param("ssisdi", $name, $location, $price_float, $description, $rating_float, $categoryID);

        if ($insertStmt->execute()) {
            $success_message = "New property **" . htmlspecialchars($name) . "** inserted successfully.";
            // Kosongkan POST data setelah sukses
            $_POST = [];
        } else {
            $error_message = 'Failed to insert property: ' . $conn->error;
        }
        $insertStmt->close();
    } else {
         $error_message = 'Please correct the errors in the form.';
    }
}

/* ====== 3. DELETE PROPERTY LOGIC ====== */
if (isset($_GET['delete_id'])) {
    $deleteID = (int)$_GET['delete_id'];
    
    $deleteStmt = $conn->prepare("DELETE FROM msproperty WHERE PropertyID = ?");
    $deleteStmt->bind_param("i", $deleteID);
    
    if ($deleteStmt->execute()) {
        if ($deleteStmt->affected_rows > 0) {
             $success_message = "Property with ID #$deleteID deleted successfully.";
        } else {
             $error_message = "Property with ID #$deleteID not found.";
        }
    } else {
        $error_message = "Failed to delete property: " . $conn->error;
    }
    $deleteStmt->close();
    
    // Redirect untuk menghilangkan parameter delete_id dari URL (Flash message simulation)
    if ($success_message || $error_message) {
        $messages = ['success' => $success_message, 'error' => $error_message];
        $_SESSION['flash_message'] = $messages;
        header("Location: manage_properties.php");
        exit();
    }
}

// Ambil pesan flash dari sesi setelah redirect (untuk delete)
if (isset($_SESSION['flash_message'])) {
    $success_message = $_SESSION['flash_message']['success'] ?? '';
    $error_message = $_SESSION['flash_message']['error'] ?? '';
    unset($_SESSION['flash_message']);
}


/* ====== 4. FETCH PROPERTIES (Search & Pagination) ====== */

$limit = 5; // 5 properties per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search parameters
$search_query = trim($_GET['search'] ?? '');
$search_param = '%' . $search_query . '%';

// Base Query
$sql_count = "SELECT COUNT(*) AS total FROM msproperty mp ";
$sql_select = "SELECT mp.PropertyID, mp.PropertyName, mp.PropertyPrice, mp.PropertyLocation, mp.PropertyRating, mc.CategoryName 
               FROM msproperty mp 
               JOIN mscategory mc ON mp.CategoryID = mc.CategoryID ";

$where_clause = "";
if (!empty($search_query)) {
    // Search based on Name OR Location
    $where_clause = " WHERE mp.PropertyName LIKE ? OR mp.PropertyLocation LIKE ? ";
}

// --- Total Count Query ---
$count_stmt = $conn->prepare($sql_count . $where_clause);
if (!empty($search_query)) {
    $count_stmt->bind_param("ss", $search_param, $search_param);
}
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$count_stmt->close();

// --- Main Select Query ---
$properties = [];
$select_stmt = $conn->prepare($sql_select . $where_clause . " ORDER BY mp.PropertyID DESC LIMIT ? OFFSET ?");

if (!empty($search_query)) {
    $select_stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
} else {
    $select_stmt->bind_param("ii", $limit, $offset);
}

$select_stmt->execute();
$result = $select_stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}
$select_stmt->close();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Properties</title>
    <link rel="stylesheet" href="manage_properties.css">
    <script>
        // Fungsi untuk mengkonfirmasi Delete
        function confirmDelete(id, name) {
            return confirm(`Are you sure you want to delete property #${id}: ${name}?`);
        }
    </script>
</head>
<body>

<header class="navbar">
    <div class="logo">anginbnb</div>
    <nav>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_properties.php" class="active">Manage Properties</a>
        <a href="manage_payment_types.php">Payment Types</a>
        <a href="manage_categories.php">Categories</a>
        <a href="#">Profile</a>
        <a href="#">Logout</a>
    </nav>
    <span class="welcome">Welcome, admin</span>
</header>

<div class="container">
    <h1>Manage Properties</h1>
    <p class="subtitle">Create and manage all property listings</p>

    <?php if (!empty($success_message)): ?>
        <div class="success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="insert-section card">
        <h3>Create New Property</h3>
        
        <form method="POST" class="insert-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" placeholder="Name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    <?php if (isset($errors['name'])): ?><div class="input-error"><?= $errors['name'] ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" placeholder="Location" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" required>
                    <?php if (isset($errors['location'])): ?><div class="input-error"><?= $errors['location'] ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="price" placeholder="Price" min="1" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
                    <?php if (isset($errors['price'])): ?><div class="input-error"><?= $errors['price'] ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Rating</label>
                    <input type="number" step="0.1" name="rating" placeholder="Rating" min="0" max="10" value="<?= htmlspecialchars($_POST['rating'] ?? '') ?>" required>
                    <?php if (isset($errors['rating'])): ?><div class="input-error"><?= $errors['rating'] ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="">-- Choose Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['CategoryID'] ?>" 
                                <?= (isset($_POST['category']) && $_POST['category'] == $cat['CategoryID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['CategoryName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['category'])): ?><div class="input-error"><?= $errors['category'] ?></div><?php endif; ?>
                </div>
            </div>

            <div class="form-group full-width">
                <label>Description</label>
                <textarea name="description" placeholder="Description" rows="4" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                <?php if (isset($errors['description'])): ?><div class="input-error"><?= $errors['description'] ?></div><?php endif; ?>
            </div>

            <button type="submit" name="insert_property" class="btn-create full-width">Create Property</button>
        </form>
    </div>

    <div class="existing-properties card">
        <h3>Existing Properties</h3>
        <p class="small-text"><?= $total_rows ?> total properties found</p>

        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search properties..." value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit" class="btn-search">Search</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Property Name</th>
                    <th>Price</th>
                    <th>Location</th>
                    <th>Rating</th>
                    <th>Category</th>
                    <th class="actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($properties)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #777;">
                            <?php if (!empty($search_query)): ?>
                                No properties found for "<?= htmlspecialchars($search_query) ?>".
                            <?php else: ?>
                                No properties found in the system.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($properties as $property): ?>
                        <tr>
                            <td><?= $property['PropertyID'] ?></td>
                            <td><?= htmlspecialchars($property['PropertyName']) ?></td>
                            <td>$<?= number_format($property['PropertyPrice'], 2) ?></td>
                            <td><?= htmlspecialchars($property['PropertyLocation']) ?></td>
                            <td><?= number_format($property['PropertyRating'], 1) ?></td>
                            <td><?= htmlspecialchars($property['CategoryName']) ?></td>
                            <td class="actions-col">
                                <a href="edit_properties.php?id=<?= $property['PropertyID'] ?>" class="btn-action btn-edit">Edit</a>
                                
                                <a href="?delete_id=<?= $property['PropertyID'] ?>" 
                                   class="btn-action btn-delete" 
                                   onclick="return confirmDelete(<?= $property['PropertyID'] ?>, '<?= addslashes(htmlspecialchars($property['PropertyName'])) ?>');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php 
                    $base_url = "manage_properties.php?search=" . urlencode($search_query) . "&";
                ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="<?= $base_url ?>page=<?= $i ?>" class="page-link <?= ($i == $page) ? 'active-page' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <span class="pagination-info">Page <?= $page ?> of <?= $total_pages ?></span>
            </div>
        <?php endif; ?>

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