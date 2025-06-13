<?php
session_start();
include_once '../includes/functions.php';

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

if ($action === 'vote') {
    // Ensure the user is logged in before voting.
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Please login to vote.']);
        exit;
    }
    
    $postId = sanitizeInput($_POST['postId'] ?? '');
    $voteType = sanitizeInput($_POST['voteType'] ?? '');
    $currentUserId = $_SESSION['user']['id'];
    
    $postsFile = '../data/posts.xml';
    $postsXml = loadXMLData($postsFile);
    if (!$postsXml) {
        echo json_encode(['success' => false, 'message' => 'Could not load posts data']);
        exit;
    }
    
    $found = false;
    foreach ($postsXml->post as $post) {
        if ((string)$post->id === $postId) {
            $found = true;
            
            // Ensure a <votes> element exists under the post.
            if (!isset($post->votes)) {
                $votes = $post->addChild('votes');
            } else {
                $votes = $post->votes;
            }
            
            // Check if user already voted
            $alreadyVoted = false;
            foreach ($votes->vote as $voteEntry) {
                if ((string)$voteEntry['user_id'] === $currentUserId) {
                    $alreadyVoted = true;
                    break;
                }
            }
            
            if ($alreadyVoted) {
                // User has already voted; return current vote counts
                echo json_encode([
                    'success' => false,
                    'message' => 'You have already voted on this post.',
                    'upvotes' => (int)$post->upvotes,
                    'downvotes' => (int)$post->downvotes
                ]);
                exit;
            }
            
            // Record the new vote
            $newVote = $votes->addChild('vote');
            $newVote->addAttribute('user_id', $currentUserId);
            $newVote->addAttribute('type', $voteType);
            
            // Update the vote counts (if not already voted).
            if ($voteType === 'upvote') {
                $post->upvotes = intval($post->upvotes) + 1;
            } elseif ($voteType === 'downvote') {
                $post->downvotes = intval($post->downvotes) + 1;
            }
            
            // Save the updated XML.
            saveXMLData($postsXml, $postsFile);
            
            echo json_encode([
                'success' => true,
                'upvotes' => (int)$post->upvotes,
                'downvotes' => (int)$post->downvotes
            ]);
            exit;
        }
    }
    
    if (!$found) {
        echo json_encode(['success' => false, 'message' => 'Post not found.']);
        exit;
    }
}

// Create a new post
elseif ($action === 'create') {
    // Ensure the user is logged in.
    if (!isset($_SESSION['user'])) {
        header("Location: ../views/forms/login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title   = sanitizeInput($_POST['title']);
        $content = sanitizeInput($_POST['content']);
        $authorId= $_SESSION['user']['id'];  // Assume the session holds the user's id.

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

        $postsFile = '../data/posts.xml';
        $postsXml = loadXMLData($postsFile);
        if ($postsXml === false) {
            // If the XML doesn't exist or is empty, create a new structure.
            $postsXml = new SimpleXMLElement('<posts></posts>');
        }

        $newPostId = generateID($postsXml, 'post');
        $newPost = $postsXml->addChild('post');
        $newPost->addChild('id', $newPostId);
        $newPost->addChild('title', $title);
        $newPost->addChild('hero_image', $heroImagePath);
        $newPost->addChild('content', $content);
        $newPost->addChild('author_id', $authorId);
        $newPost->addChild('created_at', date("Y-m-d H:i:s"));
        $newPost->addChild('upvotes', 0);
        $newPost->addChild('downvotes', 0);

        saveXMLData($postsXml, $postsFile);

        header("Location: ../index.php");
        exit;
    }
}

// Additional actions for editing or deleting posts can be handled here.

else {
    header("Location: ../index.php");
    exit;
}
?>
