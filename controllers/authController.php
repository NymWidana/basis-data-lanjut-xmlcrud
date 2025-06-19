<?php
session_start();
include_once '../includes/functions.php';
include_once '../models/userModel.php'; // Use the new DOM-based user model

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// Login: verifies user credentials and initiates a session.
if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = sanitizeInput($_POST['username']);
        $password = $_POST['password'];  // Plain input; assume it's hashed in XML

        // Get the user node using our DOM-based helper.
        $userNode = getUserByUsername($username);
        if ($userNode) {
            $xpath = new DOMXPath($userNode->ownerDocument);
            $passwordNode = $xpath->query("password", $userNode)->item(0);
            if ($passwordNode && password_verify($password, $passwordNode->nodeValue)) {
                $_SESSION['user'] = [
                    'id'            => $xpath->query("id", $userNode)->item(0)->nodeValue,
                    'username'      => $xpath->query("username", $userNode)->item(0)->nodeValue,
                    'email'         => $xpath->query("email", $userNode)->item(0)->nodeValue,
                    'profile_image' => $xpath->query("profile_image", $userNode)->item(0)->nodeValue
                ];
                header("Location: ../index.php");
                exit;
            }
        }
        // Redirect back with an error if credentials don't match.
        header("Location: ../views/forms/login.php?error=1");
        exit;
    }
}

// Logout: destroys the user session.
elseif ($action === 'logout') {
    unset($_SESSION['user']);
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Registration: creates a new user and saves it using the DOM-based model.
elseif ($action === 'register') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = sanitizeInput($_POST['username']);
        $email    = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $profileImage = 'uploads/profile/default.png';  // Default profile image fallback.
        
        // Attempt to create a new user.
        if (createUser($username, $email, $hashedPassword, $profileImage)) {
            header("Location: ../views/forms/login.php?registered=1");
            exit;
        } else {
            die("Registration failed. Please try again.");
        }
    }
}

// Fallback: if no valid action is defined, redirect to home.
else {
    header("Location: ../index.php");
    exit;
}
?>
