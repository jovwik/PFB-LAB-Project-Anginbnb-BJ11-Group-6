<?php
require 'config.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$page = $_GET['page'] ?? 1;

$limit = 6;
$offset = ($page - 1) * $limit;


$countSql = "
    SELECT COUNT(*) AS total
    FROM msproperty p
    JOIN mscategory c ON p.category_id = c.category_id
    WHERE (p.property_name LIKE ? OR p.property_location LIKE ?)
";

$params = ["%$search%", "%$search%"];
$types = "ss";

if ($category != '') {
    $countSql .= " AND c.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

$stmt = $conn->prepare($countSql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);


$dataSql = "
    SELECT p.property_id, p.property_name, p.property_location,
           p.property_price, p.property_rating,
           c.category_name
    FROM msproperty p
    JOIN mscategory c ON p.category_id = c.category_id
    WHERE (p.property_name LIKE ? OR p.property_location LIKE ?)
";

if ($category != '') {
    $dataSql .= " AND c.category_id = ?";
}

$dataSql .= " LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($dataSql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$properties = $stmt->get_result();


$categories = $conn->query("SELECT * FROM mscategory");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Properties</title>
    <link rel="stylesheet" href="all-properties.css">
</head>
<body>

<header class="navbar">
    <div class="logo">anginbnb</div>
    <nav>
        <a href="#">Home</a>
        <a class="active">Properties</a>
    </nav>
    <div class="auth">
        <button class="outline">Login</button>
        <button class="fill">Sign Up</button>
    </div>
</header>

<div class="container">
    <h1>All Properties</h1>

   
    <form class="search-bar" method="GET">
        <input type="text" name="search"
               placeholder="Search properties by name or location..."
               value="<?= htmlspecialchars($search) ?>">
        <button>Search</button>
    </form>

    
    <form class="filter-bar" method="GET">
        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
        <select name="category">
            <option value="">All Categories</option>
            <?php while ($c = $categories->fetch_assoc()): ?>
                <option value="<?= $c['category_id'] ?>"
                    <?= $category == $c['category_id'] ? 'selected' : '' ?>>
                    <?= $c['category_name'] ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button>Filter</button>
    </form>

    
    <div class="grid">
        <?php while ($p = $properties->fetch_assoc()): ?>
        <div class="card">
            <h3><?= $p['property_name'] ?></h3>
            <span class="badge"><?= $p['category_name'] ?></span>
            <p class="rating">⭐ <?= $p['property_rating'] ?>/10</p>
            <p><?= $p['property_location'] ?></p>
            <p class="price">$<?= number_format($p['property_price'], 2) ?>/night</p>
            <a href="property-detail.php?id=<?= $p['property_id'] ?>" class="detail-btn">
                View Details →
            </a>
        </div>
        <?php endwhile; ?>
    </div>

    
    <div class="pagination">
        <?php for ($i=1; $i<=$totalPages; $i++): ?>
            <a class="<?= $i==$page?'active':'' ?>"
               href="?page=<?= $i ?>&search=<?= $search ?>&category=<?= $category ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</div>

<footer>
    <p>© 2025 Anginbnb</p>
</footer>

</body>
</html>
