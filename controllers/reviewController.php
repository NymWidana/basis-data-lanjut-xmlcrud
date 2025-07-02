<?php
session_start();                                // Start session to track the logged-in user
include_once '../includes/functions.php';       // Helpers: sanitizeInput(), date helpers, XML loaders
require_once '../models/reviewModel.php';       // DOM-based review model: loadReviewDOM(), createReview(), etc.

/**
 * Create a new review under a post.
 */
function handleCreate()
{
    // Only accept POST submissions
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../index.php");
        exit;
    }

    // Sanitize inputs from form
    $postId    = sanitizeInput($_POST['post_id'] ?? '');
    $comment   = sanitizeInput($_POST['comment'] ?? '');
    $userId    = $_SESSION['user']['id'];
    $createdAt = date("Y-m-d H:i:s");

    // Delegate to the DOM-based model to append <review>
    if (createReview($postId, $userId, $comment, $createdAt)) {
        // Success → back to the post’s detail page
        header("Location: ../views/post_show.php?id={$postId}");
    } else {
        // Failure → include error flag
        header("Location: ../views/post_show.php?id={$postId}&error=create");
    }
    exit;
}

/**
 * Update an existing review.
 */
function handleUpdate()
{
    // Only accept POST submissions
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../index.php");
        exit;
    }

    // Sanitize inputs
    $reviewId       = sanitizeInput($_POST['review_id'] ?? '');
    $updatedComment = sanitizeInput($_POST['comment']   ?? '');
    $postId         = sanitizeInput($_POST['post_id']   ?? '');

    // Load the review node by ID
    $reviewNode = getReviewById($reviewId);
    if (!$reviewNode) {
        // Review not found → back to post page
        header("Location: ../views/post_show.php?id={$postId}");
        exit;
    }

    // Verify ownership: only the author can update
    $dom   = $reviewNode->ownerDocument;
    $xpath = new DOMXPath($dom);
    $owner = $xpath->query("user_id", $reviewNode)->item(0)->nodeValue;
    if ($owner !== $_SESSION['user']['id']) {
        // Unauthorized → ignore
        header("Location: ../views/post_show.php?id={$postId}");
        exit;
    }

    // Delegate to DOM model to update text and timestamp
    if (updateReview($reviewId, $updatedComment, date("Y-m-d H:i:s"))) {
        header("Location: ../views/post_show.php?id={$postId}");
    } else {
        header("Location: ../views/post_show.php?id={$postId}&error=update");
    }
    exit;
}

/**
 * Delete a review.
 */
function handleDelete()
{
    // Require both review_id & post_id as GET parameters
    if (!isset($_GET['review_id'], $_GET['post_id'])) {
        header("Location: ../index.php");
        exit;
    }

    $reviewId = sanitizeInput($_GET['review_id']);
    $postId   = sanitizeInput($_GET['post_id']);

    // Load the review node
    $reviewNode = getReviewById($reviewId);
    if ($reviewNode) {
        // Check ownership before deleting
        $dom   = $reviewNode->ownerDocument;
        $xpath = new DOMXPath($dom);
        $owner = $xpath->query("user_id", $reviewNode)->item(0)->nodeValue;
        if ($owner === $_SESSION['user']['id']) {
            deleteReview($reviewId);
        }
    }

    // Return to the post’s detail page
    header("Location: ../views/post_show.php?id={$postId}");
    exit;
}

// ----------------------------------------------------------------------
// Dispatch table: maps action names to handler functions
// ----------------------------------------------------------------------
$action   = $_POST['action'] ?? $_GET['action'] ?? '';
$handlers = [
    'create' => 'handleCreate',
    'update' => 'handleUpdate',
    'delete' => 'handleDelete',
];

// Require login for any review action
if (!isset($_SESSION['user'])) {
    header("Location: ../views/forms/login.php");
    exit;
}

// Invoke the matching handler or fallback to home
if (isset($handlers[$action])) {
    $handlers[$action]();
} else {
    header("Location: ../index.php");
    exit;
}
