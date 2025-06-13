<?php
// includes/functions.php

/**
 * Load XML data from a given filename.
 *
 * @param string $filename The path to the XML file.
 * @return SimpleXMLElement|false Returns the XML object or false on failure.
 */
function loadXMLData($filename) {
    if (file_exists($filename)) {
        return simplexml_load_file($filename);
    }
    return false;
}

/**
 * Save the updated XML data back to a file.
 *
 * @param SimpleXMLElement $xml The XML object to save.
 * @param string $filename The path to the XML file.
 * @return int|false Returns the number of bytes written or false on failure.
 */
function saveXMLData($xml, $filename) {
    // Format XML for readability.
    $dom = new DOMDocument("1.0");
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    return $dom->save($filename);
}

/**
 * Generate a new unique ID based on the highest current id in the XML.
 *
 * @param SimpleXMLElement $xmlData The XML data loaded.
 * @param string $nodeName The name of the nodes holding the id (i.e., "user", "post", "review").
 * @return int The new unique id.
 */
function generateID($xmlData, $nodeName) {
    $maxId = 0;
    foreach ($xmlData->$nodeName as $node) {
        $id = intval((string)$node->id);
        if ($id > $maxId) {
            $maxId = $id;
        }
    }
    return $maxId + 1;
}

/**
 * Sanitize user input to ensure it's safe for output or storage.
 *
 * @param string $data Input string.
 * @return string Sanitized string.
 */
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Get the user node by ID.
 *
 * @param string|int $userId
 * @return SimpleXMLElement|null
 */
function getUserById($userId) {
    $usersFile = __DIR__ . '/../data/users.xml'; // adjust the path if needed
    if (file_exists($usersFile)) {
        $usersXml = simplexml_load_file($usersFile);
        foreach ($usersXml->user as $user) {
            if ((string)$user->id === (string)$userId) {
                return $user;
            }
        }
    }
    return null;
}

?>
