<?php
session_start();
include_once '../includes/functions.php';
require_once '../models/userModel.php';  // Contains DOM functions for user management

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

// Ensure user is logged in.
if (!isset($_SESSION['user'])) {
    header("Location: ../views/forms/login.php");
    exit;
}

// ----------------------------------------------------------------------
// Update Profile: Use the DOM-based updateUser() function.
// ----------------------------------------------------------------------
if ($action === 'updateProfile') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId   = $_SESSION['user']['id'];
        $username = sanitizeInput($_POST['username']);
        $email    = sanitizeInput($_POST['email']);
        
        // Handle profile image upload, if provided.
        $profileImagePath = '';
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $uploadDir = '../uploads/profile/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename   = time() . '_' . basename($_FILES['profile_image']['name']);
            $targetFile = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
                $profileImagePath = 'uploads/profile/' . $filename;
            }
        }
        
        // Call the DOM-based update function.
        $updateResult = updateUser($userId, $username, $email, $profileImagePath);
        if ($updateResult) {
            // Update session variables to reflect the changes.
            $_SESSION['user']['username'] = $username;
            $_SESSION['user']['email'] = $email;
            if (!empty($profileImagePath)) {
                $_SESSION['user']['profile_image'] = $profileImagePath;
            }
            header("Location: ../views/profile.php?updated=1");
            exit;
        } else {
            header("Location: ../views/forms/editUser.php?error=notfound");
            exit;
        }
    }
}

// ----------------------------------------------------------------------
// Delete Account: Use the DOM-based deleteUser() function.
// ----------------------------------------------------------------------
elseif ($action === 'delete') {
    $userId = $_SESSION['user']['id'];
    $deleteResult = deleteUser($userId);
    
    // Destroy the session and redirect after deletion.
    session_destroy();
    header("Location: ../index.php?account_deleted=1");
    exit;
}

// ----------------------------------------------------------------------
// Fallback: if no valid action is provided, redirect to profile.
// ----------------------------------------------------------------------
else {
    header("Location: ../views/profile.php");
    exit;
}
?>
