<?php
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$users = loadXML('data/users.xml');
$currentUser = null;
foreach ($users->user as $user) {
    if ((string)$user->id === $userId) {
        $currentUser = $user;
        break;
    }
}

// Process account update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $uploadDir = 'uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = basename($_FILES['profile_image']['name']);
        $targetFile = $uploadDir . time() . '_' . $filename;
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile);
    } else {
        $targetFile = ''; // keep existing if not provided
    }
    updateUser($userId, $username, $email, ($targetFile ? $targetFile : null));
    // Update session username if changed
    $_SESSION['username'] = $username;
    header("Location: profile.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Account</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-4">Edit Account</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block text-gray-700">Username</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($currentUser->username); ?>" required class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block text-gray-700">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($currentUser->email); ?>" required class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block text-gray-700">Profile Image (upload new to change)</label>
            <input type="file" name="profile_image" accept="image/*" class="w-full">
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Account</button>
    </form>
    <div class="mt-4">
        <a href="profile.php" class="text-blue-500 hover:underline">&larr; Back to Profile</a>
    </div>
</div>
</body>
</html>
