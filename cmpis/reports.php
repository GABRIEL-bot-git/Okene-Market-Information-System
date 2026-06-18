<?php
// reports.php
session_start();
require_once 'config/db.php';

// STRICT RBAC: Kick out anyone who is not an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}

$userRole = $_SESSION['role'];
$userFullName = $_SESSION['fullname'];

// Fetch all historical prices for the report
$priceQuery = "
    SELECT p.price, p.unit, p.date_recorded, c.commodity_name, m.market_name, u.fullname as recorder 
    FROM prices p
    JOIN commodities c ON p.commodity_id = c.id
    JOIN markets m ON p.market_id = m.id
    LEFT JOIN users u ON p.recorded_by = u.id
    ORDER BY p.date_recorded DESC
";
$reportData = $pdo->query($priceQuery)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Reports - Okene Market Info System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { 'okene-green': '#115e3b', 'okene-light': '#e8f5e9' } } } }
    </script>
    <style>
        /* This hides the sidebar and buttons when printing/saving as PDF */
        @media print {
            .no-print { display: none !important; }
            body { background-color: white !important; }
            .print-full-width { width: 100% !important; margin: 0 !important; padding: 0 !important; border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased flex min-h-screen">

    <aside class="w-64 bg-okene-green text-white flex flex-col justify-between hidden lg:flex shadow-xl z-20 no-print">
        <div>
            <div class="p-6 border-b border-green-800 flex items-center space-x-3">
                <span class="text-2xl">📋</span>
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
                <a href="manage_crops.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>🌽</span> <span>Manage Crops</span></a>
                <a href="reports.php" class="flex items-center space-x-3 bg-white/10 px-4 py-2.5 rounded-lg text-sm font-medium"><span>📋</span> <span>Reports</span></a>
            </nav>
        </div>
        <div class="p-4 border-t border-green-800 flex items-center space-x-3">
            <a href="logout.php" class="flex items-center space-x-2 text-sm text-red-300 hover:text-white transition-colors w-full"><span>🚪</span> <span>Secure Logout</span></a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden print-full-width">
        
        <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shrink-0 no-print">
            <h2 class="text-lg font-bold text-gray-800">System Activity Reports</h2>
            <div class="flex items-center space-x-4">
                <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center space-x-2">
                    <span>🖨️ Print / Export PDF</span>
                </button>
            </div>
        </header>

        <main class="flex-1 p-6 space-y-6 overflow-y-auto bg-gray-50 print-full-width">
            
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden print-full-width">
                <div class="p-5 border-b border-gray-100 text-center">
                    <h2 class="font-bold text-2xl text-gray-800">Okene Centralized Market Price Report</h2>
                    <p class="text-sm text-gray-500 mt-1">Generated on: <?php echo date('F j, Y'); ?></p>
                </div>
                
                <div class="overflow-x-auto p-4">
                    <table class="w-full text-left border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100 text-xs font-bold uppercase tracking-wider text-gray-700 border-b border-gray-200">
                                <th class="py-3 px-4 border border-gray-200">Date Recorded</th>
                                <th class="py-3 px-4 border border-gray-200">Commodity</th>
                                <th class="py-3 px-4 border border-gray-200">Market</th>
                                <th class="py-3 px-4 border border-gray-200">Unit</th>
                                <th class="py-3 px-4 border border-gray-200">Price (₦)</th>
                                <th class="py-3 px-4 border border-gray-200">Data Entry By</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-600">
                            <?php foreach($reportData as $row): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-2.5 px-4 border border-gray-200"><?php echo date('M d, Y', strtotime($row['date_recorded'])); ?></td>
                                <td class="py-2.5 px-4 border border-gray-200 font-semibold text-gray-800"><?php echo htmlspecialchars($row['commodity_name']); ?></td>
                                <td class="py-2.5 px-4 border border-gray-200"><?php echo htmlspecialchars($row['market_name']); ?></td>
                                <td class="py-2.5 px-4 border border-gray-200 text-xs"><?php echo htmlspecialchars($row['unit']); ?></td>
                                <td class="py-2.5 px-4 border border-gray-200 font-bold text-okene-green">₦<?php echo number_format($row['price'], 2); ?></td>
                                <td class="py-2.5 px-4 border border-gray-200 text-xs italic text-gray-500"><?php echo htmlspecialchars($row['recorder'] ?? 'System Seeder'); ?></td>
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