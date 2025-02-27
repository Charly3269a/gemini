<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_log("check_auth.php: Checking authentication..."); // Added log at start

if (isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role'])) {
    error_log("check_auth.php: User is authenticated (User ID: " . $_SESSION['user_id'] . ", Username: " . $_SESSION['username'] . ", Role: " . $_SESSION['role'] . ")"); // Log if authenticated
    return true; // User is authenticated
} else {
    error_log("check_auth.php: User is NOT authenticated, redirecting to login.php"); // Log if not authenticated
    header('Location: /bolt/auth/login.php'); //  redirect to login.php
    exit; //  exit
}
?>
