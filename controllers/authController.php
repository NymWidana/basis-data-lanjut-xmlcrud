<?php
session_start();
include_once '../includes/functions.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// Login: verifies user credentials and initiates a session.
if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = sanitizeInput($_POST['username']);
        $password = $_POST['password']; // Plain input; assume it's hashed in XML.

        $usersFile = '../data/users.xml';
        $usersXml = loadXMLData($usersFile);

        if ($usersXml === false) {
            die("Unable to load user data.");
        }

        $userFound = false;
        foreach ($usersXml->user as $user) {
            if ((string)$user->username === $username) {
                // Assume stored password is hashed. Use PHP's password_verify.
                if (password_verify($password, (string)$user->password)) {
                    $userFound = true;
                    $_SESSION['user'] = [
                        'id'       => (string)$user->id,
                        'username' => (string)$user->username,
                        'email'    => (string)$user->email,
                        'profile_image'    => (string)$user->profile_image
                    ];
                    header("Location: ../index.php");
                    exit;
                }
            }
        }
        // Redirect back with error notification if credentials don't match.
        header("Location: ../views/forms/login.php?error=1");
        exit;
    }
}

// Logout: destroys the user session.
elseif ($action === 'logout') {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Registration: creates a new user and saves it to the XML file.
elseif ($action === 'register') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = sanitizeInput($_POST['username']);
        $email    = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $profileImage = 'default.png';  // Default profile image fallback.

        $usersFile = '../data/users.xml';
        $usersXml = loadXMLData($usersFile);
        if ($usersXml === false) {
            // If file doesn't exist or is empty, create a new XML structure.
            $usersXml = new SimpleXMLElement('<users></users>');
        }

        $newUserId = generateID($usersXml, 'user');
        $newUser = $usersXml->addChild('user');
        $newUser->addChild('id', $newUserId);
        $newUser->addChild('username', $username);
        $newUser->addChild('email', $email);
        $newUser->addChild('password', $hashedPassword);
        $newUser->addChild('profile_image', $profileImage);

        saveXMLData($usersXml, $usersFile);

        header("Location: ../views/forms/login.php?registered=1");
        exit;
    }
}

else {
    // If no valid action is defined, redirect to home.
    header("Location: ../index.php");
    exit;
}
?>
