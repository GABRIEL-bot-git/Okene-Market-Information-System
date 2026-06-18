<?php
// manage_users.php
session_start();
require_once 'config/db.php';

// STRICT RBAC: Kick out anyone who is not an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}

$userRole = $_SESSION['role'];
$userFullName = $_SESSION['fullname'];
$currentUserId = $_SESSION['user_id'];
$successMsg = '';
$errorMsg = '';

// Handle Role Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_role'])) {
    $target_user_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];

    // Prevent Admin from changing their own role and locking themselves out
    if ($target_user_id == $currentUserId) {
        $errorMsg = "Security Protocol: You cannot change your own administrative privileges.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
            $stmt->execute(['role' => $new_role, 'id' => $target_user_id]);
            $successMsg = "User role updated successfully!";
        } catch (PDOException $e) {
            $errorMsg = "Error updating user: " . $e->getMessage();
        }
    }
}

// Handle User Deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    if ($delete_id == $currentUserId) {
         $errorMsg = "Security Protocol: You cannot delete your own account.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $delete_id]);
            $successMsg = "User account deleted successfully!";
        } catch (PDOException $e) {
            $errorMsg = "Cannot delete this user because they have submitted market prices. Downgrade their role to Farmer instead.";
        }
    }
}

// Fetch all users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Calculate quick stats
$totalUsers = count($users);
$adminCount = 0; $traderCount = 0; $farmerCount = 0;
foreach($users as $u) {
    if($u['role'] == 'Admin') $adminCount++;
    if($u['role'] == 'Trader') $traderCount++;
    if($u['role'] == 'Farmer') $farmerCount++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Okene Market Info System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { 'okene-green': '#115e3b', 'okene-light': '#e8f5e9' } } } }
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased flex min-h-screen">

    <aside class="w-64 bg-okene-green text-white flex flex-col justify-between hidden lg:flex shadow-xl z-20">
        <div>
            <div class="p-6 border-b border-green-800 flex items-center space-x-3">
                <span class="text-2xl">👥</span>
                <div>
                    <h1 class="font-bold text-sm tracking-wide">Okene Market</h1>
                    <p class="text-xs text-green-300">Information System</p>
                </div>
            </div>
            
            <nav class="p-4 space-y-1">
                <a href="dashboard.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>📊</span> <span>Dashboard</span></a>
                <a href="trends.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>📈</span> <span>Price Trends</span></a>
                
                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-green-400 uppercase tracking-wider">Administration</div>
                <a href="manage_users.php" class="flex items-center space-x-3 bg-white/10 px-4 py-2.5 rounded-lg text-sm font-medium"><span>👥</span> <span>Manage Users</span></a>
                <a href="manage_markets.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>🏛️</span> <span>Manage Markets</span></a>
                <a href="manage_crops.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>🌽</span> <span>Manage Crops</span></a>
                <a href="reports.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>📋</span> <span>Reports</span></a>
            </nav>
        </div>
        <div class="p-4 border-t border-green-800 flex items-center space-x-3">
            <a href="logout.php" class="flex items-center space-x-2 text-sm text-red-300 hover:text-white transition-colors w-full"><span>🚪</span> <span>Secure Logout</span></a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
        
        <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shrink-0">
            <h2 class="text-lg font-bold text-gray-800">User Management Console</h2>
            <span class="text-sm font-medium text-gray-700">Administrator: <?php echo htmlspecialchars($userFullName); ?></span>
        </header>

        <main class="flex-1 p-6 space-y-6 overflow-y-auto bg-gray-50">
            
            <?php if($successMsg): ?>
                <div class="p-4 bg-green-50 border-l-4 border-green-600 text-green-800 text-sm font-medium rounded-r-xl shadow-sm"><?php echo $successMsg; ?></div>
            <?php endif; ?>
            <?php if($errorMsg): ?>
                <div class="p-4 bg-red-50 border-l-4 border-red-600 text-red-800 text-sm font-medium rounded-r-xl shadow-sm"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                    <h4 class="text-3xl font-bold text-gray-800"><?php echo $totalUsers; ?></h4>
                    <p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Total Accounts</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                    <h4 class="text-3xl font-bold text-okene-green"><?php echo $farmerCount; ?></h4>
                    <p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Farmers</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                    <h4 class="text-3xl font-bold text-blue-600"><?php echo $traderCount; ?></h4>
                    <p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Traders</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                    <h4 class="text-3xl font-bold text-purple-600"><?php echo $adminCount; ?></h4>
                    <p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Admins</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">System Users Registry</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100">
                                <th class="py-3 px-6">Full Name</th>
                                <th class="py-3 px-6">Username</th>
                                <th class="py-3 px-6">Joined Date</th>
                                <th class="py-3 px-6">Access Role</th>
                                <th class="py-3 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm text-gray-600">
                            <?php foreach($users as $u): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-6 text-gray-800 font-semibold"><?php echo htmlspecialchars($u['fullname']); ?></td>
                                <td class="py-4 px-6 text-gray-500">@<?php echo htmlspecialchars($u['username']); ?></td>
                                <td class="py-4 px-6 text-gray-400 text-xs"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <td class="py-4 px-6">
                                    <?php 
                                        $badgeColor = 'bg-gray-100 text-gray-600';
                                        if($u['role'] == 'Admin') $badgeColor = 'bg-purple-100 text-purple-700';
                                        if($u['role'] == 'Trader') $badgeColor = 'bg-blue-100 text-blue-700';
                                        if($u['role'] == 'Farmer') $badgeColor = 'bg-green-100 text-green-700';
                                    ?>
                                    <span class="<?php echo $badgeColor; ?> px-2.5 py-1 rounded-full text-xs font-semibold">
                                        <?php echo htmlspecialchars($u['role']); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <form action="manage_users.php" method="POST" class="flex items-center m-0">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <select name="new_role" class="border border-gray-300 rounded-l-lg px-2 py-1.5 text-xs focus:outline-none bg-white">
                                                <option value="Farmer" <?php if($u['role'] == 'Farmer') echo 'selected'; ?>>Farmer</option>
                                                <option value="Trader" <?php if($u['role'] == 'Trader') echo 'selected'; ?>>Trader</option>
                                                <option value="Admin" <?php if($u['role'] == 'Admin') echo 'selected'; ?>>Admin</option>
                                            </select>
                                            <button type="submit" name="update_role" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1.5 rounded-r-lg text-xs font-medium transition-colors">Update</button>
                                        </form>
                                        
                                        <a href="manage_users.php?delete_id=<?php echo $u['id']; ?>" onclick="return confirm('WARNING: Are you sure you want to delete this user?');" class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded-lg ml-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>