<?php
session_start();                              // Start session to track authentication state
include_once '../includes/functions.php';     // Helpers: sanitizeInput(), etc.
require_once '../models/userModel.php';       // DOM-based user model: getUserByUsername(), createUser()

/**
 * Handle user login: verify credentials and initialize session.
 */
function handleLogin()
{
    // Only accept POST submissions
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../views/forms/login.php");
        exit;
    }

    // Sanitize inputs from login form
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';  // Plain-text; XML stores hashed password

    // Look up the <user> node by username
    $userNode = getUserByUsername($username);
    if ($userNode) {
        $dom    = $userNode->ownerDocument;
        $xpath  = new DOMXPath($dom);
        $pwNode = $xpath->query("password", $userNode)->item(0);

        // Verify password hash
        if ($pwNode && password_verify($password, $pwNode->nodeValue)) {
            // Fetch user details into session
            $_SESSION['user'] = [
                'id'            => $xpath->query("id",            $userNode)->item(0)->nodeValue,
                'username'      => $xpath->query("username",      $userNode)->item(0)->nodeValue,
                'email'         => $xpath->query("email",         $userNode)->item(0)->nodeValue,
                'profile_image' => $xpath->query("profile_image",$userNode)->item(0)->nodeValue
            ];
            header("Location: ../index.php");
            exit;
        }
    }

    // Invalid credentials: redirect back with error flag
    header("Location: ../views/forms/login.php?error=1");
    exit;
}

/**
 * Handle user logout: clear session and redirect home.
 */
function handleLogout()
{
    unset($_SESSION['user']);  // Remove user data
    session_destroy();          // Destroy entire session
    header("Location: ../index.php");
    exit;
}

/**
 * Handle user registration: create a new user in XML and redirect.
 */
function handleRegister()
{
    // Only accept POST submissions
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../views/forms/register.php");
        exit;
    }

    // Sanitize and collect form inputs
    $username = sanitizeInput($_POST['username'] ?? '');
    $email    = sanitizeInput($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';
    $hashed   = password_hash($password, PASSWORD_DEFAULT);
    // Default profile image for new users
    $defaultAvatar = 'uploads/profile/default.png';

    // Delegate creation to DOM-based model
    if (createUser($username, $email, $hashed, $defaultAvatar)) {
        // On success, redirect to login with a 'registered' flag
        header("Location: ../views/forms/login.php?registered=1");
        exit;
    }

    // Creation failed: show an error
    die("Registration failed. Please try again.");
}

/**
 * Dispatch table mapping URL actions to handlers.
 */
$action   = $_GET['action'] ?? $_POST['action'] ?? '';
$handlers = [
    'login'    => 'handleLogin',
    'logout'   => 'handleLogout',
    'register' => 'handleRegister',
];

// Invoke the matching handler or redirect home on invalid action
if (isset($handlers[$action])) {
    $handlers[$action]();
} else {
    header("Location: ../index.php");
    exit;
}
