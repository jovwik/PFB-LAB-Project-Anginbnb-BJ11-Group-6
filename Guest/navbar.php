<?php
function renderNavbar($activePage = '') {
    $userName = getUserName();
    $role = getUserRole();
    $isGuest = !isLoggedIn();
    $isAdmin = isAdmin();
    $isMember = isMember();
    
    echo '<nav class="navbar">';
    echo '<div class="navbar-container">';
    echo '<a href="index.php" class="navbar-brand">Anginbnb</a>';
    echo '<ul class="navbar-menu">';
    
    echo '<li><a href="index.php" class="' . ($activePage === 'home' ? 'active' : '') . '">Home</a></li>';
    
    if (!$isAdmin) {
        echo '<li><a href="properties.php" class="' . ($activePage === 'properties' ? 'active' : '') . '">Properties</a></li>';
    }
    
    if ($isMember) {
        echo '<li><a href="my_bookings.php" class="' . ($activePage === 'my_bookings' ? 'active' : '') . '">My Bookings</a></li>';
    }
    
    if ($isAdmin) {
        echo '<li><a href="manage_users.php" class="' . ($activePage === 'manage_users' ? 'active' : '') . '">Manage Users</a></li>';
        echo '<li><a href="manage_properties.php" class="' . ($activePage === 'manage_properties' ? 'active' : '') . '">Manage Properties</a></li>';
        echo '<li><a href="manage_payment_types.php" class="' . ($activePage === 'manage_payment_types' ? 'active' : '') . '">Manage Payment Types</a></li>';
        echo '<li><a href="manage_categories.php" class="' . ($activePage === 'manage_categories' ? 'active' : '') . '">Manage Categories</a></li>';
    }
    
    echo '</ul>';
    echo '<div class="navbar-user">';
    
    if ($isGuest) {
        echo '<span>Welcome, Guest</span>';
        echo '<a href="login.php" class="btn-login">Login</a>';
        echo '<a href="register.php" class="btn-register">Register</a>';
    } else {
        echo '<span>Welcome, ' . htmlspecialchars($userName) . '</span>';
        echo '<a href="profile.php" class="btn-profile">Profile</a>';
        echo '<a href="logout.php" class="btn-logout">Logout</a>';
    }
    
    echo '</div>';
    echo '</div>';
    echo '</nav>';
}
?>