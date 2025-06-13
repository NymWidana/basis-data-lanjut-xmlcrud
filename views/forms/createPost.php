<?php
// views/forms/createPost.php
include_once '../components/head.php';
include_once '../components/header.php';
?>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
            <h1 class="text-2xl font-bold mb-6 text-center">Create New Post</h1>
            <form action="../../controllers/postController.php?action=create" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="title" class="block text-gray-700">Title</label>
                    <input type="text" name="title" id="title" required placeholder="Enter post title"
                           class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="hero_image" class="block text-gray-700">Hero Image</label>
                    <input type="file" name="hero_image" id="hero_image"
                           class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label for="content" class="block text-gray-700">Content</label>
                    <textarea name="content" id="content" rows="6" required placeholder="Enter post content"
                              class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <button type="submit"
                            class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600 transition duration-200">
                        Publish Post
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php include_once '../components/footer.php'; ?>
</body>
</html>
