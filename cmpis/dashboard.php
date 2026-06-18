<?php
// dashboard.php
session_start();
require_once 'config/db.php';

// Route back to login if session variables are missing
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userRole = $_SESSION['role'];
$userFullName = $_SESSION['fullname'];
$userId = $_SESSION['user_id'];
$successMsg = '';
$errorMsg = '';

// --- 1. HANDLE NEW PRICE SUBMISSION (Admin & Trader Only) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_price'])) {
    if ($userRole === 'Admin' || $userRole === 'Trader') {
        $commodity_id = $_POST['commodity_id'];
        $market_id = $_POST['market_id'];
        $price = $_POST['price'];
        $unit = trim($_POST['unit']);
        $date_recorded = date('Y-m-d');

        if (!empty($commodity_id) && !empty($market_id) && !empty($price) && !empty($unit)) {
            $insertStmt = $pdo->prepare("INSERT INTO prices (commodity_id, market_id, price, unit, date_recorded, recorded_by) VALUES (:c_id, :m_id, :price, :unit, :d_rec, :rec_by)");
            $insertStmt->execute(['c_id' => $commodity_id, 'm_id' => $market_id, 'price' => $price, 'unit' => $unit, 'd_rec' => $date_recorded, 'rec_by' => $userId]);
            $successMsg = "Market price recorded successfully!";
        }
    } else {
        $errorMsg = "Unauthorized action.";
    }
}

// --- 2. FETCH KPI STATISTICS FOR THE CARDS ---
$activeMarketsCount = $pdo->query("SELECT COUNT(*) FROM markets")->fetchColumn();
$commoditiesCount = $pdo->query("SELECT COUNT(*) FROM commodities")->fetchColumn();
$avgPrice = $pdo->query("SELECT AVG(price) FROM prices")->fetchColumn();

// --- 3. FETCH DATA FOR DROPDOWNS & TABLE ---
$allCommodities = $pdo->query("SELECT * FROM commodities")->fetchAll(PDO::FETCH_ASSOC);
$allMarkets = $pdo->query("SELECT * FROM markets")->fetchAll(PDO::FETCH_ASSOC);

$priceQuery = "
    SELECT p.price, p.unit, p.date_recorded, c.commodity_name, m.market_name, u.fullname as recorder 
    FROM prices p
    JOIN commodities c ON p.commodity_id = c.id
    JOIN markets m ON p.market_id = m.id
    LEFT JOIN users u ON p.recorded_by = u.id
    ORDER BY p.date_recorded DESC LIMIT 20
";
$livePrices = $pdo->query($priceQuery)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Okene Market Info System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { 'okene-green': '#115e3b', 'okene-light': '#e8f5e9' } } }
        }
        
        // Simple JS for the Modal
        function toggleModal() {
            const modal = document.getElementById('priceModal');
            modal.classList.toggle('hidden');
        }
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased flex min-h-screen relative">

    <aside class="w-64 bg-okene-green text-white flex flex-col justify-between hidden lg:flex shadow-xl z-20">
        <div>
            <div class="p-6 border-b border-green-800 flex items-center space-x-3">
                <span class="text-2xl">🧺</span>
                <div>
                    <h1 class="font-bold text-sm tracking-wide">Okene Market</h1>
                    <p class="text-xs text-green-300">Information System</p>
                </div>
            </div>
            
            <nav class="p-4 space-y-1">
                <a href="dashboard.php" class="flex items-center space-x-3 bg-white/10 px-4 py-2.5 rounded-lg text-sm font-medium"><span>📊</span> <span>Dashboard</span></a>
                <a href="trends.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>📈</span> <span>Price Trends</span></a>
                <?php if ($userRole === 'Admin'): ?>
                    <div class="pt-4 pb-2 px-4 text-xs font-semibold text-green-400 uppercase tracking-wider">Administration</div>
                    <a href="manage_users.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>👥</span> <span>Manage Users</span></a>
                    <a href="manage_markets.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>🏛️</span> <span>Manage Markets</span></a>
                    <a href="manage_crops.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>🌽</span> <span>Manage Crops</span></a>
                    <a href="reports.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>📋</span> <span>Reports</span></a>
                <?php endif; ?>
                <a href="logout.php" class="flex items-center space-x-3 text-red-300 hover:bg-red-950/20 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>🚪</span> <span>Logout</span></a>
            </nav>
        </div>
        
        <div class="p-4 border-t border-green-800 bg-green-950/40 flex items-center space-x-3">
            <div class="w-9 h-9 bg-green-700 rounded-full flex items-center justify-center text-sm font-bold uppercase"><?php echo substr($userFullName, 0, 2); ?></div>
            <div>
                <p class="text-xs font-semibold"><?php echo htmlspecialchars($userFullName); ?></p>
                <p class="text-[10px] text-green-300"><?php echo $userRole; ?> Account</p>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
        
        <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 z-10 shrink-0">
            <div class="flex items-center space-x-3">
                <h2 class="text-lg font-bold text-gray-800">System Dashboard</h2>
            </div>
            <div class="flex items-center space-x-4">
                
                <?php if ($userRole === 'Admin' || $userRole === 'Trader'): ?>
                    <button onclick="toggleModal()" class="bg-okene-green hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center space-x-2">
                        <span>+ Add Price</span>
                    </button>
                <?php endif; ?>

                <div class="flex items-center space-x-2 border-l border-gray-200 pl-4">
                    <span class="text-sm font-medium text-gray-700 hidden sm:inline capitalize"><?php echo htmlspecialchars($userFullName); ?> (<?php echo $userRole; ?>)</span>
                    <a href="logout.php" class="text-xs text-red-500 hover:underline ml-2 font-semibold">Logout</a>
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 space-y-6 overflow-y-auto bg-gray-50">
            
            <?php if($successMsg): ?>
                <div class="p-4 bg-green-50 border-l-4 border-green-600 text-green-800 text-sm font-medium rounded-r-xl shadow-sm"><?php echo $successMsg; ?></div>
            <?php endif; ?>
            <?php if($errorMsg): ?>
                <div class="p-4 bg-red-50 border-l-4 border-red-600 text-red-800 text-sm font-medium rounded-r-xl shadow-sm"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center space-x-4">
                    <div class="p-3 bg-green-50 text-okene-green rounded-lg text-xl">🏪</div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Active Markets</p>
                        <h4 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $activeMarketsCount; ?></h4>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center space-x-4">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-lg text-xl">📦</div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Commodities</p>
                        <h4 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $commoditiesCount; ?></h4>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center space-x-4">
                    <div class="p-3 bg-purple-50 text-purple-600 rounded-lg text-xl">🏷️</div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Average Price</p>
                        <h4 class="text-2xl font-bold text-gray-800 mt-1">₦<?php echo number_format($avgPrice ?? 0, 0); ?></h4>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center space-x-4">
                    <div class="p-3 bg-green-50 text-green-600 rounded-lg text-xl">🛡️</div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">System Role</p>
                        <h4 class="text-2xl font-bold text-green-600 mt-1"><?php echo $userRole; ?></h4>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden lg:col-span-2 flex flex-col">
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-white">
                        <h3 class="font-bold text-gray-800">Latest Market Prices</h3>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-[11px] font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100">
                                    <th class="py-3 px-4">Commodity</th>
                                    <th class="py-3 px-4">Market</th>
                                    <th class="py-3 px-4">Unit</th>
                                    <th class="py-3 px-4">Price (₦)</th>
                                    <th class="py-3 px-4">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm text-gray-600">
                                <?php if(empty($livePrices)): ?>
                                    <tr><td colspan="5" class="py-8 text-center text-gray-400">No prices recorded yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach($livePrices as $row): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3.5 px-4 font-medium text-gray-800"><?php echo htmlspecialchars($row['commodity_name']); ?></td>
                                        <td class="py-3.5 px-4 text-gray-500"><?php echo htmlspecialchars($row['market_name']); ?></td>
                                        <td class="py-3.5 px-4 text-gray-400 text-xs"><?php echo htmlspecialchars($row['unit']); ?></td>
                                        <td class="py-3.5 px-4 font-bold text-okene-green">₦<?php echo number_format($row['price'], 2); ?></td>
                                        <td class="py-3.5 px-4 text-xs text-gray-400"><?php echo date('M d', strtotime($row['date_recorded'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-50 pb-3">
                            <h3 class="font-bold text-gray-800 text-sm">Okene Local Conditions</h3>
                            <span class="text-xs text-gray-400"><?php echo date('M d, Y'); ?></span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-4xl">🌦️</span>
                            <div>
                                <h4 class="text-2xl font-bold text-gray-800">28°C</h4>
                                <p class="text-xs text-gray-500">Good condition for trading</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-okene-light p-5 rounded-xl border border-green-200 shadow-sm">
                        <h3 class="font-bold text-okene-green text-sm mb-2">System Notice</h3>
                        <p class="text-xs text-green-800 leading-relaxed">
                            <?php if ($userRole === 'Admin'): ?>
                                As an Administrator, ensure all submitted prices are accurate. You have full access to manage the system.
                            <?php elseif ($userRole === 'Trader'): ?>
                                Welcome, Trader. Please use the "+ Add Price" button to submit today's market rates for your commodities.
                            <?php else: ?>
                                Welcome, Farmer. Use this dashboard to compare prices across different markets before selling your produce.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="priceModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-lg text-gray-800">Add New Market Price</h3>
                <button onclick="toggleModal()" class="text-gray-400 hover:text-red-500 text-xl font-bold">&times;</button>
            </div>
            
            <form action="dashboard.php" method="POST" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Commodity</label>
                    <select name="commodity_id" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-okene-green focus:outline-none">
                        <?php foreach($allCommodities as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['commodity_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Market Location</label>
                    <select name="market_id" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-okene-green focus:outline-none">
                        <?php foreach($allMarkets as $m): ?>
                            <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['market_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit (e.g., 1 Bag)</label>
                        <input type="text" name="unit" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-okene-green focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (₦)</label>
                        <input type="number" step="0.01" name="price" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-okene-green focus:outline-none"/>
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" onclick="toggleModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" name="submit_price" class="px-4 py-2 text-sm font-medium text-white bg-okene-green rounded-lg hover:bg-green-800 transition-colors shadow-sm">Submit Price</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>