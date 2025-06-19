<?php
session_start();
include_once 'components/head.php';
include_once 'components/header.php';

// Make sure a post ID is provided.
if (!isset($_GET['id'])) {
    echo "<div class='container mx-auto px-4 py-8'><p class='text-center text-red-500'>No post specified.</p></div>";
    exit;
}

$postId = htmlspecialchars($_GET['id']);

// Include our DOM-based models.
require_once '../models/postModel.php';
require_once '../models/reviewModel.php';
require_once '../models/userModel.php';

// ----------------------------------------------------------------------
// Load the Post using DOM-based model functions.
// ----------------------------------------------------------------------
$domPost = loadPostDOM();  // Returns a DOMDocument for posts.
$xpathPost = new DOMXPath($domPost);
$postQuery = sprintf("//post[id/text()='%s']", $postId);
$postNode = $xpathPost->query($postQuery)->item(0);

if (!$postNode) {
    echo "<div class='container mx-auto px-4 py-8'><p class='text-center text-red-500'>Post not found.</p></div>";
    exit;
}

// Convert the found post node to an associative array.
$postToShow = [
    'id'         => $xpathPost->query("id", $postNode)->item(0)->nodeValue,
    'title'      => $xpathPost->query("title", $postNode)->item(0)->nodeValue,
    'content'    => $xpathPost->query("content", $postNode)->item(0)->nodeValue,
    'author_id'  => $xpathPost->query("author_id", $postNode)->item(0)->nodeValue,
    'hero_image' => $xpathPost->query("hero_image", $postNode)->item(0)->nodeValue,
    'upvotes'    => $xpathPost->query("upvotes", $postNode)->item(0)->nodeValue,
    'downvotes'  => $xpathPost->query("downvotes", $postNode)->item(0)->nodeValue,
    'created_at' => $xpathPost->query("created_at", $postNode)->item(0)->nodeValue,
];

// ----------------------------------------------------------------------
// Load Reviews for this post using the DOM-based review model.
// ----------------------------------------------------------------------
$domReview = loadReviewDOM();  // Returns a DOMDocument for reviews.
$xpathReview = new DOMXPath($domReview);
$reviewQuery = sprintf("//review[post_id/text()='%s']", $postId);
$reviewNodes = $xpathReview->query($reviewQuery);
$reviewsArray = [];

foreach ($reviewNodes as $reviewNode) {
    $reviewsArray[] = [
        'id'         => $xpathReview->query("id", $reviewNode)->item(0)->nodeValue,
        'post_id'    => $xpathReview->query("post_id", $reviewNode)->item(0)->nodeValue,
        'user_id'    => $xpathReview->query("user_id", $reviewNode)->item(0)->nodeValue,
        'comment'    => $xpathReview->query("comment", $reviewNode)->item(0)->nodeValue,
        'created_at' => $xpathReview->query("created_at", $reviewNode)->item(0)->nodeValue,
    ];
}

// ----------------------------------------------------------------------
// Lookup the Post's Author using the DOM-based user model.
// ----------------------------------------------------------------------
$authorNode = getUserById($postToShow['author_id']);
if ($authorNode) {
    $xpathUser = new DOMXPath($authorNode->ownerDocument);
    $authorUsername = $xpathUser->query("username", $authorNode)->item(0)->nodeValue;
}
?>
<body class="bg-gray-100">
  <div class="container mx-auto px-4 py-8">
    <!-- Post Content -->
    <div class="bg-white rounded shadow p-6 mb-8">
      <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($postToShow['title']); ?></h1>
      
      <!-- Show author's name, if available -->
      <?php if (isset($authorUsername)): ?>
          <p class="text-sm text-gray-600 mb-4">By <?php echo htmlspecialchars($authorUsername); ?></p>
      <?php else: ?>
          <p class="text-sm text-gray-500 mb-2 text-red-900 italic">By Deleted User</p>
      <?php endif; ?>
      
      <?php if (!empty($postToShow['hero_image'])): ?>
         <img src="/<?php echo htmlspecialchars($postToShow['hero_image']); ?>" alt="Hero Image" class="w-full h-auto mb-4">
      <?php endif; ?>
      
      <div class="prose max-w-none">
        <?php echo nl2br(htmlspecialchars($postToShow['content'])); ?>
      </div>
      <div class="mt-4 text-sm text-gray-500">
         Posted on <?php echo date("M d, Y", strtotime($postToShow['created_at'])); ?>
      </div>
      <div class="mt-4 flex items-center">
         <button onclick="vote('<?php echo $postId; ?>', 'upvote')" class="mr-4 text-green-600">
              <i class="fa fa-thumbs-up"></i> <span id="upvote-<?php echo $postId; ?>"><?php echo $postToShow['upvotes']; ?></span>
         </button>
         <button onclick="vote('<?php echo $postId; ?>', 'downvote')" class="text-red-600">
              <i class="fa fa-thumbs-down"></i> <span id="downvote-<?php echo $postId; ?>"><?php echo $postToShow['downvotes']; ?></span>
         </button>
      </div>
    </div>

    <!-- Reviews Section -->
    <div class="bg-white rounded shadow p-6 mb-8">
       <h2 class="text-2xl font-bold mb-4">Reviews</h2>
       <?php if (empty($reviewsArray)): ?>
          <p class="text-gray-600">No reviews yet. Be the first to review!</p>
       <?php else: ?>
          <?php foreach ($reviewsArray as $review): ?>
              <div class="border-b py-2">
                  <?php  
                      // Get review author's details using DOM-based getUserById().
                      $reviewAuthorNode = getUserById($review['user_id']);
                      if ($reviewAuthorNode) {
                          $xpathAuthor = new DOMXPath($reviewAuthorNode->ownerDocument);
                          $reviewAuthorUsername = $xpathAuthor->query("username", $reviewAuthorNode)->item(0)->nodeValue;
                          echo '<p class="text-sm text-gray-600 mb-1">Review by ' . htmlspecialchars($reviewAuthorUsername) . '</p>';
                      } else {
                          echo '<p class="text-sm text-red-900 italic mb-1">Review by Deleted User</p>';
                      }
                  ?>
                  <p class="text-gray-800"><?php echo htmlspecialchars($review['comment']); ?></p>
                  <small class="text-gray-500">
                      Posted on <?php echo date("M d, Y", strtotime($review['created_at'])); ?>
                  </small>
                  <?php if (isset($_SESSION['user']) && $review['user_id'] === $_SESSION['user']['id']): ?>
                      <div class="mt-1">
                          <a href="forms/editReview.php?review_id=<?php echo $review['id']; ?>&post_id=<?php echo $postId; ?>" class="text-blue-500 text-sm mr-2">
                              <i class="fas fa-edit"></i> Edit
                          </a>
                          <a href="../controllers/reviewController.php?action=delete&review_id=<?php echo $review['id']; ?>&post_id=<?php echo $postId; ?>" 
                             class="text-red-500 text-sm" 
                             onclick="return confirm('Are you sure you want to delete this review?');">
                              <i class="fas fa-trash"></i> Delete
                          </a>
                      </div>
                  <?php endif; ?>
              </div>
          <?php endforeach; ?>
       <?php endif; ?>
    </div>

    <!-- Add Review Form -->
    <?php if (isset($_SESSION['user'])): ?>
    <div class="bg-white rounded shadow p-6">
      <h2 class="text-2xl font-bold mb-4">Add a Review</h2>
      <form action="../controllers/reviewController.php?action=create" method="POST">
         <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
         <div class="mb-4">
            <textarea name="comment" required rows="4" placeholder="Write your review here..." class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
         </div>
         <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
           Submit Review
         </button>
      </form>
    </div>
    <?php else: ?>
    <div class="bg-white rounded shadow p-6 text-center">
         <p class="text-gray-600">Please <a href="forms/login.php" class="text-blue-500 hover:underline">login</a> to add a review.</p>
    </div>
    <?php endif; ?>
  </div>

  <!-- AJAX Voting Script -->
  <script>
    function vote(postId, voteType) {
      const formData = new URLSearchParams();
      formData.append('action', 'vote');
      formData.append('postId', postId);
      formData.append('voteType', voteType);

      fetch('../controllers/postController.php', {
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

<?php include_once 'components/footer.php'; ?>
</body>
</html>
