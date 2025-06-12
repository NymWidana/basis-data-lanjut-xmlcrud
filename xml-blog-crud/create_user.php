<?php
require_once 'includes/functions.php';

// Process registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    
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
        $targetFile = '';
    }
    
    // Register user into users.xml
    addUser($username, $email, $password, $targetFile);
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Registration</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-4">Register New User</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block text-gray-700">Username</label>
        <input type="text" name="username" required class="w-full border p-2 rounded">
      </div>
      <div>
        <label class="block text-gray-700">Email</label>
        <input type="email" name="email" required class="w-full border p-2 rounded">
      </div>
      <div>
        <label class="block text-gray-700">Password</label>
        <input type="password" name="password" required class="w-full border p-2 rounded">
      </div>
      <div>
        <label class="block text-gray-700">Profile Image</label>
        <input type="file" name="profile_image" accept="image/*" class="w-full">
      </div>
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Register</button>
    </form>
    <p class="mt-4">Already have an account? <a href="login.php" class="text-blue-500">Login here</a>.</p>
  </div>
</body>
</html>
