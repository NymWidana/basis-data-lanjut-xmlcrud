<?php
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if (!isset($_GET['id'])) {
    header("Location: profile.php");
    exit;
}

$postId = $_GET['id'];
$posts = loadXML('data/posts.xml');
$foundPost = null;
foreach ($posts->post as $post) {
    if ((string)$post->id === (string)$postId && (string)$post->author_id === $_SESSION['user_id']) {
        $foundPost = $post;
        break;
    }
}
if (!$foundPost) {
    echo "Post not found or you do not have permission to edit it.";
    exit;
}

// Process update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Handle hero image upload
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === 0) {
        $uploadDir = 'uploads/posts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = basename($_FILES['hero_image']['name']);
        $targetFile = $uploadDir . time() . '_' . $filename;
        move_uploaded_file($_FILES['hero_image']['tmp_name'], $targetFile);
    } else {
        $targetFile = ''; // no new image provided
    }
    updatePost($postId, $title, $content, ($targetFile ? $targetFile : null));
    header("Location: profile.php");
    exit;
}
include './components/head.php'
?>

<body class="bg-gray-100">
  <?php include './components/header.php' ?>
<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-4">Edit Post</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block text-gray-700">Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($foundPost->title); ?>" required class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block text-gray-700">Content</label>
            <textarea name="content" required class="w-full border p-2 rounded"><?php echo htmlspecialchars($foundPost->content); ?></textarea>
        </div>
        <div>
            <label class="block text-gray-700">Hero Image (upload new to change)</label>
            <input type="file" name="hero_image" accept="image/*" class="w-full">
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Post</button>
    </form>
    <div class="mt-4">
        <a href="profile.php" class="text-blue-500 hover:underline">&larr; Back to Profile</a>
    </div>
</div>
</body>
</html>
