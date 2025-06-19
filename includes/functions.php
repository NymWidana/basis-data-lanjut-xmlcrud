<?php
/**
 * Sanitize user input to ensure it's safe for output or storage.
 *
 * @param string $data Input string.
 * @return string Sanitized string.
 */
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>
