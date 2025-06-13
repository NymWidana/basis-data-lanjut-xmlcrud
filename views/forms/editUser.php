<?php
// views/forms/editUser.php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user'];

include_once '../components/head.php';
include_once '../components/header.php';
?>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
            <h1 class="text-2xl font-bold mb-6 text-center">Edit Profile</h1>

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                    An error occurred while updating your profile.
                </div>
            <?php endif; ?>

            <form action="../../controllers/userController.php?action=updateProfile" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700">Username</label>
                    <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required
                           class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                           class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="profile_image" class="block text-gray-700">Profile Image</label>
                    <input type="file" name="profile_image" id="profile_image"
                           class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php if (!empty($_SESSION['user']['profile_image'])): ?>
                        <div class="mt-2">
                            <img src="/<?php echo htmlspecialchars($_SESSION['user']['profile_image']); ?>" alt="Profile Image" class="w-20 h-20 object-cover rounded-full">
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <button type="submit"
                            class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 transition duration-200">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php include_once '../components/footer.php'; ?>
</body>
</html>
