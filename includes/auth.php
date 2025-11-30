<?php
// includes/auth.php

session_start();

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user role
 */
function current_user_role() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

/**
 * Get current user name
 */
function current_user_name() {
    return isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest';
}

/**
 * Require login, redirect if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: /project-web-s5/web-rental-outdor/public/login.php');
        exit;
    }
}

/**
 * Require specific role
 */
function require_role($role) {
    require_login();
    if (current_user_role() !== $role) {
        // If admin tries to access customer page or vice versa, redirect to their home
        if (current_user_role() === 'admin') {
            header('Location: /project-web-s5/web-rental-outdor/public/erp/index.php');
        } else {
            header('Location: /project-web-s5/web-rental-outdor/public/ecommerce/index.php');
        }
        exit;
    }
}

/**
 * Login user
 */
function login_user($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['email'] = $user['email'];
}

/**
 * Logout user
 */
function logout_user() {
    session_unset();
    session_destroy();
}
?>
