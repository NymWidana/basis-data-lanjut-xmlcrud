<?php
// views/forms/editReview.php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include_once '../components/head.php';
include_once '../components/header.php';

// Retrieve the review and post IDs from the query string.
$reviewId = isset($_GET['review_id']) ? htmlspecialchars($_GET['review_id']) : null;
$postId   = isset($_GET['post_id']) ? htmlspecialchars($_GET['post_id']) : null;

if (!$reviewId || !$postId) {
    echo "<div class='container mx-auto px-4 py-8'>
            <p class='text-center text-red-500'>Review or post not specified.</p>
          </div>";
    exit;
}

// Load the reviews data.
$reviewsFile = '../../data/reviews.xml';
if (file_exists($reviewsFile)) {
    $reviewsXml = simplexml_load_file($reviewsFile);
} else {
    echo "<div class='container mx-auto px-4 py-8'>
            <p class='text-center text-red-500'>Unable to load reviews data.</p>
          </div>";
    exit;
}

// Find the review matching the given ID.
$review = null;
foreach ($reviewsXml->review as $r) {
    if ((string)$r->id === $reviewId) {
        $review = $r;
        break;
    }
}
if (!$review) {
    echo "<div class='container mx-auto px-4 py-8'>
            <p class='text-center text-red-500'>Review not found.</p>
          </div>";
    exit;
}
?>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
            <h1 class="text-2xl font-bold mb-6 text-center">Edit Review</h1>
            <form action="../../controllers/reviewController.php?action=edit" method="POST">
                <!-- Hidden fields pass the review and post IDs to the controller -->
                <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($reviewId); ?>">
                <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($postId); ?>">
                <div class="mb-4">
                    <label for="comment" class="block text-gray-700">Your Review</label>
                    <textarea id="comment" name="comment" rows="4" required
                        class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Update your review here..."><?php echo htmlspecialchars($review->comment); ?></textarea>
                </div>
                <div>
                    <button type="submit"
                        class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 transition duration-200">
                        Update Review
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php include_once '../components/footer.php'; ?>
</body>
</html>
