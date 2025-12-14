<?php
session_start();
include 'conn.php'; 

if (!isset($_SESSION['user_id'])) { 
    $_SESSION['user_id'] = 2; 
}
$current_user_id = $_SESSION['user_id']; 

if (isset($_POST['delete_user'])) {
    $id_hapus = $_POST['user_id_delete'];
    
    if ($id_hapus != $current_user_id) {
        $query_hapus = "DELETE FROM msuser WHERE UserID = '$id_hapus'";
        $run_hapus = mysqli_query($koneksi, $query_hapus);

        if ($run_hapus) {
            echo "<script>alert('User berhasil dihapus!'); window.location='manage_users.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus user!'); window.location='manage_users.php';</script>";
        }
    } else {
        echo "<script>alert('Anda tidak bisa menghapus akun sendiri!'); window.location='manage_users.php';</script>";
    }
}

$limit = 6; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$search_query = "";
$search_keyword = "";
if (isset($_GET['search'])) {
    $search_keyword = $_GET['search'];
    $search_query = "AND UserName LIKE '%$search_keyword%'";
}

$query_count = mysqli_query($koneksi, "SELECT count(*) as total FROM msuser WHERE 1 $search_query");
$data_count = mysqli_fetch_assoc($query_count);
$total_users = $data_count['total']; 
$total_pages = ceil($total_users / $limit);

$query_data = "SELECT * FROM msuser WHERE 1 $search_query LIMIT $start, $limit";
$result = mysqli_query($koneksi, $query_data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anginbnb - User Management</title>
    <link rel="stylesheet" href="manage_users.css">
</head>
<body>

    <header>
        <div class="logo">anginbnb</div>
        <nav>
            <a href="manage_users.php" class="active">Manage Users</a>
            <a href="manage_properties.php">Manage Properties</a>
            <a href="payment_types.php">Payment Types</a>
            <a href="categories.php">Categories</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
        <div class="user-info">Welcome, admin</div> 
    </header>

    <main>
        <div class="page-header">
            <h1>User Management</h1>
            <p>Manage and monitor all registered users</p>
        </div>

        <div class="container">
            
            <div class="search-card">
                <div class="search-header">
                    <h3>Search Users</h3>
                    <span class="page-indicator">Page <?php echo $page; ?> of <?php echo $total_pages == 0 ? 1 : $total_pages; ?></span>
                </div>
                
                <p class="total-info"><?php echo $total_users; ?> total users found</p>

                <form action="" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search by username..." value="<?php echo $search_keyword; ?>">
                    <button type="submit">Search</button>
                </form>
            </div>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="40%">User Details</th>
                            <th width="20%">Role</th>
                            <th width="35%" style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['UserID']; ?></td>
                                <td>
                                    <div class="user-name"><?php echo $row['UserName']; ?></div>
                                    <div class="user-email"><?php echo $row['UserEmail']; ?></div>
                                </td>
                                <td>
                                    <span class="role-badge <?php echo strtolower($row['UserRole']); ?>">
                                        <?php echo $row['UserRole']; ?>
                                    </span>
                                </td>
                                <td align="right">
                                    <div class="action-buttons">
                                        <a href="edit_user.php?id=<?php echo $row['UserID']; ?>" class="btn btn-edit">Edit</a>

                                        <?php if($row['UserID'] == $current_user_id): ?>
                                            <span class="you-label">(You)</span>
                                        <?php else: ?>
                                            <form action="" method="POST" onsubmit="return confirm('Yakin hapus user ini?');">
                                                <input type="hidden" name="user_id_delete" value="<?php echo $row['UserID']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-delete">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" align="center" style="padding: 20px;">User tidak ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo $search_keyword; ?>" 
                       class="<?php echo ($page == $i) ? 'active' : ''; ?>">
                       <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>

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
</body>
</html>