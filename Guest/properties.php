<?php
require 'config.php';

$search   = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$page     = $_GET['page'] ?? 1;

$limit  = 6;
$offset = ($page - 1) * $limit;

/* ================= COUNT ================= */
$countSql = "
    SELECT COUNT(*) AS total
    FROM msproperty p
    WHERE (p.PropertyName LIKE ? OR p.PropertyLocation LIKE ?)
";

$params = ["%$search%", "%$search%"];
$types  = "ss";

if ($category !== '') {
    $countSql .= " AND p.CategoryID = ?";
    $params[] = $category;
    $types   .= "i";
}

$stmt = $conn->prepare($countSql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);

/* ================= DATA ================= */
$dataSql = "
    SELECT 
        p.PropertyID,
        p.PropertyName,
        p.PropertyLocation,
        p.PropertyPrice,
        p.PropertyRating,
        c.CategoryName
    FROM msproperty p
    LEFT JOIN mscategory c ON p.CategoryID = c.CategoryID
    WHERE (p.PropertyName LIKE ? OR p.PropertyLocation LIKE ?)
";

if ($category !== '') {
    $dataSql .= " AND p.CategoryID = ?";
}

$dataSql .= " LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($dataSql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$properties = $stmt->get_result();

/* ================= CATEGORY ================= */
$categories = $conn->query("SELECT * FROM mscategory");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Properties</title>
    <link rel="stylesheet" href="properties.css">
</head>
<body>

<!-- ================= NAVBAR ================= -->
<header class="navbar">
    <div class="nav-inner">
        <div class="logo">anginbnb</div>
        <nav>
            <a href="#">Home</a>
            <a class="active">Properties</a>
        </nav>
        <div class="auth">
            <button class="login">Login</button>
            <button class="signup">Sign up</button>
        </div>
    </div>
</header>

<!-- ================= MAIN ================= -->
<main class="main">
    <h1>All Properties</h1>

    <!-- SEARCH -->
    <div class="search-bar">
        <input type="text" placeholder="Search properties by name or location...">
        <button class="search-btn">Search</button>
    </div>

    <!-- FILTER -->
    <div class="filter-bar">
        <select>
            <option>All Categories</option>
        </select>
        <button class="filter-btn">Filter</button>
    </div>

    <!-- GRID -->
    <div class="grid">
        <?php for ($i=0; $i<6; $i++): ?>
        <div class="card">
            <h3>Grand Plaza Hotel</h3>
            <span class="badge">Hotel</span>
            <p class="rating">⭐ 9.0/10</p>
            <p>New York City, USA</p>
            <p class="price">$450.00/night</p>
            <a class="detail-btn">View Details →</a>
        </div>
        <?php endfor; ?>
    </div>

    <!-- PAGINATION -->
    <div class="pagination">
        <a class="active">1</a>
        <a>2</a>
        <a>3</a>
        <a>4</a>
    </div>
</main>

<!-- ================= FOOTER ================= -->
<footer class="footer">
    <div class="footer-grid">
        <div>
            <strong>Anginbnb</strong>
            <p>Your home away from home.</p>
        </div>
        <div>
            <strong>Support</strong>
            <p>Help Center</p>
            <p>Cancellation Options</p>
        </div>
        <div>
            <strong>Community</strong>
            <p>Airbnb.org</p>
            <p>Host Resources</p>
        </div>
        <div>
            <strong>Company</strong>
            <p>About Us</p>
            <p>Careers</p>
        </div>
    </div>
</footer>

</body>
</html>
