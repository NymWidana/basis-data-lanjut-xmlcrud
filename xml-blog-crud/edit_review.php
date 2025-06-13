<?php
require_once 'includes/functions.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get review id from GET
if (!isset($_GET['review_id'])) {
    header("Location: index.php");
    exit;
}

$reviewId = $_GET['review_id'];
$file = 'data/reviews.xml';
$xml = loadXML($file);
$foundReview = null;
foreach ($xml->review as $review) {
    if ((string)$review->id === (string)$reviewId) {
        $foundReview = $review;
        break;
    }
}

// Check that review exists and that the logged-in user is the author of the review
if (!$foundReview || ((string)$foundReview->user_id !== (string)$_SESSION['user_id'])) {
    echo "Review not found or you do not have permission to edit it.";
    exit;
}

// Process form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newComment = trim($_POST['comment']);
    if (!empty($newComment)) {
        updateReview($reviewId, $newComment);
    }
    // Redirect back to the blog post details page after update
    header("Location: show_post.php?id=" . urlencode($foundReview->post_id));
    exit;
}
include './components/head.php'
?>

<body class="bg-gray-100">
  <?php include './components/header.php' ?>
<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-4">Edit Your Review</h2>
    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-gray-700">Your Review</label>
            <textarea name="comment" required class="w-full border p-2 rounded" rows="4"><?php echo htmlspecialchars($foundReview->comment); ?></textarea>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Review</button>
    </form>
    <div class="mt-4">
        <a href="show_post.php?id=<?php echo htmlspecialchars($foundReview->post_id); ?>" class="text-blue-500 hover:underline">&larr; Back to Post</a>
    </div>
</div>
</body>
</html>
