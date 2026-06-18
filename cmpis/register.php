<?php
// register.php
session_start();
require_once 'config/db.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // SECURITY PATCH: Strict Server-Side Validation against Privilege Escalation
    $allowed_roles = ['Farmer', 'Trader'];

    if (!in_array($role, $allowed_roles)) {
        $errorMsg = "Security Violation: Invalid or unauthorized account role requested.";
    } elseif (!empty($fullname) && !empty($username) && !empty($password)) {
        try {
            // Check if username already exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
            $checkStmt->execute(['username' => $username]);
            
            if ($checkStmt->rowCount() > 0) {
                $errorMsg = "Username is already taken.";
            } else {
                // Securely hash the password using industry-standard BCRYPT
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                $insertStmt = $pdo->prepare("INSERT INTO users (fullname, username, password, role) VALUES (:fullname, :username, :password, :role)");
                $insertStmt->execute([
                    'fullname' => $fullname,
                    'username' => $username,
                    'password' => $hashedPassword,
                    'role' => $role
                ]);
                
                $successMsg = "Account created successfully! You can now login.";
            }
        } catch (PDOException $e) {
            $errorMsg = "System Error: " . $e->getMessage();
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
    <title>Register - Okene Market Info System</title>
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
<body class="bg-gray-50 font-sans antialiased flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md border border-gray-100">
        <div class="flex flex-col items-center mb-6">
            <div class="bg-green-50 p-3 rounded-full mb-3 border border-green-100">
                <svg class="w-8 h-8 text-okene-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Create An Account</h2>
            <p class="text-gray-500 text-sm mt-1">Join the Centralized Market Information System</p>
        </div>

        <form action="register.php" method="POST" class="space-y-5">
            <?php if($errorMsg): ?>
                <div class="p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm font-medium rounded-r-lg"><?php echo htmlspecialchars($errorMsg); ?></div>
            <?php endif; ?>
            
            <?php if($successMsg): ?>
                <div class="p-3 bg-green-50 border-l-4 border-green-500 text-green-700 text-sm font-medium rounded-r-lg"><?php echo htmlspecialchars($successMsg); ?></div>
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" name="fullname" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-okene-green" placeholder="e.g., Aliyu Yusuf">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-okene-green" placeholder="Choose a unique username">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-okene-green" placeholder="Create a secure password">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Account Role</label>
                <select name="role" required class="w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-okene-green">
                    <option value="Farmer">Farmer (View & Search Prices)</option>
                    <option value="Trader">Trader (Submit Price Updates)</option>
                </select>
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-lg shadow-sm text-sm font-medium text-white bg-okene-green hover:bg-green-800 transition-colors mt-2">
                Register Account
            </button>

            <p class="text-center text-sm text-gray-600 mt-4">
                Already have an account? <a href="login.php" class="text-okene-green font-semibold hover:underline">Sign In</a>
            </p>
        </form>
    </div>
</body>
</html>