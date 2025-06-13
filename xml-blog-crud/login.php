<?php
require_once 'includes/functions.php';

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $user = validateUser($username, $password);
    if ($user) {
        // Credentials valid; set session variables
        $_SESSION['user_id']   = (string)$user->id;
        $_SESSION['username']  = (string)$user->username;
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
include './components/head.php'
?>
<body class="bg-gray-100">
  <div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-4">Login</h2>
    <?php if (isset($error)): ?>
      <p class="text-red-500 mb-4"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700">Username</label>
        <input type="text" name="username" required class="w-full border p-2 rounded">
      </div>
      <div>
        <label class="block text-gray-700">Password</label>
        <input type="password" name="password" required class="w-full border p-2 rounded">
      </div>
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Login</button>
    </form>
    <p class="mt-4">Don't have an account? <a href="create_user.php" class="text-blue-500">Register here</a>.</p>
  </div>
</body>
</html>
