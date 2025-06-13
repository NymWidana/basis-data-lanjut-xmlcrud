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

// Load posts created by the user.
$postsFile = "../data/posts.xml";
$userPosts = [];
if (file_exists($postsFile)) {
    $postsXml = simplexml_load_file($postsFile);
    foreach ($postsXml->post as $post) {
        if ((string)$post->author_id === $user['id']) {
            $userPosts[] = $post;
        }
    }
}
?>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6 mb-8 flex items-center gap-6">
        <!-- Profile Image -->
        <div>
            <img src="/<?php echo htmlspecialchars($_SESSION['user']['profile_image'] ?? 'default.png'); ?>" 
                alt="Profile Image" 
                class="w-24 h-24 object-cover rounded-full border border-gray-300">
        </div>
        <!-- Profile Details and Actions -->
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

        <!-- User's Blog Posts -->
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4">My Posts</h2>
            <?php if(empty($userPosts)): ?>
                <p class="text-gray-600">You haven't posted anything yet.</p>
            <?php else: ?>
                <?php foreach ($userPosts as $post): ?>
                    <div class="mb-6 border-b pb-4">
                        <h3 class="text-2xl font-bold mb-2">
                            <a href="post_show.php?id=<?php echo $post->id; ?>" class="text-blue-500 hover:underline">
                                <?php echo htmlspecialchars($post->title); ?>
                            </a>
                        </h3>
                        <?php if(!empty($post->hero_image)): ?>
                            <img src="../<?php echo htmlspecialchars($post->hero_image); ?>" alt="Hero Image" class="w-full h-auto mb-2">
                        <?php endif; ?>
                        <p class="text-gray-700 mb-2"><?php echo nl2br(htmlspecialchars($post->content)); ?></p>
                        <div class="text-sm text-gray-500">
                            Posted on <?php echo date("M d, Y", strtotime($post->created_at)); ?>
                        </div>
                        <a href="forms/editPost.php?id=<?php echo $post->id; ?>" class="text-blue-500 hover:underline text-sm">
                            <i class="fas fa-edit"></i> Edit Post
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php include_once 'components/footer.php'; ?>
</body>
</html>
