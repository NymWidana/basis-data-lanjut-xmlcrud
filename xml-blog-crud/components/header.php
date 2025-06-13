<header class="bg-black p-4 text-white text-center flex justify-between px-[100px] md:px-[150px]">
    <a href="index.php" class="text-3xl font-bold underline-none">XML Based Blog</a>
    <div class="mt-2 flex space-x-4">
      <?php if (isset($_SESSION['user_id'])): ?>
          <a href="profile.php" class="hover:underline">My Profile</a>
          <a href="logout.php" class="hover:underline">Logout</a>
          <a href="create_post.php" class="text-white bg-gradient-to-r from-cyan-400 via-cyan-500 to-cyan-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-cyan-300 dark:focus:ring-cyan-800 font-medium rounded-lg text-sm px-3 py-1 text-center me-2 mb-2"><i class="fa-solid fa-plus"></i><span>New Post</span></a>
      <?php else: ?>
          <a href="login.php" class="underline">Login</a>
          <a href="create_user.php" class="underline">Register</a>
      <?php endif; ?>
    </div>
  </header>