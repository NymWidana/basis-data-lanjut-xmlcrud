<?php
require_once 'includes/functions.php';

// Ensure a post ID is provided; otherwise, redirect to home.
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$postId = $_GET['id'];
$posts = loadXML('data/posts.xml');
$foundPost = null;

// Find the post with matching ID.
foreach ($posts->post as $post) {
    if ((string)$post->id === (string)$postId) {
        $foundPost = $post;
        break;
    }
}

if (!$foundPost) {
    echo "Post not found.";
    exit;
}

// Load reviews from XML.
$reviewsXml = loadXML('data/reviews.xml');
$postReviews = [];
if (isset($reviewsXml->review)) {
    foreach ($reviewsXml->review as $review) {
        if ((string)$review->post_id === (string)$postId) {
            $postReviews[] = $review;
        }
    }
}
include './components/head.php'
?>

<body class="bg-gray-100">
  <?php include './components/header.php' ?>
  <div class="container mx-auto p-4">
    <!-- Blog Post -->
    <article class="bg-white p-6 rounded shadow mb-8">
      <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($foundPost->title); ?></h1>
      <?php if (!empty($foundPost->hero_image)): ?>
        <img src="<?php echo htmlspecialchars($foundPost->hero_image); ?>" alt="Hero Image" class="w-full h-auto rounded mb-4">
      <?php endif; ?>
      <div class="mb-4">
        <?php echo nl2br(htmlspecialchars($foundPost->content)); ?>
      </div>
      <p class="text-sm text-gray-500 mb-2">Posted on <?php echo htmlspecialchars($foundPost->created_at); ?></p>
    </article>

    <!-- Review Section -->
    <section class="mb-8">
      <h2 class="text-2xl font-bold mb-4">Reviews</h2>
      
      <!-- Review Submission Button (Visible If Logged In) -->
      <?php if (isset($_SESSION['user_id'])): ?>
        <div class="mb-4">
          <a href="create_review.php?post_id=<?php echo urlencode($foundPost->id); ?>" class="text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            Write a Review
          </a>
        </div>
      <?php else: ?>
        <p class="text-gray-600 mb-4">
          Please <a href="login.php" class="text-blue-500 underline">login</a> to write a review.
        </p>
      <?php endif; ?>

      <!-- Review Listing -->
      <div class="space-y-4">
        <?php if (count($postReviews) > 0): ?>
          <?php foreach ($postReviews as $review): ?>
            <div class="bg-gray-50 p-4 rounded shadow">
              <p class="mb-1"><?php echo nl2br(htmlspecialchars($review->comment)); ?></p>
              <p class="text-sm text-gray-500">
                Reviewed on <?php echo htmlspecialchars($review->created_at); ?>
                <?php if (isset($_SESSION['user_id']) && (string)$review->user_id === (string)$_SESSION['user_id']): ?>
                  | <a href="edit_review.php?review_id=<?php echo urlencode($review->id); ?>" class="text-green-500 hover:underline">Edit</a>
                  | <a href="delete_review.php?review_id=<?php echo urlencode($review->id); ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this review?');">Delete</a>
                <?php endif; ?>
              </p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-gray-600">There are no reviews yet for this post.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- Back to Home Link -->
    <div>
      <a href="index.php" class="text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">&larr; Back to Home</a>
    </div>
  </div>
</body>
</html>
