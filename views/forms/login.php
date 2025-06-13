<?php
// views/forms/login.php
include_once '../components/head.php';
include_once '../components/header.php';
?>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
            <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                    Invalid username or password.
                </div>
            <?php endif; ?>

            <form action="/controllers/authController.php?action=login" method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700">Username</label>
                    <input type="text" name="username" id="username" required
                           class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required
                           class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <button type="submit"
                            class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 transition duration-200">
                        Login
                    </button>
                </div>
            </form>
            <p class="mt-4 text-center text-sm text-gray-600">
                Don't have an account? 
                <a href="createUser.php" class="text-blue-500 hover:underline">Register here</a>.
            </p>
        </div>
    </div>
    <?php include_once '../components/footer.php'; ?>
</body>
</html>
