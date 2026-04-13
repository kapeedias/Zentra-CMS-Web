<?php
// Start the session if you plan to check for logged-in users
session_start();

// Optional: check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: myaccount.php");
    exit;
}

// Redirect to login.php
header("Location: login.php");
exit; // Always call exit after redirect to stop further execution