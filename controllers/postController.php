<?php
session_start();
include_once '../includes/functions.php';
require_once '../models/postModel.php';

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

// ----------------------------------------------------------------------
// Vote: Process an upvote/downvote on a post using the DOM model
// ----------------------------------------------------------------------
if ($action === 'vote') {
    // Ensure the user is logged in before voting.
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Please login to vote.']);
        exit;
    }
    
    $postId   = sanitizeInput($_POST['postId'] ?? '');
    $voteType = sanitizeInput($_POST['voteType'] ?? '');
    $currentUserId = $_SESSION['user']['id'];
    
    // Validate vote type.
    if (!in_array($voteType, ['upvote', 'downvote'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid vote type.']);
        exit;
    }
    
    // Load the posts DOM document using our post model function.
    $dom = loadPostDOM();
    $xpath = new DOMXPath($dom);
    
    // Query for the post with the given id.
    $query  = sprintf("//post[id/text()='%s']", $postId);
    $postNode = $xpath->query($query)->item(0);
    if (!$postNode) {
        echo json_encode(['success' => false, 'message' => 'Post not found.']);
        exit;
    }
    
    // Ensure that a <votes> element exists under the post.
    $votesNodes = $xpath->query("votes", $postNode);
    if ($votesNodes->length === 0) {
        $votesElem = $dom->createElement("votes");
        $postNode->appendChild($votesElem);
    } else {
        $votesElem = $votesNodes->item(0);
    }
    
    // Check if the current user has already voted.
    $voteQuery = sprintf("vote[@user_id='%s']", $currentUserId);
    $existingVotes = $xpath->query($voteQuery, $votesElem);
    
    if ($existingVotes->length === 0) {
        // No previous vote: record the new vote.
        $newVote = $dom->createElement("vote");
        $newVote->setAttribute("user_id", $currentUserId);
        $newVote->setAttribute("type", $voteType);
        $votesElem->appendChild($newVote);
        
        // Update vote counts.
        $upvotesNodes = $xpath->query("upvotes", $postNode);
        $downvotesNodes = $xpath->query("downvotes", $postNode);
        if ($voteType === 'upvote' && $upvotesNodes->length > 0) {
            $currentUpvotes = intval($upvotesNodes->item(0)->nodeValue);
            $upvotesNodes->item(0)->nodeValue = $currentUpvotes + 1;
        } elseif ($voteType === 'downvote' && $downvotesNodes->length > 0) {
            $currentDownvotes = intval($downvotesNodes->item(0)->nodeValue);
            $downvotesNodes->item(0)->nodeValue = $currentDownvotes + 1;
        }
    } else {
        // A previous vote exists.
        $existingVote = $existingVotes->item(0);
        
        /** @var DOMElement|null $existingVote */
        $currentVoteType = $existingVote->getAttribute("type");
        
        if ($currentVoteType === $voteType) {
            // Same vote clicked: cancel the vote.
            if ($voteType === 'upvote') {
                $upvotesNodes = $xpath->query("upvotes", $postNode);
                if ($upvotesNodes->length > 0) {
                    $currentUpvotes = intval($upvotesNodes->item(0)->nodeValue);
                    $upvotesNodes->item(0)->nodeValue = max(0, $currentUpvotes - 1);
                }
            } else {
                $downvotesNodes = $xpath->query("downvotes", $postNode);
                if ($downvotesNodes->length > 0) {
                    $currentDownvotes = intval($downvotesNodes->item(0)->nodeValue);
                    $downvotesNodes->item(0)->nodeValue = max(0, $currentDownvotes - 1);
                }
            }
            $votesElem->removeChild($existingVote);
        } else {
            // Different vote: switch the vote.
            if ($currentVoteType === 'upvote') {
                // Switch from upvote to downvote.
                $upvotesNodes = $xpath->query("upvotes", $postNode);
                if ($upvotesNodes->length > 0) {
                    $currentUpvotes = intval($upvotesNodes->item(0)->nodeValue);
                    $upvotesNodes->item(0)->nodeValue = max(0, $currentUpvotes - 1);
                }
                $downvotesNodes = $xpath->query("downvotes", $postNode);
                if ($downvotesNodes->length > 0) {
                    $currentDownvotes = intval($downvotesNodes->item(0)->nodeValue);
                    $downvotesNodes->item(0)->nodeValue = $currentDownvotes + 1;
                }
            } else {
                // Switch from downvote to upvote.
                $downvotesNodes = $xpath->query("downvotes", $postNode);
                if ($downvotesNodes->length > 0) {
                    $currentDownvotes = intval($downvotesNodes->item(0)->nodeValue);
                    $downvotesNodes->item(0)->nodeValue = max(0, $currentDownvotes - 1);
                }
                $upvotesNodes = $xpath->query("upvotes", $postNode);
                if ($upvotesNodes->length > 0) {
                    $currentUpvotes = intval($upvotesNodes->item(0)->nodeValue);
                    $upvotesNodes->item(0)->nodeValue = $currentUpvotes + 1;
                }
            }
            // Update the existing vote type.
            $existingVote->setAttribute("type", $voteType);
        }
    }
    
    // Save the updated post XML.
    savePostDOM($dom);
    
    // Retrieve the updated vote counts.
    $upvotesNode = $xpath->query("upvotes", $postNode)->item(0);
    $downvotesNode = $xpath->query("downvotes", $postNode)->item(0);
    
    echo json_encode([
        'success'   => true,
        'upvotes'   => intval($upvotesNode->nodeValue),
        'downvotes' => intval($downvotesNode->nodeValue)
    ]);
    exit;
}

// ----------------------------------------------------------------------
// Create: Create a new post using the DOM-based post model.
// ----------------------------------------------------------------------
elseif ($action === 'create') {
    // Ensure the user is logged in.
    if (!isset($_SESSION['user'])) {
        header("Location: ../views/forms/login.php");
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title   = sanitizeInput($_POST['title']);
        $content = sanitizeInput($_POST['content']);
        $authorId= $_SESSION['user']['id'];
        
        // Handle file upload for the hero image.
        $heroImagePath = '';
        if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] == 0) {
            $uploadDir = '../uploads/post/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename   = time() . '_' . basename($_FILES['hero_image']['name']);
            $targetFile = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $targetFile)) {
                $heroImagePath = 'uploads/post/' . $filename;
            }
        }
        
        // Use our DOM-based post model to create the new post.
        if (createPost($title, $heroImagePath, $content, $authorId)) {
            header("Location: ../index.php");
        } else {
            echo "Failed to create post.";
        }
        exit;
    }
}

// ----------------------------------------------------------------------
// Update: Update an existing post using the DOM-based post model.
// ----------------------------------------------------------------------
elseif ($action === 'update') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postId = sanitizeInput($_POST['id'] ?? '');
        $title = sanitizeInput($_POST['title'] ?? '');
        $content = sanitizeInput($_POST['content'] ?? '');
        $authorId = $_SESSION['user']['id'];
        
        // Handle file upload for a new hero image, if provided.
        $heroImagePath = '';
        if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] == 0) {
            $uploadDir = '../uploads/post/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename   = time() . '_' . basename($_FILES['hero_image']['name']);
            $targetFile = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $targetFile)) {
                $heroImagePath = 'uploads/post/' . $filename;
            }
        }
        
        // Load the posts DOM.
        $dom = loadPostDOM();
        $xpath = new DOMXPath($dom);
        
        // Locate the post by id.
        $query = sprintf("//post[id/text()='%s']", $postId);
        $postNode = $xpath->query($query)->item(0);
        if (!$postNode) {
            header("Location: ../views/profile.php?error=postnotfound");
            exit;
        }
        
        // Check if the current user is the owner of the post.
        $authorNode = $xpath->query("author_id", $postNode)->item(0);
        if ((string)$authorNode->nodeValue !== $authorId) {
            header("Location: ../views/profile.php?error=unauthorized");
            exit;
        }
        
        // Update the title.
        $titleNode = $xpath->query("title", $postNode)->item(0);
        if ($titleNode) {
            $titleNode->nodeValue = $title;
        }
        
        // Update the content.
        $contentNode = $xpath->query("content", $postNode)->item(0);
        if ($contentNode) {
            $contentNode->nodeValue = $content;
        }
        
        // Update the hero image if a new one was uploaded.
        if (!empty($heroImagePath)) {
            $heroImageNodes = $xpath->query("hero_image", $postNode);
            if ($heroImageNodes->length > 0) {
                $heroImageNodes->item(0)->nodeValue = $heroImagePath;
            } else {
                $newImageNode = $dom->createElement("hero_image", $heroImagePath);
                $postNode->appendChild($newImageNode);
            }
        }
        
        // Save the updated post XML.
        savePostDOM($dom);
        header("Location: ../views/profile.php?updated=1");
        exit;
    }
}

// ----------------------------------------------------------------------
// Delete: Delete a post (only if the logged in user is the owner).
// ----------------------------------------------------------------------
elseif ($action === 'delete') {
    if (isset($_GET['post_id'])) {
        $postId = sanitizeInput($_GET['post_id']);
        
        // Retrieve the post using the DOM-based model.
        $postNode = getPostById($postId);
        if ($postNode) {
            $xpath = new DOMXPath($postNode->ownerDocument);
            $authorNode = $xpath->query("author_id", $postNode)->item(0);
            
            // Only delete if the current user is the author.
            if ((string)$authorNode->nodeValue === $_SESSION['user']['id']) {
                deletePost($postId);
            }
        }
        
        header("Location: ../views/profile.php");
        exit;
    }
}

// Fallback: redirect to home if no valid action is provided.
else {
    header("Location: ../index.php");
    exit;
}
?>
