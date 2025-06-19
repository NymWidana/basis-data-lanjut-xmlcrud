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

// Use the DOM-based review model.
require_once '../../models/reviewModel.php';

// Load the reviews using the DOM.
$dom = loadReviewDOM();
$xpath = new DOMXPath($dom);

// Find the review node by matching the <id>'s text.
$query = sprintf("//review[id/text()='%s']", $reviewId);
$reviewNode = $xpath->query($query)->item(0);

if (!$reviewNode) {
    echo "<div class='container mx-auto px-4 py-8'>
            <p class='text-center text-red-500'>Review not found.</p>
          </div>";
    exit;
}

// Get the comment text from the review node.
$commentNode = $xpath->query("comment", $reviewNode)->item(0);
$commentText = $commentNode ? $commentNode->nodeValue : '';
?>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
            <h1 class="text-2xl font-bold mb-6 text-center">Edit Review</h1>
            <!-- Form action points to your review controller using the new model -->
            <form action="../../controllers/reviewController.php?action=update" method="POST">
                <!-- Hidden fields pass the review and post IDs to the controller -->
                <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($reviewId); ?>">
                <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($postId); ?>">
                <div class="mb-4">
                    <label for="comment" class="block text-gray-700">Your Review</label>
                    <textarea id="comment" name="comment" rows="4" required
                        class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Update your review here..."><?php echo htmlspecialchars($commentText); ?></textarea>
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
