<?php
// models/userModel.php

/**
 * Load the users XML file into a DOMDocument.
 *
 * @return DOMDocument The loaded DOM document.
 */
function loadUserDOM() {
    $xmlFile = __DIR__ . '/../data/users.xml';
    $dom = new DOMDocument("1.0", "UTF-8");
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    if (file_exists($xmlFile)) {
        $dom->load($xmlFile);
    } else {
        // Create a new document with root <users>
        $root = $dom->createElement("users");
        $dom->appendChild($root);
    }
    return $dom;
}

/**
 * Save the DOMDocument to the users XML file.
 *
 * @param DOMDocument $dom The DOM document to save.
 * @return int|false Returns the number of bytes written or false on failure.
 */
function saveUserDOM($dom) {
    $xmlFile = __DIR__ . '/../data/users.xml';
    return $dom->save($xmlFile);
}

function getNextUserIDFromXML($dom) {
    $root = $dom->documentElement;
    $nextID = intval($root->getAttribute('next_id'));
    if (!$nextID) {
        $nextID = 1; // Default starting value.
    }
    $root->setAttribute('next_id', $nextID + 1);
    return $nextID;
}


/**
 * Create a new user.
 *
 * @param string $username The username.
 * @param string $email The email address.
 * @param string $hashedPassword The hashed password.
 * @param string $profileImage (Optional) The profile image file path.
 * @return bool Returns true if saved successfully.
 */
function createUser($username, $email, $hashedPassword, $profileImage = 'default.png') {
    $dom = loadUserDOM();
    $root = $dom->documentElement; // <users> node

    // Create <user> element with child nodes.
    $userElem = $dom->createElement("user");

    $idElem = $dom->createElement("id", getNextUserIDFromXML($dom));
    $usernameElem = $dom->createElement("username", htmlspecialchars($username));
    $emailElem = $dom->createElement("email", htmlspecialchars($email));
    $passwordElem = $dom->createElement("password", htmlspecialchars($hashedPassword));
    $profileElem = $dom->createElement("profile_image", htmlspecialchars($profileImage));

    // Append all child nodes to <user>
    $userElem->appendChild($idElem);
    $userElem->appendChild($usernameElem);
    $userElem->appendChild($emailElem);
    $userElem->appendChild($passwordElem);
    $userElem->appendChild($profileElem);

    // Append <user> to the root element (<users>)
    $root->appendChild($userElem);

    return (bool) saveUserDOM($dom);
}

/**
 * Retrieve a user DOMNode by the given user ID.
 *
 * @param string|int $userId The ID of the user.
 * @return DOMNode|null The user node or null if not found.
 */
function getUserById($userId) {
    $dom = loadUserDOM();
    $xpath = new DOMXPath($dom);
    $query = sprintf("//user[id='%s']", $userId);
    $nodeList = $xpath->query($query);
    if ($nodeList->length > 0) {
        return $nodeList->item(0);
    }
    return null;
}

/**
 * Retrieve a user DOMNode by its username.
 *
 * @param string $username The username to search for.
 * @return DOMNode|null The user node if found; null otherwise.
 */
function getUserByUsername($username) {
    $dom = loadUserDOM();
    $xpath = new DOMXPath($dom);
    $query = sprintf("//user[translate(username, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')='%s']",
                     strtolower($username));
    $nodeList = $xpath->query($query);
    return ($nodeList->length > 0) ? $nodeList->item(0) : null;
}


/**
 * Update the given user’s information.
 *
 * @param string|int $userId The user's id.
 * @param string $username The new username.
 * @param string $email The new email.
 * @param string $profileImage (Optional) The new profile image path.
 * @return bool Returns true if updated successfully.
 */
function updateUser($userId, $username, $email, $profileImage = '') {
    $dom = loadUserDOM();
    $xpath = new DOMXPath($dom);
    $query = sprintf("//user[id='%s']", $userId);
    $nodeList = $xpath->query($query);

    if ($nodeList->length === 0) {
        return false;
    }
    $userNode = $nodeList->item(0);
    
    // Update username.
    $usernameNodes = $xpath->query("username", $userNode);
    if ($usernameNodes->length > 0) {
        $usernameNodes->item(0)->nodeValue = htmlspecialchars($username);
    }
    
    // Update email.
    $emailNodes = $xpath->query("email", $userNode);
    if ($emailNodes->length > 0) {
        $emailNodes->item(0)->nodeValue = htmlspecialchars($email);
    }
    
    // Update profile image if provided.
    if (!empty($profileImage)) {
        $profileNodes = $xpath->query("profile_image", $userNode);
        if ($profileNodes->length > 0) {
            $profileNodes->item(0)->nodeValue = htmlspecialchars($profileImage);
        }
    }
    
    return (bool) saveUserDOM($dom);
}

/**
 * Delete a user with the given ID.
 *
 * @param string|int $userId
 * @return bool Returns true if deletion is successful.
 */
function deleteUser($userId) {
    $dom = loadUserDOM();
    $xpath = new DOMXPath($dom);
    $query = sprintf("//user[id='%s']", $userId);
    $nodeList = $xpath->query($query);

    if ($nodeList->length > 0) {
        $userNode = $nodeList->item(0);
        $parent = $userNode->parentNode;
        $parent->removeChild($userNode);
        return (bool) saveUserDOM($dom);
    }
    return false;
}
