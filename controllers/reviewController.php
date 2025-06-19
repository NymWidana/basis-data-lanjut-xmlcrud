<?php
session_start();
include_once '../includes/functions.php';
require_once '../models/reviewModel.php';

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

// Require authentication for review operations.
if (!isset($_SESSION['user'])) {
    header("Location: ../views/forms/login.php");
    exit;
}

// ----------------------------------------------------------------------
// Create: Add a new review using DOM-based functions.
// ----------------------------------------------------------------------
if ($action === 'create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postId   = sanitizeInput($_POST['post_id']);
        $comment  = sanitizeInput($_POST['comment']);
        $userId   = $_SESSION['user']['id'];
        $createdAt = date("Y-m-d H:i:s");

        // Call the DOM-based function to create the review.
        if (createReview($postId, $userId, $comment, $createdAt)) {
            header("Location: ../views/post_show.php?id=" . $postId);
            exit;
        } else {
            // Handle error (optionally display a message)
            header("Location: ../views/post_show.php?id=" . $postId . "&error=create");
            exit;
        }
    }
}

// ----------------------------------------------------------------------
// Update: Update an existing review using DOM-based functions.
// ----------------------------------------------------------------------
elseif ($action === 'update') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reviewId       = sanitizeInput($_POST['review_id']);
        $updatedComment = sanitizeInput($_POST['comment']);
        $postId         = sanitizeInput($_POST['post_id']);

        // Retrieve the review node (using our DOM model function).
        $reviewNode = getReviewById($reviewId);
        if (!$reviewNode) {
            // Review not found: redirect back.
            header("Location: ../views/post_show.php?id=" . $postId);
            exit;
        }
        
        // Check if the logged-in user is the owner.
        $dom    = $reviewNode->ownerDocument;
        $xpath  = new DOMXPath($dom);
        /** @var DOMElement $userNode */
        $userNode = $xpath->query("user_id", $reviewNode)->item(0);
        if (!$userNode || ((string)$userNode->nodeValue !== $_SESSION['user']['id'])) {
            header("Location: ../views/post_show.php?id=" . $postId);
            exit;
        }
        
        // Update the review using our DOM-based update function.
        if (updateReview($reviewId, $updatedComment, date("Y-m-d H:i:s"))) {
            header("Location: ../views/post_show.php?id=" . $postId);
            exit;
        } else {
            header("Location: ../views/post_show.php?id=" . $postId . "&error=update");
            exit;
        }
    }
}

// ----------------------------------------------------------------------
// Delete: Remove a review using DOM-based functions.
// ----------------------------------------------------------------------
elseif ($action === 'delete') {
    if (isset($_GET['review_id']) && isset($_GET['post_id'])) {
        $reviewId = sanitizeInput($_GET['review_id']);
        $postId   = sanitizeInput($_GET['post_id']);

        $reviewNode = getReviewById($reviewId);
        if ($reviewNode) {
            $dom    = $reviewNode->ownerDocument;
            $xpath  = new DOMXPath($dom);
            /** @var DOMElement $userNode */
            $userNode = $xpath->query("user_id", $reviewNode)->item(0);
            // Only allow deletion if the review belongs to the logged-in user.
            if ($userNode && (string)$userNode->nodeValue === $_SESSION['user']['id']) {
                deleteReview($reviewId);
            }
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
