<?php
require_once 'includes/functions.php';

// Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the post ID from GET (we expect a URL like: create_review.php?post_id=3)
$post_id = $_GET['post_id'] ?? '';
if (empty($post_id)) {
    header("Location: index.php");
    exit;
}

// Process the review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = trim($_POST['comment'] ?? '');
    if (!empty($comment)) {
        addReview($post_id, $_SESSION['user_id'], $comment);
    }
    // Redirect back to the post details page after submission
    header("Location: show_post.php?id=" . $post_id);
    exit;
}
include './components/head.php'
?>

<body class="bg-gray-100">
  <?php include './components/header.php' ?>
  <div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-4">Write a Review</h2>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700">Your Review</label>
        <textarea name="comment" required class="w-full border p-2 rounded" rows="4"></textarea>
      </div>
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Submit Review</button>
    </form>
    <div class="mt-4">
      <a href="show_post.php?id=<?php echo htmlspecialchars($post_id); ?>" class="text-blue-500 hover:underline">&larr; Back to Post</a>
    </div>
  </div>
</body>
</html>
