<?php
require_once 'models/userModel.php';
require_once 'models/postModel.php';
include 'includes/functions.php';
include 'views/components/head.php';
include 'views/components/header.php';

// -----------------------------
// Load posts using DOM and convert to array
// -----------------------------
$posts = [];
$dom = loadPostDOM(); // loads the DOMDocument for posts
$xpath = new DOMXPath($dom);
$postElements = $xpath->query("//post");

foreach ($postElements as $postElement) {
    // Convert each post DOM element into an associative array.
    $postArr = [
        'id'          => $xpath->query("id", $postElement)->item(0)->nodeValue,
        'title'       => $xpath->query("title", $postElement)->item(0)->nodeValue,
        'content'     => $xpath->query("content", $postElement)->item(0)->nodeValue,
        'author_id'   => $xpath->query("author_id", $postElement)->item(0)->nodeValue,
        'hero_image'  => $xpath->query("hero_image", $postElement)->item(0)->nodeValue,
        'upvotes'     => $xpath->query("upvotes", $postElement)->item(0)->nodeValue,
        'downvotes'   => $xpath->query("downvotes", $postElement)->item(0)->nodeValue,
        'created_at'  => $xpath->query("created_at", $postElement)->item(0)->nodeValue,
    ];
    $posts[] = $postArr;
}

// -----------------------------
// Sorting Options
// -----------------------------
$sortOption = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
switch ($sortOption) {
    case 'date_asc':
        usort($posts, function ($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });
        break;
    case 'title_asc':
        usort($posts, function ($a, $b) {
            return strcmp($a['title'], $b['title']);
        });
        break;
    case 'title_desc':
        usort($posts, function ($a, $b) {
            return strcmp($b['title'], $a['title']);
        });
        break;
    case 'votes_asc':
        usort($posts, function ($a, $b) {
            $netA = intval($a['upvotes']) - intval($a['downvotes']);
            $netB = intval($b['upvotes']) - intval($b['downvotes']);
            return $netA - $netB;
        });
        break;
    case 'votes_desc':
        usort($posts, function ($a, $b) {
            $netA = intval($a['upvotes']) - intval($a['downvotes']);
            $netB = intval($b['upvotes']) - intval($b['downvotes']);
            return $netB - $netA;
        });
        break;
    case 'date_desc':
    default:
        usort($posts, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        break;
}

// -----------------------------
// Search Feature: Filter posts by search term in title or content.
// -----------------------------
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $searchTerm = trim($_GET['search']);
    $posts = array_filter($posts, function($post) use ($searchTerm) {
        return (stripos($post['title'], $searchTerm) !== false) ||
               (stripos($post['content'], $searchTerm) !== false);
    });
}

// -----------------------------
// Pagination Feature
// -----------------------------
$postsPerPage = 9; // posts per page
$totalPosts   = count($posts);
$totalPages   = $totalPosts > 0 ? ceil($totalPosts / $postsPerPage) : 1;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
} elseif ($page > $totalPages) {
    $page = $totalPages;
}

$start         = ($page - 1) * $postsPerPage;
$postsToDisplay = array_slice($posts, $start, $postsPerPage);
?>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- Combined Search & Sorting Form (Same as in profile.php) -->
        <form method="GET" action="index.php" class="mb-4 flex items-center space-x-2">
            <input type="text" name="search" placeholder="Search posts..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                   class="w-full border border-gray-300 rounded-l px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="sort" class="border border-gray-300 p-2">
                <option value="date_desc" <?php if ($sortOption === 'date_desc') echo "selected"; ?>>Newest First</option>
                <option value="date_asc" <?php if ($sortOption === 'date_asc') echo "selected"; ?>>Oldest First</option>
                <option value="title_asc" <?php if ($sortOption === 'title_asc') echo "selected"; ?>>Title (A-Z)</option>
                <option value="title_desc" <?php if ($sortOption === 'title_desc') echo "selected"; ?>>Title (Z-A)</option>
                <option value="votes_desc" <?php if ($sortOption === 'votes_desc') echo "selected"; ?>>Highest Votes</option>
                <option value="votes_asc" <?php if ($sortOption === 'votes_asc') echo "selected"; ?>>Lowest Votes</option>
            </select>
            <button type="submit" class="rounded px-4 py-2 bg-blue-500 text-white hover:bg-blue-600 transition duration-200">
                <i class="fa fa-search"></i>
            </button>
        </form>
        
        <?php if (empty($postsToDisplay)) : ?>
            <p class="text-center text-gray-500">
                <?php echo isset($searchTerm) ? "No posts found for '$searchTerm'" : "No posts available"; ?>
            </p>
        <?php else: ?>
            <!-- Responsive Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($postsToDisplay as $post): ?>
                    <div class="bg-white rounded shadow p-4 flex flex-col h-full">
                        <h2 class="text-2xl font-bold mb-2">
                            <a href="views/post_show.php?id=<?php echo $post['id']; ?>" 
                            class="text-blue-500 hover:underline line-clamp-1">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h2>
                        <!-- author info -->
                        <?php if (isset($authorUsername)): ?>
                            <p class="text-sm text-gray-500 mb-2">By <?php echo htmlspecialchars($authorUsername); ?></p>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 mb-2 text-red-900 italic">By Deleted User</p>
                        <?php endif; ?>

                        <!-- image -->
                        <?php if (!empty($post['hero_image'])): ?>
                            <img src="/<?php echo htmlspecialchars($post['hero_image']); ?>" 
                                alt="Hero Image" class="w-full h-auto mb-4">
                        <?php endif; ?>

                        <!-- post content -->
                        <p class="text-gray-700 mb-4 line-clamp-4">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </p>

                        <!-- footer sticks to bottom -->
                        <div class="mt-auto flex items-center justify-between pt-4 border-t border-gray-200">
                            <div>
                                <button onclick="vote('<?php echo $post['id']; ?>', 'upvote')" class="text-green-600 mr-2">
                                    <i class="fa fa-thumbs-up"></i> <span id="upvote-<?php echo $post['id']; ?>"><?php echo $post['upvotes']; ?></span>
                                </button>
                                <button onclick="vote('<?php echo $post['id']; ?>', 'downvote')" class="text-red-600">
                                    <i class="fa fa-thumbs-down"></i> <span id="downvote-<?php echo $post['id']; ?>"><?php echo $post['downvotes']; ?></span>
                                </button>
                            </div>
                            <div class="text-sm text-gray-500">
                                Posted on <?php echo date("M d, Y", strtotime($post['created_at'])); ?>
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
                    // Preserve existing GET parameters.
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
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
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
