<?php
// manage_crops.php
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

// Handle Adding a New Crop
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_crop'])) {
    $crop_name = trim($_POST['crop_name']);
    $category = trim($_POST['category']);

    if (!empty($crop_name) && !empty($category)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO commodities (commodity_name, category) VALUES (:name, :category)");
            $stmt->execute(['name' => $crop_name, 'category' => $category]);
            $successMsg = "New crop added successfully!";
        } catch (PDOException $e) {
            $errorMsg = "Error adding crop: " . $e->getMessage();
        }
    } else {
        $errorMsg = "Please fill all fields.";
    }
}

// Handle Deleting a Crop
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM commodities WHERE id = :id");
        $stmt->execute(['id' => $delete_id]);
        $successMsg = "Crop deleted successfully!";
    } catch (PDOException $e) {
        $errorMsg = "Cannot delete this crop because it is linked to existing market prices.";
    }
}

// Fetch all crops
$commodities = $pdo->query("SELECT * FROM commodities ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Crops - Okene Market Info System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { 'okene-green': '#115e3b', 'okene-light': '#e8f5e9' } } } }
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-okene-green text-white flex flex-col justify-between hidden lg:flex shadow-xl z-20">
        <div>
            <div class="p-6 border-b border-green-800 flex items-center space-x-3">
                <span class="text-2xl">🌽</span>
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
                <a href="manage_markets.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>🏛️</span> <span>Manage Markets</span></a>
                <a href="manage_crops.php" class="flex items-center space-x-3 bg-white/10 px-4 py-2.5 rounded-lg text-sm font-medium"><span>🌽</span> <span>Manage Crops</span></a>
                <a href="reports.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>📋</span> <span>Reports</span></a>
            </nav>
        </div>
        <div class="p-4 border-t border-green-800 flex items-center space-x-3">
            <a href="logout.php" class="flex items-center space-x-2 text-sm text-red-300 hover:text-white transition-colors w-full"><span>🚪</span> <span>Secure Logout</span></a>
        </div>
    </aside>

    <!-- MAIN PANEL -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
        
        <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shrink-0">
            <h2 class="text-lg font-bold text-gray-800">Crop Management Registry</h2>
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
                
                <!-- ADD CROP FORM -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 h-fit">
                    <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Add New Commodity</h3>
                    <form action="manage_crops.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Crop Name</label>
                            <input type="text" name="crop_name" required placeholder="e.g., Sorghum" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-okene-green focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-okene-green focus:outline-none bg-white">
                                <option value="Grains">Grains</option>
                                <option value="Tubers">Tubers</option>
                                <option value="Legumes">Legumes</option>
                                <option value="Vegetables">Vegetables</option>
                                <option value="Fruits">Fruits</option>
                                <option value="Livestock">Livestock</option>
                            </select>
                        </div>
                        <button type="submit" name="add_crop" class="w-full bg-okene-green hover:bg-green-800 text-white font-medium py-2.5 rounded-lg transition-colors shadow-sm mt-2">
                            Add to Database
                        </button>
                    </form>
                </div>

                <!-- CROP LIST TABLE -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden lg:col-span-2">
                    <div class="p-5 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800">Registered Commodities</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100">
                                    <th class="py-3 px-6">ID</th>
                                    <th class="py-3 px-6">Commodity Name</th>
                                    <th class="py-3 px-6">Category</th>
                                    <th class="py-3 px-6 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm text-gray-600">
                                <?php foreach($commodities as $c): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-3 px-6 font-medium">#<?php echo $c['id']; ?></td>
                                    <td class="py-3 px-6 text-gray-800 font-semibold"><?php echo htmlspecialchars($c['commodity_name']); ?></td>
                                    <td class="py-3 px-6"><span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs"><?php echo htmlspecialchars($c['category']); ?></span></td>
                                    <td class="py-3 px-6 text-right">
                                        <a href="manage_crops.php?delete_id=<?php echo $c['id']; ?>" onclick="return confirm('Are you sure you want to delete this crop?');" class="text-red-500 hover:text-red-700 font-medium text-xs bg-red-50 px-3 py-1.5 rounded-lg">Delete</a>
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