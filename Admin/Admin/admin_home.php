<?php
include "config/database.php";

$q = mysqli_query($conn, "SELECT * FROM categories");
echo "JUMLAH CATEGORY: " . mysqli_num_rows($q);
exit;


$categories = mysqli_query($conn, "SELECT * FROM categories");
$properties = mysqli_query($conn, "
    SELECT p.*, c.name AS category
    FROM properties p
    JOIN categories c ON p.category_id = c.id
    ORDER BY rating DESC
    LIMIT 6
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Home - Anginbnb</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>


<div class="navbar">
    <div class="logo">anginbnb</div>
    <ul>
        <li><a href="#">Dashboard</a></li>
        <li><a href="#">Manage Users</a></li>
        <li><a href="#">Manage Properties</a></li>
        <li><a href="#">Payment Types</a></li>
        <li><a href="#">Categories</a></li>
        <li><a href="#">Profile</a></li>
        <li><a href="#">Logout</a></li>
    </ul>
</div>


<div class="hero">
    <h1>Find your next adventure</h1>
    <p>Discover unique places to stay around the world with Anginbnb</p>
</div>

<div class="section">
    <h2>Explore by Category</h2>
    <div class="categories">
        <?php while ($c = mysqli_fetch_assoc($categories)) { ?>
            <div class="category">
                <h3><?= $c['name'] ?></h3>
            </div>
        <?php } ?>
    </div>
</div>

<div class="section">
    <h2>Featured Properties</h2>
    <div class="properties">
        <?php while ($p = mysqli_fetch_assoc($properties)) { ?>
            <div class="card">
                <img src="assets/images/<?= $p['image'] ?>">
                <h3><?= $p['name'] ?></h3>
                ⭐ <?= $p['rating'] ?><br>
                <?= $p['location'] ?><br>
                <span class="price">$<?= number_format($p['price']) ?>/night</span>
            </div>
        <?php } ?>
    </div>
</div>

<footer>
    <p>© 2025 Anginbnb</p>
</footer>

</body>
</html>
