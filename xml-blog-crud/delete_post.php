<?php
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: profile.php");
    exit;
}

$postId = $_GET['id'];
$posts = loadXML('data/posts.xml');
$hasPermission = false;
foreach ($posts->post as $post) {
    if ((string)$post->id === (string)$postId && (string)$post->author_id === $_SESSION['user_id']) {
        $hasPermission = true;
        break;
    }
}

if ($hasPermission) {
    deletePost($postId);
}
header("Location: profile.php");
exit;
?>
