<?php
session_start();
include_once '../includes/functions.php';

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

// Require authentication for review operations.
if (!isset($_SESSION['user'])) {
    header("Location: ../views/forms/login.php");
    exit;
}

$reviewsFile = '../data/reviews.xml';
$reviewsXml = loadXMLData($reviewsFile);
if ($reviewsXml === false) {
    // If file is not found, initialize new XML structure.
    $reviewsXml = new SimpleXMLElement('<reviews></reviews>');
}

// Handle creating a new review.
if ($action === 'create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postId  = sanitizeInput($_POST['post_id']);
        $comment = sanitizeInput($_POST['comment']);
        $userId  = $_SESSION['user']['id'];
        $createdAt = date("Y-m-d H:i:s");

        // Generate a new unique review ID.
        $newReviewId = generateID($reviewsXml, 'review');

        // Append the new review to the XML.
        $newReview = $reviewsXml->addChild('review');
        $newReview->addChild('id', $newReviewId);
        $newReview->addChild('post_id', $postId);
        $newReview->addChild('user_id', $userId);
        $newReview->addChild('comment', $comment);
        $newReview->addChild('created_at', $createdAt);

        // Save the XML file.
        saveXMLData($reviewsXml, $reviewsFile);

        header("Location: ../views/post_show.php?id=" . $postId);
        exit;
    }
}

// Handle editing an existing review.
elseif ($action === 'edit') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reviewId = sanitizeInput($_POST['review_id']);
        $updatedComment = sanitizeInput($_POST['comment']);
        $postId = sanitizeInput($_POST['post_id']);
        $found = false;

        foreach ($reviewsXml->review as $review) {
            if ((string)$review->id === $reviewId) {
                // Only allow the owner to update their review.
                if ((string)$review->user_id !== $_SESSION['user']['id']) {
                    header("Location: ../views/post_show.php?id=" . $postId);
                    exit;
                }

                // Update review comment and timestamp.
                $review->comment = $updatedComment;
                $review->created_at = date("Y-m-d H:i:s");
                $found = true;
                break;
            }
        }

        if ($found) {
            saveXMLData($reviewsXml, $reviewsFile);
        }

        header("Location: ../views/post_show.php?id=" . $postId);
        exit;
    }
}

// Handle deleting a review.
elseif ($action === 'delete') {
    if (isset($_GET['review_id']) && isset($_GET['post_id'])) {
        $reviewId = sanitizeInput($_GET['review_id']);
        $postId   = sanitizeInput($_GET['post_id']);
        $foundIndex = -1;

        // Find the review and check ownership.
        foreach ($reviewsXml->review as $key => $review) {
            if ((string)$review->id === $reviewId && (string)$review->user_id === $_SESSION['user']['id']) {
                $foundIndex = $key;
                break;
            }
        }

        if ($foundIndex !== -1) {
            unset($reviewsXml->review[$foundIndex]);
            saveXMLData($reviewsXml, $reviewsFile);
        }

        header("Location: ../views/post_show.php?id=" . $postId);
        exit;
    }
}

// Fallback: redirect to home if no valid action is provided.
else {
    header("Location: ../index.php");
    exit;
}
?>
