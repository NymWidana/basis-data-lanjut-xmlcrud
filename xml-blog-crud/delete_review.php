<?php
require_once 'includes/functions.php';

// Make sure the user is logged in and review_id is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['review_id'])) {
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

// Check if the review belongs to the current user
if (!$foundReview || ((string)$foundReview->user_id !== (string)$_SESSION['user_id'])) {
    echo "Review not found or you do not have permission to delete it.";
    exit;
}

// Get post_id (to redirect back to the blog post) then delete the review
$postId = (string)$foundReview->post_id;
deleteReview($reviewId);
header("Location: show_post.php?id=" . urlencode($postId));
exit;
?>
