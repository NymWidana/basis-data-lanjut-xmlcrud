<?php
// Start session if not already active
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

///////////////////////
// Existing functions
///////////////////////

// Load XML from file
function loadXML($file) {
    if (file_exists($file)) {
        return simplexml_load_file($file);
    } else {
        $rootName = basename($file, '.xml');
        $xml = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><$rootName></$rootName>");
        $xml->asXML($file);
        return $xml;
    }
}

// Save XML to file
function saveXML($xml, $file) {
    $xml->asXML($file);
}

// Generate new unique ID based on last element’s id
function getNewId($xml, $elementName) {
    $maxId = 0;
    foreach ($xml->{$elementName} as $record) {
        $id = (int)$record->id;
        if ($id > $maxId) {
            $maxId = $id;
        }
    }
    return $maxId + 1;
}

// Add a new user; now with password
function addUser($username, $email, $password, $profile_image) {
    $file = 'data/users.xml';
    $xml = loadXML($file);
    $newId = getNewId($xml, 'user');
    $user = $xml->addChild('user');
    $user->addChild('id', $newId);
    $user->addChild('username', $username);
    $user->addChild('email', $email);
    // Store hashed password
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $user->addChild('password', $hashed);
    $user->addChild('profile_image', $profile_image);
    saveXML($xml, $file);
    return $newId;
}

// Validate user credentials
function validateUser($username, $password) {
    $file = 'data/users.xml';
    $xml = loadXML($file);
    foreach ($xml->user as $user) {
        if ((string)$user->username === $username) {
            if (password_verify($password, (string)$user->password)) {
                return $user; // credentials match
            }
        }
    }
    return false;
}

// Add a new blog post
function addPost($title, $content, $hero_image, $author_id) {
    $file = 'data/posts.xml';
    $xml = loadXML($file);
    $newId = getNewId($xml, 'post');
    $post = $xml->addChild('post');
    $post->addChild('id', $newId);
    $post->addChild('title', $title);
    $post->addChild('hero_image', $hero_image);
    $post->addChild('content', $content);
    $post->addChild('author_id', $author_id);
    $post->addChild('created_at', date("Y-m-d"));
    saveXML($xml, $file);
    return $newId;
}

// Add a new review
function addReview($post_id, $user_id, $comment) {
    $file = 'data/reviews.xml';
    $xml = loadXML($file);
    $newId = getNewId($xml, 'review');
    $review = $xml->addChild('review');
    $review->addChild('id', $newId);
    $review->addChild('post_id', $post_id);
    $review->addChild('user_id', $user_id);
    $review->addChild('comment', $comment);
    $review->addChild('created_at', date("Y-m-d"));
    saveXML($xml, $file);
    return $newId;
}

//////////////////////////////////////
// New functions for updating account
//////////////////////////////////////

// Update user account data based on user id
function updateUser($id, $username, $email, $profile_image = null) {
    $file = 'data/users.xml';
    $xml = loadXML($file);
    foreach ($xml->user as $user) {
        if ((string)$user->id === (string)$id) {
            $user->username = $username;
            $user->email = $email;
            if ($profile_image !== null && $profile_image !== '') {
                $user->profile_image = $profile_image;
            }
            saveXML($xml, $file);
            return true;
        }
    }
    return false;
}

// Update an existing blog post based on post id
function updatePost($id, $title, $content, $hero_image = null) {
    $file = 'data/posts.xml';
    $xml = loadXML($file);
    foreach ($xml->post as $post) {
        if ((string)$post->id === (string)$id) {
            $post->title = $title;
            $post->content = $content;
            if ($hero_image !== null && $hero_image !== '') {
                $post->hero_image = $hero_image;
            }
            // Optionally update the timestamp or leave as is
            saveXML($xml, $file);
            return true;
        }
    }
    return false;
}

// Delete a blog post based on post id
function deletePost($id) {
    $file = 'data/posts.xml';
    $xml = loadXML($file);
    $domXml = dom_import_simplexml($xml);
    foreach ($xml->post as $post) {
        if ((string)$post->id === (string)$id) {
            $domPost = dom_import_simplexml($post);
            $domPost->parentNode->removeChild($domPost);
            // Save updated XML using DOM's owner document
            $xml->asXML($file);
            return true;
        }
    }
    return false;
}
// Update an existing review based on review id
function updateReview($reviewId, $newComment) {
    $file = 'data/reviews.xml';
    $xml = loadXML($file);
    foreach ($xml->review as $review) {
        if ((string)$review->id === (string)$reviewId) {
            $review->comment = $newComment;
            // Optionally, update the timestamp to reflect change:
            $review->updated_at = date("Y-m-d H:i:s");
            saveXML($xml, $file);
            return true;
        }
    }
    return false;
}

// Delete a review based on review id
function deleteReview($reviewId) {
    $file = 'data/reviews.xml';
    $xml = loadXML($file);
    $domXml = dom_import_simplexml($xml);
    foreach ($xml->review as $review) {
        if ((string)$review->id === (string)$reviewId) {
            $domReview = dom_import_simplexml($review);
            $domReview->parentNode->removeChild($domReview);
            $xml->asXML($file);
            return true;
        }
    }
    return false;
}

function getUserById($id) {
    $file = 'data/users.xml';
    $xml = loadXML($file);    foreach ($xml->user as $user) {
        if ((string) $user->id === (string) $id) {
            return [
                'id' => (string) $user->id,
                'name' => (string) $user->name,
                'email' => (string) $user->email
            ];
        }
    }
    return null; // User not found
}

?>
