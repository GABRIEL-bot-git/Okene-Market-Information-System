<?php
// login.php
session_start();
require_once 'config/db.php';

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, fullname, username, password, role FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Login Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $errorMsg = "Invalid username or password.";
        }
    } else {
        $errorMsg = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Okene Market Info System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'okene-green': '#115e3b',
                        'okene-light': '#e8f5e9',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex h-screen w-full">
        
        <div class="hidden md:flex w-1/2 bg-okene-green p-12 flex-col justify-between relative overflow-hidden">
            <div class="z-10">
                <div class="flex items-center space-x-3 mb-16 text-white">
                    <div class="bg-white p-2 rounded-lg">
                        <svg class="w-6 h-6 text-okene-green" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold tracking-wide">Okene Market Info System</h2>
                        <p class="text-xs text-green-200">Controlled. Centralized. Reliable.</p>
                    </div>
                </div>

                <h1 class="text-4xl lg:text-5xl font-bold text-white leading-tight mb-6">
                    Welcome to Okene <br /> Market Price Information <br /> System
                </h1>
                <p class="text-green-100 text-lg mb-12 max-w-md">
                    Your reliable source for real-time and accurate market price information.
                </p>

                <div class="space-y-8">
                    <div class="flex items-start space-x-4">
                        <div class="bg-green-600 p-2 rounded-full mt-1">
                             <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-lg">Accurate & Verified Prices</h3>
                            <p class="text-green-200 text-sm">Controlled and validated market price data</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-1/2 flex items-center justify-center p-8 bg-gray-50">
            <div class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-md">
                
                <div class="flex flex-col items-center mb-8">
                    <div class="bg-green-50 p-3 rounded-full mb-4 border border-green-100">
                         <svg class="w-8 h-8 text-okene-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Login to Your Account</h2>
                    <p class="text-gray-500 text-sm">Enter your credentials to access the system</p>
                </div>

                <form action="login.php" method="POST" class="space-y-6">
                    
                    <?php if($errorMsg): ?>
                        <div class="p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm font-medium">
                            <?php echo htmlspecialchars($errorMsg); ?>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" required class="w-full border border-gray-300 rounded-lg py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-okene-green focus:border-okene-green" placeholder="Enter your username">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-okene-green focus:border-okene-green" placeholder="Enter your password">
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-lg shadow-sm text-sm font-medium text-white bg-okene-green hover:bg-green-800 transition-colors">
                        Login
                    </button>
                    
                    <div class="mt-4 flex items-center justify-center space-x-2">
                        <span class="text-gray-400 text-sm">or</span>
                    </div>

                    <a href="register.php" class="mt-4 w-full flex justify-center items-center py-3 px-4 border-2 border-gray-200 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        Create New Account
                    </a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>