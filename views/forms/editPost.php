<?php
// views/forms/editPost.php
session_start();
// Optionally, ensure that the user is logged in before allowing post edits.
if (!isset($_SESSION['user'])) {
    header("Location: ../forms/login.php");
    exit;
}

include_once '../components/head.php';
include_once '../components/header.php';

// Retrieve the post id from the query string.
$postId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null;
if (!$postId) {
    echo "<div class='container mx-auto px-4 py-8'><p class='text-center text-red-500'>No post selected for editing.</p></div>";
    exit;
}

// Load the posts data.
$postsFile = '../../data/posts.xml';
$postsXml = file_exists($postsFile) ? simplexml_load_file($postsFile) : false;
if (!$postsXml) {
    echo "<div class='container mx-auto px-4 py-8'><p class='text-center text-red-500'>Unable to load posts data.</p></div>";
    exit;
}

// Find the post with the matching ID.
$postToEdit = null;
foreach ($postsXml->post as $post) {
    if ((string)$post->id === $postId) {
        $postToEdit = $post;
        break;
    }
}
if (!$postToEdit) {
    echo "<div class='container mx-auto px-4 py-8'><p class='text-center text-red-500'>Post not found.</p></div>";
    exit;
}
?>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
            <h1 class="text-2xl font-bold mb-6 text-center">Edit Post</h1>
            <form action="../../controllers/postController.php?action=update" method="POST" enctype="multipart/form-data">
                <!-- Hidden field for the post ID -->
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($postToEdit->id); ?>">

                <div class="mb-4">
                    <label for="title" class="block text-gray-700">Title</label>
                    <input type="text" name="title" id="title" required
                           value="<?php echo htmlspecialchars($postToEdit->title); ?>"
                           class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700">Current Hero Image</label>
                    <?php if (!empty($postToEdit->hero_image)): ?>
                        <img src="../../<?php echo htmlspecialchars($postToEdit->hero_image); ?>" alt="Hero Image" class="w-full h-auto mb-2">
                    <?php else: ?>
                        <p class="text-gray-500">No image uploaded.</p>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="hero_image" class="block text-gray-700">Change Hero Image (optional)</label>
                    <input type="file" name="hero_image" id="hero_image"
                           class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-6">
                    <label for="content" class="block text-gray-700">Content</label>
                    <textarea name="content" id="content" rows="8" required
                              class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($postToEdit->content); ?></textarea>
                </div>

                <div>
                    <button type="submit"
                            class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600 transition duration-200">
                        Update Post
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php include_once '../components/footer.php'; ?>
</body>
</html>
