<?php
// views/forms/editPost.php
session_start();
// Ensure the user is logged in.
if (!isset($_SESSION['user'])) {
    header("Location: ../forms/login.php");
    exit;
}

include_once '../components/head.php';
include_once '../components/header.php';
require_once '../../models/postModel.php';  // Use your DOM-based post model

// Retrieve the post id from the query string.
$postId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null;
if (!$postId) {
    echo "<div class='container mx-auto px-4 py-8'><p class='text-center text-red-500'>No post selected for editing.</p></div>";
    exit;
}

// Load the posts DOM.
$dom = loadPostDOM();  // Returns a DOMDocument
$xpath = new DOMXPath($dom);

// Query for the post node by comparing the text of the <id> element.
$postQuery = sprintf("//post[id/text()='%s']", $postId);
$postNode = $xpath->query($postQuery)->item(0);

if (!$postNode) {
    echo "<div class='container mx-auto px-4 py-8'><p class='text-center text-red-500'>Post not found.</p></div>";
    exit;
}

// Convert the post node into an associative array for ease of use.
$postToEdit = [
    'id'         => $xpath->query("id", $postNode)->item(0)->nodeValue,
    'title'      => $xpath->query("title", $postNode)->item(0)->nodeValue,
    'hero_image' => $xpath->query("hero_image", $postNode)->item(0)->nodeValue,
    'content'    => $xpath->query("content", $postNode)->item(0)->nodeValue
];
?>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
            <h1 class="text-2xl font-bold mb-6 text-center">Edit Post</h1>
            <!-- The form action now points appropriately to your controller -->
            <form action="../../controllers/postController.php?action=update" method="POST" enctype="multipart/form-data">
                <!-- Hidden field for the post ID -->
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($postToEdit['id']); ?>">
                
                <div class="mb-4">
                    <label for="title" class="block text-gray-700">Title</label>
                    <input type="text" name="title" id="title" required
                           value="<?php echo htmlspecialchars($postToEdit['title']); ?>"
                           class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Current Hero Image</label>
                    <?php if (!empty($postToEdit['hero_image'])): ?>
                        <img src="../../<?php echo htmlspecialchars($postToEdit['hero_image']); ?>" alt="Hero Image" class="w-full h-auto mb-2">
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
                              class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($postToEdit['content']); ?></textarea>
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
