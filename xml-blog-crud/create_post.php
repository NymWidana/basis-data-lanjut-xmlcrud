<?php
require_once 'includes/functions.php';

// Ensure the user is logged in; otherwise, redirect to login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Process form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read and trim the inputs.
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);
    // Get the author id from the session.
    $author_id = $_SESSION['user_id'];
    
    // Handle hero image upload if provided.
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === 0) {
        $uploadDir = 'uploads/posts/';
        // Ensure the directory exists.
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        // Create a unique filename.
        $filename   = basename($_FILES['hero_image']['name']);
        $targetFile = $uploadDir . time() . '_' . $filename;
        move_uploaded_file($_FILES['hero_image']['tmp_name'], $targetFile);
    } else {
        $targetFile = ''; // No image provided.
    }
    
    // Save the new blog post into the XML data store.
    addPost($title, $content, $targetFile, $author_id);
    
    // Redirect to the index page after creation.
    header("Location: index.php");
    exit;
}
include './components/head.php'
?>

<body class="bg-gray-100">
  <?php include './components/header.php' ?>
  <div class="container mx-auto p-8">
    <h2 class="text-2xl font-bold mb-4">Create New Post</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block text-gray-700">Title</label>
        <input type="text" name="title" required class="w-full border p-2 rounded">
      </div>
      <div>
        <label class="block text-gray-700">Content</label>
        <textarea name="content" required class="w-full border p-2 rounded" rows="6"></textarea>
      </div>
      <div>
        <label class="block text-gray-700">Hero Image (optional)</label>
        <input type="file" name="hero_image" accept="image/*" class="w-full">
      </div>
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Submit Post</button>
    </form>
    <div class="mt-4">
      <a href="index.php" class="text-blue-500 underline">&larr; Back to Home</a>
    </div>
  </div>
</body>
</html>
