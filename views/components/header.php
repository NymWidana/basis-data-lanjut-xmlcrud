<?php
// Start the session if it hasn't been started already.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="bg-white shadow">
    <div class="container mx-auto px-4 py-4 flex flex-col md:flex-row md:items-center md:justify-between">
        <!-- Site Logo / Title -->
        <div class="mb-4 md:mb-0">
            <a href="/index.php" class="text-2xl font-bold text-gray-800">XML Based Blog</a>
        </div>

        <!-- Navigation Links and Search Form -->
        <div class="flex flex-col md:flex-row md:items-center md:space-x-6">
            <!-- Navigation links: conditionally show based on user login state -->
            <nav class="flex flex-col md:flex-row md:space-x-4 mb-4 md:mb-0">
                <a href="/index.php" class="text-gray-600 hover:text-gray-800">Home</a>
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="/views/profile.php" class="text-gray-600 hover:text-gray-800">My Profile</a>
                    <a href="/views/forms/createPost.php" class="text-gray-600 hover:text-gray-800">New Post</a>
                    <a href="/controllers/authController.php?action=logout" class="text-gray-600 hover:text-gray-800">Logout</a>
                <?php else: ?>
                    <a href="/views/forms/login.php" class="text-gray-600 hover:text-gray-800">Login</a>
                    <a href="/views/forms/createUser.php" class="text-gray-600 hover:text-gray-800">Register</a>
                <?php endif; ?>
            </nav>

            <!-- Search Form -->
            <form action="/index.php" method="GET" class="flex items-center">
                <input type="text" name="search" placeholder="Search posts..." class="border border-gray-300 rounded-l px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-blue-500 text-white rounded-r px-3 py-1">
                    <i class="fa fa-search"></i>
                </button>
            </form>
        </div>
    </div>
</header>
