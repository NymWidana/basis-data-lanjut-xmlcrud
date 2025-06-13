<?php
require_once 'includes/functions.php';

// Ensure the user is logged in. If not redirect to login.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Load the current user's data from users.xml
$users = loadXML('data/users.xml');
$currentUser = null;
foreach ($users->user as $user) {
    if ((string)$user->id === (string)$userId) {
        $currentUser = $user;
        break;
    }
}

// Load blog posts authored by the current user from posts.xml
$postsXml  = loadXML('data/posts.xml');
$userPosts = [];
if (isset($postsXml->post)) {
    foreach ($postsXml->post as $post) {
        if ((string)$post->author_id === (string)$userId) {
            $userPosts[] = $post;
        }
    }
}
include './components/head.php'
?>
<body class="bg-gray-100">
  <?php include './components/header.php' ?>
  <main class="container mx-auto p-4">
    <!-- User Information -->
    <div class="flex items-center space-x-4">
      <img src="<?php echo htmlspecialchars($currentUser->profile_image); ?>" alt="Profile Image" class="w-20 h-20 rounded-full">
      <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($currentUser->username); ?></h2>
        <p><?php echo htmlspecialchars($currentUser->email); ?></p>
        <a href="edit_account.php" class="text-blue-500 hover:underline">Edit Account</a>
      </div>
    </div>

    <!-- User's Blog Posts -->
    <h3 class="mt-8 text-xl font-bold">My Blog Posts</h3>
    <?php if (count($userPosts) > 0): ?>
      <ul class="space-y-4 mt-4">
        <?php foreach ($userPosts as $post): ?>
          <li class="bg-white p-4 rounded shadow">
            <h4 class="text-lg font-bold"><?php echo htmlspecialchars($post->title); ?></h4>
            <p class="text-gray-500">Posted on <?php echo htmlspecialchars($post->created_at); ?></p>
            <div class="mt-2">
              <a href="show_post.php?id=<?php echo htmlspecialchars($post->id); ?>" class="text-blue-500 hover:underline">View</a> |
              <a href="edit_post.php?id=<?php echo htmlspecialchars($post->id); ?>" class="text-green-500 hover:underline">Edit</a> |
              <a href="delete_post.php?id=<?php echo htmlspecialchars($post->id); ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="mt-4">You have not created any blog posts yet.</p>
    <?php endif; ?>

    <!-- Back to Home Link -->
    <div class="mt-8">
      <a href="index.php" class="text-blue-500 hover:underline">&larr; Back to Home</a>
    </div>
  </main>
</body>
</html>
