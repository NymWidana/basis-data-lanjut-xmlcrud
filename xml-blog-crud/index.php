<?php
require_once 'includes/functions.php';

// Load XML data for posts and reviews
$postsXml   = loadXML('data/posts.xml');
$reviewsXml = loadXML('data/reviews.xml');

// Convert posts into an array for easier handling.
$allPosts = [];
if (isset($postsXml->post)) {
    foreach ($postsXml->post as $post) {
        $allPosts[] = $post;
    }
}

// Build review count array: reviewCounts[post_id] = number of reviews.
$reviewCounts = [];
if (isset($reviewsXml->review)) {
    foreach ($reviewsXml->review as $review) {
        $pid = (string)$review->post_id;
        if (!isset($reviewCounts[$pid])) {
            $reviewCounts[$pid] = 0;
        }
        $reviewCounts[$pid]++;
    }
}

// Process search query if provided.
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($searchQuery !== '') {
    $filteredPosts = [];
    foreach ($allPosts as $post) {
        if (
            stripos($post->title, $searchQuery) !== false ||
            stripos($post->content, $searchQuery) !== false
        ) {
            $filteredPosts[] = $post;
        }
    }
    $allPosts = $filteredPosts;
}

// Pagination Settings
$postsPerPage = 5;  // Number of posts per page.
$totalPosts   = count($allPosts);
$totalPages   = $totalPosts > 0 ? ceil($totalPosts / $postsPerPage) : 1;
$page         = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { 
    $page = 1;
} elseif ($page > $totalPages) {
    $page = $totalPages;
}
$startIndex   = ($page - 1) * $postsPerPage;
$postsToDisplay = array_slice($allPosts, $startIndex, $postsPerPage);
include './components/head.php'
?>

<body class="bg-gray-100 text-gray-900">
  <?php include './components/header.php' ?>
  <nav class="bg-gray-200 p-4 flex justify-center">
    <a href="index.php" class="text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800 flex space-x-1 items-center"><i class="fa-solid fa-house"></i><span>Home</span></a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="create_post.php" class="text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800 flex space-x-1 items-center"><i class="fa-solid fa-plus"></i><span>New Post</span></a>
    <?php endif; ?>
  </nav>
  Hello, <?php echo htmlspecialchars($_SESSION['username']); ?> 
  <!-- Search Bar -->
  <div class="container mx-auto p-4">
    <form method="GET" action="index.php" class="flex items-center space-x-2">
      <input type="text" name="search" placeholder="Search posts..." value="<?php echo htmlspecialchars($searchQuery); ?>"
             class="w-full border p-2 rounded" />
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Search</button>
    </form>
  </div>

  <!-- Main Content Area -->
  <main class="container mx-auto p-4">
    <?php
    if ($searchQuery !== '' && count($allPosts) === 0) {
        echo '<p class="text-gray-600">No posts found for "<strong>' . htmlspecialchars($searchQuery) . '</strong>".</p>';
    }
    echo '<div class="space-y-8">';
    foreach ($postsToDisplay as $post) {
        echo '<div class="bg-white p-4 rounded shadow">';
        echo '<h2 class="text-xl font-bold mb-2">' . htmlspecialchars($post->title) . '</h2>';
        if (!empty($post->hero_image)) {
            echo '<img src="' . htmlspecialchars($post->hero_image) . '" alt="Hero Image" class="w-full h-auto mb-2 rounded">';
        }
        echo '<p class="mb-2">' . nl2br(htmlspecialchars($post->content)) . '</p>';
        echo '<p class="text-sm text-gray-500">Posted on ' . htmlspecialchars($post->created_at) . ' </p>';
        $postId = (string)$post->id;
        if (isset($reviewCounts[$postId])) {
            echo '<p class="text-sm text-gray-700 mb-2">Reviews: ' . $reviewCounts[$postId] . '</p>';
        }
        echo '<a href="show_post.php?id=' . htmlspecialchars($post->id) . '" class="text-blue-500 underline">Read More</a>';
        echo '</div>';
    }
    echo '</div>';
    ?>

    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-8 flex justify-center space-x-2">
      <?php if ($page > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
           class="px-4 py-2 border rounded hover:bg-blue-200">Previous</a>
      <?php endif; ?>

      <?php
      // Display links for each page.
      for ($i = 1; $i <= $totalPages; $i++):
          if ($i == $page):
      ?>
              <span class="px-4 py-2 border rounded bg-blue-500 text-white"><?php echo $i; ?></span>
      <?php else: ?>
              <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                 class="px-4 py-2 border rounded hover:bg-blue-200"><?php echo $i; ?></a>
      <?php
          endif;
      endfor;
      ?>

      <?php if ($page < $totalPages): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
           class="px-4 py-2 border rounded hover:bg-blue-200">Next</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </main>
</body>
</html>
