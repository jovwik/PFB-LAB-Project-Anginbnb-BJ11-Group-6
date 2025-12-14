<?php
session_start();
include 'conn.php'; 

if (isset($_POST['create_payment'])) {
    $name = $_POST['payment_name'];

    
    if (empty(trim($name))) {
        echo "<script>
            alert('Gagal: Nama Payment Type harus diisi!'); 
            window.location.href='payment_types.php';
        </script>";
    } else {
        
        $query = "INSERT INTO mspaymenttype (PaymentTypeName) VALUES ('$name')";
        $insert = mysqli_query($koneksi, $query);

        if ($insert) {
            
            echo "<script>
                alert('Success! New payment type added.'); 
                window.location.href='payment_types.php';
            </script>";
        } else {
            echo "<script>
                alert('Gagal insert database: " . mysqli_error($koneksi) . "'); 
                window.location.href='payment_types.php';
            </script>";
        }
    }
}



if (isset($_POST['delete_payment'])) {
    $id = $_POST['payment_id'];
    
    
    $query = "DELETE FROM mspaymenttype WHERE PaymentTypeID = '$id'";
    $delete = mysqli_query($koneksi, $query);

    if ($delete) {
        echo "<script>
            alert('Payment type deleted successfully.'); 
            window.location.href='payment_types.php';
        </script>";
    } else {
        echo "<script>
            alert('Gagal menghapus data.'); 
            window.location.href='payment_types.php';
        </script>";
    }
}



$result = mysqli_query($koneksi, "SELECT * FROM mspaymenttype");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anginbnb - Payment Types</title>
    <link rel="stylesheet" href="payment_types.css">
</head>
<body>

    <header>
        <div class="logo">anginbnb</div>
        <nav class="nav-center">
            <a href="#">Manage Users</a>
            <a href="#">Manage Properties</a>
            <a href="" class="active">Payment Types</a>
            <a href="#">Categories</a>
        </nav>
        <div class="nav-right">
            <a href="#">Profile</a>
            <a href="#">Logout</a>
            <span style="margin-left: 15px; color: #717171; font-size: 12px;">
                Welcome, Admin
            </span>
        </div>
    </header>

    <main>
        <div class="page-header">
            <h1>Payment Types Management</h1>
            <p>Manage all available payment methods</p>
        </div>

        <div class="container">
            
            <div class="card form-card">
                <h3>Create New Payment Type</h3>
                <p style="color: #717171; font-size: 14px; margin-bottom: 15px;">
                    <?php echo mysqli_num_rows($result); ?> payment types in system
                </p>

                <form action="" method="POST" style="display: flex; gap: 10px;">
                    <input type="text" name="payment_name" placeholder="Enter payment type name..." class="input-text">
                    <button type="submit" name="create_payment" class="btn-primary">Create</button>
                </form>
            </div>

            <div class="card table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">ID</th>
                            <th style="width: 70%;">Payment Type</th>
                            <th style="width: 20%; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $row['PaymentTypeID']; ?></td>
                                    <td><?php echo $row['PaymentTypeName']; ?></td>
                                    <td style="text-align: right;">
                                        <form action="" method="POST" onsubmit="return confirm('Are you sure you want to remove this payment type?');">
                                            <input type="hidden" name="payment_id" value="<?php echo $row['PaymentTypeID']; ?>">
                                            <button type="submit" name="delete_payment" class="btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 20px;">No payment types found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <footer>
        <div class="footcont">
            <div>
                <h4>Anginbnb</h4>
                <p>Your home away from home. Discover unique places to stay around the world.</p>
            </div>
            <div>
                <h4>Support</h4>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Safety Information</a></li>
                    <li><a href="#">Cancellation Options</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            <div>
                <h4>Community</h4>
                <ul>
                    <li><a href="#">Anginbnb Blog</a></li>
                    <li><a href="#">Host Resources</a></li>
                    <li><a href="#">Community Forum</a></li>
                    <li><a href="#">Refer Friends</a></li>
                </ul>
            </div>
            <div>
                <h4>Company</h4>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Press</a></li>
                    <li><a href="#">Investors</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <div>© 2025 Anginbnb, Inc. All rights reserved.</div>
            <div class="rightside">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookies Policy</a>
            </div>
        </div>
    </footer>

</body>
</html>