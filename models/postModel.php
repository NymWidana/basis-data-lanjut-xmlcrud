<?php
// models/postModel.php

/**
 * Load the posts XML file into a DOMDocument.
 *
 * @return DOMDocument The loaded DOM document.
 */
function loadPostDOM() {
    $xmlFile = __DIR__ . '/../data/posts.xml';
    $dom = new DOMDocument("1.0", "UTF-8");
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    if (file_exists($xmlFile)) {
        $dom->load($xmlFile);
    } else {
        // Create a new document with a root <posts> element.
        $root = $dom->createElement("posts");
        $dom->appendChild($root);
    }
    return $dom;
}

/**
 * Save the DOMDocument back to the posts XML file.
 *
 * @param DOMDocument $dom The DOM document to save.
 * @return int|false Returns the number of bytes written or false on failure.
 */
function savePostDOM($dom) {
    $xmlFile = __DIR__ . '/../data/posts.xml';
    return $dom->save($xmlFile);
}

/**
 * Generate a new unique post ID based on the highest existing <post>/<id> value.
 *
 * @param DOMDocument $dom The DOM document containing the posts.
 * @return int The new unique post ID.
 */
function generatePostID($dom) {
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//post/id");
    $max = 0;
    foreach ($nodes as $node) {
        $id = intval($node->nodeValue);
        if ($id > $max) {
            $max = $id;
        }
    }
    return $max + 1;
}

/**
 * Create a new post and append it to the XML.
 *
 * @param string $title The post title.
 * @param string $hero_image The hero image path.
 * @param string $content The post content.
 * @param string $author_id The ID of the post author.
 * @param string $created_at (Optional) Creation timestamp; defaults to current date and time.
 * @return bool Returns true if the post was saved successfully.
 */
function createPost($title, $hero_image, $content, $author_id, $created_at = '') {
    if (empty($created_at)) {
        $created_at = date("Y-m-d H:i:s");
    }
    $dom = loadPostDOM();
    $root = $dom->documentElement; // <posts> root element

    // Create a new <post> element.
    $postElem = $dom->createElement("post");

    // Generate a new unique ID.
    $idElem = $dom->createElement("id", generatePostID($dom));
    $titleElem = $dom->createElement("title", htmlspecialchars($title));
    $heroImageElem = $dom->createElement("hero_image", htmlspecialchars($hero_image));
    $contentElem = $dom->createElement("content", htmlspecialchars($content));
    $authorElem = $dom->createElement("author_id", htmlspecialchars($author_id));
    $createdAtElem = $dom->createElement("created_at", $created_at);

    // Initialize vote counts.
    $upvotesElem = $dom->createElement("upvotes", 0);
    $downvotesElem = $dom->createElement("downvotes", 0);
    // Create an empty <votes> element (if you plan to track individual votes).
    $votesElem = $dom->createElement("votes");

    // Append all child elements to the <post> node.
    $postElem->appendChild($idElem);
    $postElem->appendChild($titleElem);
    $postElem->appendChild($heroImageElem);
    $postElem->appendChild($contentElem);
    $postElem->appendChild($authorElem);
    $postElem->appendChild($createdAtElem);
    $postElem->appendChild($upvotesElem);
    $postElem->appendChild($downvotesElem);
    $postElem->appendChild($votesElem);

    // Append the new post to the root.
    $root->appendChild($postElem);

    return (bool) savePostDOM($dom);
}

/**
 * Retrieve a post node by its ID.
 *
 * @param string|int $postId The post ID.
 * @return DOMNode|null The post node if found; null otherwise.
 */
function getPostById($postId) {
    $dom = loadPostDOM();
    $xpath = new DOMXPath($dom);
    $query = sprintf("//post[id='%s']", $postId);
    $nodeList = $xpath->query($query);
    if ($nodeList->length > 0) {
        return $nodeList->item(0);
    }
    return null;
}

/**
 * Update an existing post's title, hero image, and content.
 *
 * @param string|int $postId The ID of the post to update.
 * @param string $title The new title.
 * @param string $hero_image The new hero image path.
 * @param string $content The new content.
 * @return bool Returns true if the post was updated successfully; false otherwise.
 */
function updatePost($postId, $title, $hero_image, $content) {
    $dom = loadPostDOM();
    $xpath = new DOMXPath($dom);
    $query = sprintf("//post[id='%s']", $postId);
    $nodeList = $xpath->query($query);
    if ($nodeList->length === 0) {
        return false;
    }
    $postNode = $nodeList->item(0);

    // Update the <title> element.
    $titleNodes = $xpath->query("title", $postNode);
    if ($titleNodes->length > 0) {
        $titleNodes->item(0)->nodeValue = htmlspecialchars($title);
    }

    // Update the <hero_image> element.
    $heroImageNodes = $xpath->query("hero_image", $postNode);
    if ($heroImageNodes->length > 0) {
        $heroImageNodes->item(0)->nodeValue = htmlspecialchars($hero_image);
    }

    // Update the <content> element.
    $contentNodes = $xpath->query("content", $postNode);
    if ($contentNodes->length > 0) {
        $contentNodes->item(0)->nodeValue = htmlspecialchars($content);
    }

    return (bool) savePostDOM($dom);
}

/**
 * Delete a post from the XML.
 *
 * @param string|int $postId The ID of the post to delete.
 * @return bool Returns true if the deletion was successful; false otherwise.
 */
function deletePost($postId) {
    $dom = loadPostDOM();
    $xpath = new DOMXPath($dom);
    $query = sprintf("//post[id='%s']", $postId);
    $nodeList = $xpath->query($query);
    if ($nodeList->length > 0) {
        $postNode = $nodeList->item(0);
        $parent = $postNode->parentNode;
        $parent->removeChild($postNode);
        return (bool) savePostDOM($dom);
    }
    return false;
}
?>
