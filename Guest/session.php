<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isMember() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'member';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

function getUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

function getUserRole() {
    return $_SESSION['user_role'] ?? 'guest';
}
?>