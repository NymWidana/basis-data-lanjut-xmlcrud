<?php
// index.php

include 'includes/functions.php';
include 'views/components/head.php';
include 'views/components/header.php';

// Load posts from the XML data file.
$postsFile = 'data/posts.xml';
$posts = [];
if (file_exists($postsFile)) {
    $postsXML = simplexml_load_file($postsFile);
    foreach ($postsXML->post as $post) {
        $posts[] = $post;
    }
}

// ---- Search Feature ----
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $searchTerm = trim($_GET['search']);
    // Filter posts that contain the search term in their title or content.
    $posts = array_filter($posts, function($post) use ($searchTerm) {
        return (stripos($post->title, $searchTerm) !== false) || (stripos($post->content, $searchTerm) !== false);
    });
}

// ---- Sort Posts ----
// Sort by highest upvotes first; if tied, sort by least downvotes.
usort($posts, function($a, $b) {
    $upDiff = intval($b->upvotes) - intval($a->upvotes);
    if ($upDiff === 0) {
        // If upvotes are equal, sort by the number of downvotes in ascending order.
        return intval($a->downvotes) - intval($b->downvotes);
    }
    return $upDiff;
});

// ---- Pagination Feature ----
$postsPerPage = 9; // Or however many posts per page you'd like
$totalPosts   = count($posts);
$totalPages   = $totalPosts > 0 ? ceil($totalPosts / $postsPerPage) : 1;

// Get the current page from the query parameters; default to 1.
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
} elseif ($page > $totalPages) {
    $page = $totalPages;
}

// Determine the list of posts to display on the current page.
$start         = ($page - 1) * $postsPerPage;
$postsToDisplay = array_slice($posts, $start, $postsPerPage);
?>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <?php if (empty($postsToDisplay)) : ?>
            <p class="text-center text-gray-500">
                <?php echo isset($searchTerm) ? "No posts found for '$searchTerm'" : "No posts available"; ?>
            </p>
        <?php else: ?>
            <!-- Responsive Grid: 1 column on small, 2 on medium, 3 on large screens -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($postsToDisplay as $post): ?>
                    <div class="bg-white rounded shadow p-4">
                        <h2 class="text-2xl font-bold mb-2">
                            <a href="views/post_show.php?id=<?php echo $post->id; ?>" class="text-blue-500 hover:underline">
                                <?php echo htmlspecialchars($post->title); ?>
                            </a>
                        </h2>
                        <?php 
                            // Display author name if available (assumes you have a helper function to get it)
                            $author = getUserById($post->author_id);
                            if ($author):
                        ?>
                            <p class="text-sm text-gray-500 mb-2">By <?php echo htmlspecialchars($author->username); ?></p>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 mb-2 text-red-900 italic">By Deleted User</p>
                        <?php endif; ?>
                        <?php if (!empty($post->hero_image)): ?>
                            <img src="<?php echo htmlspecialchars($post->hero_image); ?>" alt="Hero Image" class="w-full h-auto mb-4">
                        <?php endif; ?>
                        <p class="text-gray-700 mb-4 line-clamp-4"><?php echo nl2br(htmlspecialchars($post->content)); ?></p>
                        <div class="flex items-center justify-between">
                            <div>
                                <button onclick="vote('<?php echo $post->id; ?>', 'upvote')" class="text-green-600 mr-2">
                                    <i class="fa fa-thumbs-up"></i> <span id="upvote-<?php echo $post->id; ?>"><?php echo $post->upvotes; ?></span>
                                </button>
                                <button onclick="vote('<?php echo $post->id; ?>', 'downvote')" class="text-red-600">
                                    <i class="fa fa-thumbs-down"></i> <span id="downvote-<?php echo $post->id; ?>"><?php echo $post->downvotes; ?></span>
                                </button>
                            </div>
                            <div class="text-sm text-gray-500">
                                Posted on <?php echo date("M d, Y", strtotime($post->created_at)); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Pagination Links -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center mt-4 space-x-2">
                <?php 
                    // Preserve the search query and other GET parameters in pagination links.
                    $queryParams = $_GET;
                    for ($i = 1; $i <= $totalPages; $i++):
                        $queryParams['page'] = $i;
                        $link = '?' . http_build_query($queryParams);
                ?>
                    <a href="<?php echo $link; ?>" class="px-3 py-2 border rounded 
                        <?php echo ($i == $page) ? 'bg-blue-500 text-white' : 'bg-white text-blue-500 hover:bg-blue-100'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Ajax Voting Script -->
    <script>
        function vote(postId, voteType) {
            const formData = new URLSearchParams();
            formData.append('action', 'vote');
            formData.append('postId', postId);
            formData.append('voteType', voteType);

            fetch('controllers/postController.php', {
                method: 'POST',
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: formData.toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('upvote-' + postId).textContent = data.upvotes;
                    document.getElementById('downvote-' + postId).textContent = data.downvotes;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error("Error:", error));
        }
    </script>

<?php include 'views/components/footer.php'; ?>
</body>
</html>
