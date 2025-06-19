<?php
// views/profile.php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: forms/login.php");
    exit;
}
$user = $_SESSION['user'];

include_once 'components/head.php';
include_once 'components/header.php';
require_once '../models/postModel.php';

// Load posts using the new DOM-based model.
$dom = loadPostDOM();                      // Load DOMDocument for posts.
$xpath = new DOMXPath($dom);
// Query for posts where the <author_id> equals the logged-in user ID.
$query = sprintf("//post[author_id/text()='%s']", $user['id']);
$postNodes = $xpath->query($query);

// Convert each post DOM element into an associative array.
$userPosts = [];
foreach ($postNodes as $postElem) {
    $userPosts[] = [
        'id'         => $xpath->query("id", $postElem)->item(0)->nodeValue,
        'title'      => $xpath->query("title", $postElem)->item(0)->nodeValue,
        'content'    => $xpath->query("content", $postElem)->item(0)->nodeValue,
        'hero_image' => $xpath->query("hero_image", $postElem)->item(0)->nodeValue,
        'upvotes'    => $xpath->query("upvotes", $postElem)->item(0)->nodeValue,
        'downvotes'  => $xpath->query("downvotes", $postElem)->item(0)->nodeValue,
        'created_at' => $xpath->query("created_at", $postElem)->item(0)->nodeValue,
    ];
}

// -----------------------------
// Search Feature: Filter posts by search term in title or content.
// -----------------------------
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $searchTerm = trim($_GET['search']);
    $userPosts = array_filter($userPosts, function($post) use ($searchTerm) {
        return (stripos($post['title'], $searchTerm) !== false) || 
               (stripos($post['content'], $searchTerm) !== false);
    });
}

// -----------------------------
// Sorting Options
// -----------------------------
$sortOption = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
switch ($sortOption) {
    case 'date_asc':
        usort($userPosts, function ($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });
        break;
    case 'title_asc':
        usort($userPosts, function ($a, $b) {
            return strcmp($a['title'], $b['title']);
        });
        break;
    case 'title_desc':
        usort($userPosts, function ($a, $b) {
            return strcmp($b['title'], $a['title']);
        });
        break;
    case 'votes_asc':
        usort($userPosts, function ($a, $b) {
            $netA = intval($a['upvotes']) - intval($a['downvotes']);
            $netB = intval($b['upvotes']) - intval($b['downvotes']);
            return $netA - $netB;
        });
        break;
    case 'votes_desc':
        usort($userPosts, function ($a, $b) {
            $netA = intval($a['upvotes']) - intval($a['downvotes']);
            $netB = intval($b['upvotes']) - intval($b['downvotes']);
            return $netB - $netA;
        });
        break;
    case 'date_desc':
    default:
        usort($userPosts, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        break;
}
?>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Profile Header -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-8 flex items-center gap-6">
            <div>
                <img src="/<?php echo htmlspecialchars($user['profile_image'] ?: 'uploads/profile/default.png'); ?>" 
                     alt="Profile Image" class="w-24 h-24 object-cover rounded-full border border-gray-300">
            </div>
            <div class="flex-1">
                <h1 class="text-3xl font-semibold mb-1"><?php echo htmlspecialchars($user['username']); ?></h1>
                <p class="text-gray-500 mb-4"><?php echo htmlspecialchars($user['email']); ?></p>
                <div class="flex space-x-4">
                    <a href="forms/editUser.php" 
                       class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded hover:bg-blue-600 transition duration-150">
                        <i class="fas fa-edit mr-2"></i> Edit Profile
                    </a>
                    <form action="../controllers/userController.php?action=delete" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-red-500 text-white text-sm font-medium rounded hover:bg-red-600 transition duration-150">
                            <i class="fas fa-trash mr-2"></i> Delete Account
                        </button>
                    </form>
                </div>  
            </div>
        </div>

        <!-- Sorting Form for User Posts -->
        <form method="GET" action="profile.php" class="mb-4 flex items-center space-x-2">
            <input type="text" name="search" placeholder="Search posts..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                   class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="sort" id="sort" class="border border-gray-300 p-2 rounded">
                <option value="date_desc" <?php if ($sortOption === 'date_desc') echo "selected"; ?>>Newest First</option>
                <option value="date_asc" <?php if ($sortOption === 'date_asc') echo "selected"; ?>>Oldest First</option>
                <option value="title_asc" <?php if ($sortOption === 'title_asc') echo "selected"; ?>>Title (A-Z)</option>
                <option value="title_desc" <?php if ($sortOption === 'title_desc') echo "selected"; ?>>Title (Z-A)</option>
                <option value="votes_desc" <?php if ($sortOption === 'votes_desc') echo "selected"; ?>>Highest Votes</option>
                <option value="votes_asc" <?php if ($sortOption === 'votes_asc') echo "selected"; ?>>Lowest Votes</option>
            </select>
            <button type="submit" class="bg-blue-500 text-white rounded px-3 py-1">
                <i class="fa fa-search"></i>
            </button>
        </form>

        <!-- Displaying the User's Posts in a Responsive Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($userPosts)): ?>
                <p class="text-center text-gray-600">You haven't posted anything yet.</p>
            <?php else: ?>
                <?php foreach ($userPosts as $post): ?>
                    <div class="bg-white rounded shadow p-4">
                        <h3 class="text-2xl font-bold mb-2">
                            <a href="post_show.php?id=<?php echo $post['id']; ?>" class="text-blue-500 hover:underline">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>
                        <?php if (!empty($post['hero_image'])): ?>
                            <img src="../<?php echo htmlspecialchars($post['hero_image']); ?>" alt="Hero Image" class="w-full h-auto mb-2">
                        <?php endif; ?>
                        <p class="text-gray-700 mb-2">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </p>
                        <div class="text-sm text-gray-500">
                            Posted on <?php echo date("M d, Y", strtotime($post['created_at'])); ?>
                        </div>
                        <div class="mt-1">
                            <a href="forms/editPost.php?id=<?php echo $post['id']; ?>" class="text-blue-500 hover:underline text-sm mr-2">
                                <i class="fas fa-edit"></i> Edit Post
                            </a>
                            <a href="../controllers/postController.php?action=delete&post_id=<?php echo $post['id']; ?>" 
                               class="text-red-500 text-sm" 
                               onclick="return confirm('Are you sure you want to delete this post?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include_once 'components/footer.php'; ?>
</body>
</html>
