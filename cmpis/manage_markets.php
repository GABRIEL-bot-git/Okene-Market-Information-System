<?php
// manage_markets.php
session_start();
require_once 'config/db.php';

// STRICT RBAC: Kick out anyone who is not an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}

$userRole = $_SESSION['role'];
$userFullName = $_SESSION['fullname'];
$successMsg = '';
$errorMsg = '';

// Handle Adding a New Market
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_market'])) {
    $market_name = trim($_POST['market_name']);
    $location = trim($_POST['location']);

    if (!empty($market_name) && !empty($location)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO markets (market_name, location) VALUES (:name, :location)");
            $stmt->execute(['name' => $market_name, 'location' => $location]);
            $successMsg = "New market location added successfully!";
        } catch (PDOException $e) {
            $errorMsg = "Error adding market: " . $e->getMessage();
        }
    } else {
        $errorMsg = "Please fill all fields.";
    }
}

// Handle Deleting a Market
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM markets WHERE id = :id");
        $stmt->execute(['id' => $delete_id]);
        $successMsg = "Market deleted successfully!";
    } catch (PDOException $e) {
        $errorMsg = "Cannot delete this market because it is linked to existing market prices.";
    }
}

// Fetch all markets
$markets = $pdo->query("SELECT * FROM markets ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Markets - Okene Market Info System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { 'okene-green': '#115e3b', 'okene-light': '#e8f5e9' } } } }
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased flex min-h-screen">

    <aside class="w-64 bg-okene-green text-white flex flex-col justify-between hidden lg:flex shadow-xl z-20">
        <div>
            <div class="p-6 border-b border-green-800 flex items-center space-x-3">
                <span class="text-2xl">🏛️</span>
                <div>
                    <h1 class="font-bold text-sm tracking-wide">Okene Market</h1>
                    <p class="text-xs text-green-300">Information System</p>
                </div>
            </div>
            
            <nav class="p-4 space-y-1">
                <a href="dashboard.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>📊</span> <span>Dashboard</span></a>
                <a href="trends.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>📈</span> <span>Price Trends</span></a>
                
                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-green-400 uppercase tracking-wider">Administration</div>
                <a href="manage_users.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>👥</span> <span>Manage Users</span></a>
                <a href="manage_markets.php" class="flex items-center space-x-3 bg-white/10 px-4 py-2.5 rounded-lg text-sm font-medium"><span>🏛️</span> <span>Manage Markets</span></a>
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
            <h2 class="text-lg font-bold text-gray-800">Market Location Registry</h2>
            <span class="text-sm font-medium text-gray-700">Administrator: <?php echo htmlspecialchars($userFullName); ?></span>
        </header>

        <main class="flex-1 p-6 space-y-6 overflow-y-auto bg-gray-50">
            
            <?php if($successMsg): ?>
                <div class="p-4 bg-green-50 border-l-4 border-green-600 text-green-800 text-sm font-medium rounded-r-xl shadow-sm"><?php echo $successMsg; ?></div>
            <?php endif; ?>
            <?php if($errorMsg): ?>
                <div class="p-4 bg-red-50 border-l-4 border-red-600 text-red-800 text-sm font-medium rounded-r-xl shadow-sm"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 h-fit">
                    <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Register New Market</h3>
                    <form action="manage_markets.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Market Name</label>
                            <input type="text" name="market_name" required placeholder="e.g., Kuroko Market" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-okene-green focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Location / Ward</label>
                            <input type="text" name="location" required placeholder="e.g., Kuroko Ward" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-okene-green focus:outline-none">
                        </div>
                        <button type="submit" name="add_market" class="w-full bg-okene-green hover:bg-green-800 text-white font-medium py-2.5 rounded-lg transition-colors shadow-sm mt-2">
                            Add Market
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden lg:col-span-2">
                    <div class="p-5 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800">Active Market Locations</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100">
                                    <th class="py-3 px-6">ID</th>
                                    <th class="py-3 px-6">Market Name</th>
                                    <th class="py-3 px-6">Location</th>
                                    <th class="py-3 px-6 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm text-gray-600">
                                <?php foreach($markets as $m): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-3 px-6 font-medium">#<?php echo $m['id']; ?></td>
                                    <td class="py-3 px-6 text-gray-800 font-semibold"><?php echo htmlspecialchars($m['market_name']); ?></td>
                                    <td class="py-3 px-6 text-gray-500"><?php echo htmlspecialchars($m['location']); ?></td>
                                    <td class="py-3 px-6 text-right">
                                        <a href="manage_markets.php?delete_id=<?php echo $m['id']; ?>" onclick="return confirm('Are you sure you want to delete this market?');" class="text-red-500 hover:text-red-700 font-medium text-xs bg-red-50 px-3 py-1.5 rounded-lg">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>