<?php
session_start();                              // Start session to track authenticated user
include_once '../includes/functions.php';     // Helpers: sanitizeInput(), etc.
require_once '../models/userModel.php';       // DOM-based user model: loadUserDOM(), updateUser(), deleteUser()

/**
 * Handle updating the current user’s profile.
 */
function handleUpdateProfile()
{
    // Only accept POST requests for profile updates
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../views/profile.php");
        exit;
    }

    // Grab current user ID from session
    $userId   = $_SESSION['user']['id'];

    // Sanitize incoming form data
    $username = sanitizeInput($_POST['username'] ?? '');
    $email    = sanitizeInput($_POST['email']    ?? '');

    // Process optional profile image upload
    $profileImagePath = uploadFile($_FILES['profile_image'] ?? null, '../uploads/profile/');

    // Delegate the update to the DOM-based model
    $success = updateUser($userId, $username, $email, $profileImagePath);

    if ($success) {
        // Sync session data with updated values
        $_SESSION['user']['username']      = $username;
        $_SESSION['user']['email']         = $email;
        if ($profileImagePath) {
            $_SESSION['user']['profile_image'] = $profileImagePath;
        }
        header("Location: ../views/profile.php?updated=1");
    } else {
        // User not found or update failed
        header("Location: ../views/forms/editUser.php?error=notfound");
    }
    exit;
}

/**
 * Handle deleting the current user’s account.
 */
function handleDeleteAccount()
{
    // Get current user ID
    $userId = $_SESSION['user']['id'];

    // Delegate deletion to DOM-based model
    $deleted = deleteUser($userId);

    // Destroy session regardless of deletion result
    session_destroy();

    // Redirect to home with an account_deleted flag
    header("Location: ../index.php?account_deleted=1");
    exit;
}

/**
 * Helper: move an uploaded file to destination directory.
 * Returns the relative file path on success, or empty string if none.
 */
function uploadFile($file, $destDir)
{
    // No file provided or upload error
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return '';
    }

    // Ensure destination directory exists
    if (!is_dir($destDir)) {
        mkdir($destDir, 0777, true);
    }

    // Generate a unique filename
    $filename   = time() . '_' . basename($file['name']);
    $targetPath = $destDir . $filename;

    // Move the uploaded file; return its public-facing path
    return move_uploaded_file($file['tmp_name'], $targetPath)
        ? 'uploads/profile/' . $filename
        : '';
}

// ----------------------------------------------------------------------
// Dispatch table: map actions to handler functions
// ----------------------------------------------------------------------
$action   = $_POST['action'] ?? $_GET['action'] ?? '';
$handlers = [
    'updateProfile' => 'handleUpdateProfile',
    'delete'        => 'handleDeleteAccount',
];

// Require authentication for any user action
if (!isset($_SESSION['user'])) {
    header("Location: ../views/forms/login.php");
    exit;
}

// Invoke the matched handler or fallback to profile page
if (isset($handlers[$action])) {
    $handlers[$action]();
} else {
    header("Location: ../views/profile.php");
    exit;
}
