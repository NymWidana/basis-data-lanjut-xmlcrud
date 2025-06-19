<?php
// models/reviewModel.php

/**
 * Load the reviews XML file into a DOMDocument.
 *
 * @return DOMDocument The loaded DOM document.
 */
function loadReviewDOM() {
    $xmlFile = __DIR__ . '/../data/reviews.xml';
    $dom = new DOMDocument("1.0", "UTF-8");
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    if (file_exists($xmlFile)) {
        $dom->load($xmlFile);
    } else {
        // Create a new document with root <reviews> if file is not found.
        $root = $dom->createElement("reviews");
        $dom->appendChild($root);
    }
    return $dom;
}

/**
 * Save the DOMDocument back to the reviews XML file.
 *
 * @param DOMDocument $dom The DOM document to save.
 * @return int|false Returns the number of bytes written, or false on failure.
 */
function saveReviewDOM($dom) {
    $xmlFile = __DIR__ . '/../data/reviews.xml';
    return $dom->save($xmlFile);
}

/**
 * Generate a new unique review ID based on the highest existing <review>/<id> value.
 *
 * @param DOMDocument $dom The DOM document containing the reviews.
 * @return int The new unique review ID.
 */
function generateReviewID($dom) {
    $xpath = new DOMXPath($dom);
    // Query for all <id> nodes under <review>.
    $nodes = $xpath->query("//review/id");
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
 * Create a new review and append it to the XML.
 *
 * @param string $postId The ID of the post to review.
 * @param string $userId The ID of the user making the review.
 * @param string $comment The review comment.
 * @param string $createdAt The creation timestamp (optional, default to current datetime).
 * @return bool Returns true if the review was saved successfully.
 */
function createReview($postId, $userId, $comment, $createdAt = '') {
    if (empty($createdAt)) {
        $createdAt = date("Y-m-d H:i:s");
    }
    $dom = loadReviewDOM();
    $root = $dom->documentElement; // Should be the <reviews> root.

    // Create a new <review> element.
    $reviewElem = $dom->createElement("review");

    // Generate a new unique review ID.
    $idElem = $dom->createElement("id", generateReviewID($dom));
    $postIdElem = $dom->createElement("post_id", htmlspecialchars($postId));
    $userIdElem = $dom->createElement("user_id", htmlspecialchars($userId));
    $commentElem = $dom->createElement("comment", htmlspecialchars($comment));
    $createdAtElem = $dom->createElement("created_at", $createdAt);

    // Append fields to review element.
    $reviewElem->appendChild($idElem);
    $reviewElem->appendChild($postIdElem);
    $reviewElem->appendChild($userIdElem);
    $reviewElem->appendChild($commentElem);
    $reviewElem->appendChild($createdAtElem);

    // Append review to the root <reviews>.
    $root->appendChild($reviewElem);

    return (bool) saveReviewDOM($dom);
}

/**
 * Retrieve a review DOMNode by its ID.
 *
 * @param string|int $reviewId The review ID.
 * @return DOMNode|null The review node if found, or null.
 */
function getReviewById($reviewId) {
    $dom = loadReviewDOM();
    $xpath = new DOMXPath($dom);
    $query = sprintf("//review[id='%s']", $reviewId);
    $nodeList = $xpath->query($query);
    if ($nodeList->length > 0) {
        return $nodeList->item(0);
    }
    return null;
}

/**
 * Update an existing review's comment (and optionally update its timestamp).
 *
 * @param string|int $reviewId The review ID.
 * @param string $comment The updated comment.
 * @param string $updatedAt (Optional) A new updated timestamp; if empty, current timestamp is set.
 * @return bool Returns true if the review was updated successfully.
 */
function updateReview($reviewId, $comment, $updatedAt = '') {
    if (empty($updatedAt)) {
        $updatedAt = date("Y-m-d H:i:s");
    }
    $dom = loadReviewDOM();
    $xpath = new DOMXPath($dom);
    $query = sprintf("//review[id='%s']", $reviewId);
    $nodeList = $xpath->query($query);
    
    if ($nodeList->length === 0) {
        return false;
    }
    
    $reviewNode = $nodeList->item(0);
    // Update the comment.
    $commentNodes = $xpath->query("comment", $reviewNode);
    if ($commentNodes->length > 0) {
        $commentNodes->item(0)->nodeValue = htmlspecialchars($comment);
    } else {
        // In case the element is not found (shouldn't happen), create one.
        $commentElem = $dom->createElement("comment", htmlspecialchars($comment));
        $reviewNode->appendChild($commentElem);
    }
    
    // Optionally, update the timestamp. Here we overwrite the 'created_at' element.
    $timestampNodes = $xpath->query("created_at", $reviewNode);
    if ($timestampNodes->length > 0) {
        $timestampNodes->item(0)->nodeValue = $updatedAt;
    } else {
        $timestampElem = $dom->createElement("created_at", $updatedAt);
        $reviewNode->appendChild($timestampElem);
    }
    
    return (bool) saveReviewDOM($dom);
}

/**
 * Delete a review by its ID.
 *
 * @param string|int $reviewId The review ID.
 * @return bool Returns true if deletion was successful; false otherwise.
 */
function deleteReview($reviewId) {
    $dom = loadReviewDOM();
    $xpath = new DOMXPath($dom);
    $query = sprintf("//review[id='%s']", $reviewId);
    $nodeList = $xpath->query($query);
    
    if ($nodeList->length > 0) {
        $reviewNode = $nodeList->item(0);
        $parent = $reviewNode->parentNode;
        $parent->removeChild($reviewNode);
        return (bool) saveReviewDOM($dom);
    }
    return false;
}
?>
