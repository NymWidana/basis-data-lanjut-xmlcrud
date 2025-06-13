<?php
// views/forms/createUser.php
include_once '../components/head.php';
include_once '../components/header.php';
?>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
            <h1 class="text-2xl font-bold mb-6 text-center">Create User Account</h1>
            <form action="../../controllers/authController.php?action=register" method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700">Username</label>
                    <input type="text" name="username" id="username" required
                           class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Email</label>
                    <input type="email" name="email" id="email" required
                           class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required
                           class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <button type="submit"
                            class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 transition duration-200">
                        Register
                    </button>
                </div>
            </form>
            <p class="mt-4 text-center text-sm text-gray-600">
                Already have an account?
                <a href="login.php" class="text-blue-500 hover:underline">Login here</a>.
            </p>
        </div>
    </div>
    <?php include_once '../components/footer.php'; ?>
</body>
</html>
