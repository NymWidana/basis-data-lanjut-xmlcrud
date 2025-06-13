<?php
session_start();
include_once '../includes/functions.php';

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

// Make sure the user is logged in for actions that require authentication.
if (!isset($_SESSION['user'])) {
    header("Location: ../views/forms/login.php");
    exit;
}

// Update user profile information
if ($action === 'updateProfile') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId   = $_SESSION['user']['id'];
        $username = sanitizeInput($_POST['username']);
        $email    = sanitizeInput($_POST['email']);

        // Handle profile image upload if provided.
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

        $usersFile = '../data/users.xml';
        $usersXml = loadXMLData($usersFile);
        if ($usersXml === false) {
            die("Unable to load user data.");
        }

        $userFound = false;
        foreach ($usersXml->user as $user) {
            if ((string)$user->id === $userId) {
                // Update profile details
                $user->username = $username;
                $user->email = $email;
                if (!empty($profileImagePath)) {
                    $user->profile_image = $profileImagePath;
                }
                $userFound = true;
                // Update session variables as well
                $_SESSION['user']['username'] = $username;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['profile_image'] = $profileImagePath;
                break;
            }
        }

        if ($userFound) {
            saveXMLData($usersXml, $usersFile);
            header("Location: ../views/profile.php?updated=1");
            exit;
        } else {
            header("Location: ../views/forms/editUser.php?error=notfound");
            exit;
        }
    }
}

// Delete Account Action
if ($action === 'delete') {
    $userId = $_SESSION['user']['id'];
    $usersFile = '../data/users.xml';
    $usersXml = loadXMLData($usersFile);
    if ($usersXml === false) {
        die("Unable to load user data.");
    }
    
    // Find and remove the matching user.
    foreach ($usersXml->user as $key => $user) {
        if ((string)$user->id === (string)$userId) {
            unset($usersXml->user[$key]);
            break;
        }
    }
    
    // Save the updated XML data.
    saveXMLData($usersXml, $usersFile);
    
    // Destroy the session and redirect.
    session_destroy();
    header("Location: ../index.php?account_deleted=1");
    exit;
}
// Additional user-related actions (like viewing a profile) can be added here.
else {
    // Redirect to profile view if no valid action is specified.
    header("Location: ../views/profile.php");
    exit;
}
?>
