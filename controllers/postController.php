<?php
session_start();                            // Start PHP session to track logged-in user
include_once '../includes/functions.php';   // Include shared helper functions (e.g., sanitizeInput, XML loaders)
require_once '../models/postModel.php';     // Include the DOM-based post model (loadPostDOM, savePostDOM, createPost, etc.)

/**
 * Subroutine: record a new <vote> element and bump count.
 */
function recordNewVote($dom, $xpath, $post, $votesElem, $userId, $type)
{
    // Create <vote user_id="..." type="..."/>
    $newVote = $dom->createElement("vote");
    $newVote->setAttribute("user_id", $userId);
    $newVote->setAttribute("type",    $type);
    $votesElem->appendChild($newVote);

    // Increment the matching <upvotes> or <downvotes> count
    $countNode = $xpath->query($type . 's', $post)->item(0);
    $countNode->nodeValue = intval($countNode->nodeValue) + 1;
}

/**
 * Subroutine: toggle or switch an existing vote element.
 */
function toggleVote($dom, $xpath, $post, $votesElem, $existingVote, $newType)
{
    // Determine the old vote type
    $oldType   = $existingVote->getAttribute("type");
    $upNode    = $xpath->query("upvotes",   $post)->item(0);
    $downNode  = $xpath->query("downvotes", $post)->item(0);

    // Decrement the old vote counter
    if ($oldType === 'upvote') {
        $upNode->nodeValue = max(0, intval($upNode->nodeValue) - 1);
    } else {
        $downNode->nodeValue = max(0, intval($downNode->nodeValue) - 1);
    }

    if ($oldType === $newType) {
        // Same vote clicked again → remove it entirely
        $votesElem->removeChild($existingVote);
    } else {
        // Switching vote type → update attribute and increment new counter
        $existingVote->setAttribute('type', $newType);
        $targetNode = ($newType === 'upvote' ? $upNode : $downNode);
        $targetNode->nodeValue = intval($targetNode->nodeValue) + 1;
    }
}

/**
 * Handle AJAX voting on a post.
 */
function handleVote()
{
    // 1. Ensure user is logged in
    if (!isset($_SESSION['user'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Please login to vote.'
        ]);
        exit;
    }

    // 2. Sanitize incoming POST parameters
    $postId      = sanitizeInput($_POST['postId']   ?? '');
    $voteType    = sanitizeInput($_POST['voteType'] ?? '');
    $currentUser = $_SESSION['user']['id'];

    // 3. Validate vote type
    if (!in_array($voteType, ['upvote','downvote'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid vote type.'
        ]);
        exit;
    }

    // 4. Load the XML DOM and prepare XPath
    $dom   = loadPostDOM();               
    $xpath = new DOMXPath($dom);

    // 5. Locate the <post> node by ID
    $post = $xpath
        ->query(sprintf("//post[id/text()='%s']", $postId))
        ->item(0);
    if (!$post) {
        echo json_encode([
            'success' => false,
            'message' => 'Post not found.'
        ]);
        exit;
    }

    // 6. Ensure a <votes> container exists, or create it
    $votesList = $xpath->query("votes", $post);
    if ($votesList->length === 0) {
        $votesElem = $dom->createElement("votes");
        $post->appendChild($votesElem);
    } else {
        $votesElem = $votesList->item(0);
    }

    // 7. Check for an existing <vote> by this user
    $existing = $xpath
        ->query(sprintf("vote[@user_id='%s']", $currentUser), $votesElem);

    // 8. If no previous vote, record a new one; else toggle or switch it
    if ($existing->length === 0) {
        recordNewVote($dom, $xpath, $post, $votesElem, $currentUser, $voteType);
    } else {
        toggleVote($dom, $xpath, $post, $votesElem, $existing->item(0), $voteType);
    }

    // 9. Persist changes back to posts.xml
    savePostDOM($dom);

    // 10. Fetch updated <upvotes> & <downvotes> counts for JSON response
    $upNode   = $xpath->query("upvotes",   $post)->item(0);
    $downNode = $xpath->query("downvotes", $post)->item(0);

    echo json_encode([
        'success'   => true,
        'upvotes'   => intval($upNode->nodeValue),
        'downvotes' => intval($downNode->nodeValue),
    ]);
    exit;
}

/**
 * Handle creation of a new post.
 */
function handleCreate()
{
    // Redirect guests to login form
    if (!isset($_SESSION['user'])) {
        header("Location: ../views/forms/login.php");
        exit;
    }

    // Only respond to POST submissions
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../index.php");
        exit;
    }

    // Sanitize title and content
    $title    = sanitizeInput($_POST['title']   ?? '');
    $content  = sanitizeInput($_POST['content'] ?? '');
    $authorId = $_SESSION['user']['id'];

    // Process optional hero image upload and get relative path
    $heroPath = uploadFile($_FILES['hero_image'] ?? null, '../uploads/post/');

    // Delegate to model to append a new <post> node in XML
    if (createPost($title, $heroPath, $content, $authorId)) {
        header("Location: ../index.php");  // Success → back to home
    } else {
        echo "Failed to create post.";     // Show error on failure
    }
    exit;
}

/**
 * Handle updating an existing post.
 */
function handleUpdate()
{
    // Only handle POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../index.php");
        exit;
    }

    // Sanitize inputs
    $postId   = sanitizeInput($_POST['id']      ?? '');
    $title    = sanitizeInput($_POST['title']   ?? '');
    $content  = sanitizeInput($_POST['content'] ?? '');
    $authorId = $_SESSION['user']['id'];

    // Optional new hero image
    $heroPath = uploadFile($_FILES['hero_image'] ?? null, '../uploads/post/');

    // Load DOM & find the post node
    $dom   = loadPostDOM();
    $xpath = new DOMXPath($dom);
    $post  = $xpath
        ->query(sprintf("//post[id/text()='%s']", $postId))
        ->item(0);

    // If post missing or user not author, abort
    if (!$post) {
        header("Location: ../views/profile.php?error=postnotfound");
        exit;
    }
    if ($xpath->query("author_id", $post)->item(0)->nodeValue !== $authorId) {
        header("Location: ../views/profile.php?error=unauthorized");
        exit;
    }

    // Update <title> and <content>
    $xpath->query("title",   $post)->item(0)->nodeValue   = $title;
    $xpath->query("content", $post)->item(0)->nodeValue   = $content;

    // Update or append <hero_image>
    if ($heroPath) {
        $imgNodes = $xpath->query("hero_image", $post);
        if ($imgNodes->length) {
            $imgNodes->item(0)->nodeValue = $heroPath;
        } else {
            $post->appendChild($dom->createElement("hero_image", $heroPath));
        }
    }

    // Save changes and return to profile
    savePostDOM($dom);
    header("Location: ../views/profile.php?updated=1");
    exit;
}

/**
 * Handle deletion of a post.
 */
function handleDelete()
{
    // Ensure we have a post_id query parameter
    if (!isset($_GET['post_id'])) {
        header("Location: ../index.php");
        exit;
    }
    $postId = sanitizeInput($_GET['post_id']);
    $post   = getPostById($postId);  // Returns DOMElement or null

    if ($post) {
        // Verify current user is the author before deleting
        $xpath      = new DOMXPath($post->ownerDocument);
        $authorNode = $xpath->query("author_id", $post)->item(0);
        if ($authorNode->nodeValue === $_SESSION['user']['id']) {
            deletePost($postId);
        }
    }

    // Redirect back to profile
    header("Location: ../views/profile.php");
    exit;
}

/**
 * Helper: move uploaded file to destination dir.
 * Returns relative path on success, or empty string on failure.
 */
function uploadFile($file, $destDir)
{
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return '';
    }
    // Create directory if missing
    if (!is_dir($destDir)) {
        mkdir($destDir, 0777, true);
    }
    // Build unique filename
    $filename   = time() . '_' . basename($file['name']);
    $targetFile = $destDir . $filename;

    // Move uploaded file and return its public path
    return move_uploaded_file($file['tmp_name'], $targetFile)
        ? 'uploads/post/' . $filename
        : '';
}

/**
 * Dispatch mapping: action → handler function
 */
$action   = $_POST['action'] ?? $_GET['action'] ?? '';
$handlers = [
    'vote'   => 'handleVote',
    'create' => 'handleCreate',
    'update' => 'handleUpdate',
    'delete' => 'handleDelete',
];

// Call the appropriate handler, or default to home
if (isset($handlers[$action])) {
    $handlers[$action]();
} else {
    header("Location: ../index.php");
    exit;
}

