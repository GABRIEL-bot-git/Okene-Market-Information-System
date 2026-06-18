<?php
// trends.php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userRole = $_SESSION['role'];
$userFullName = $_SESSION['fullname'];

// Fetch all commodities AND markets for the dropdown filters
$commodities = $pdo->query("SELECT * FROM commodities ORDER BY commodity_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$markets = $pdo->query("SELECT * FROM markets ORDER BY market_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Safely default to the first available commodity and market if none is selected yet
$default_cid = !empty($commodities) ? $commodities[0]['id'] : 1;
$default_mid = !empty($markets) ? $markets[0]['id'] : 1;

$selected_commodity_id = isset($_GET['commodity_id']) ? $_GET['commodity_id'] : $default_cid;
$selected_market_id = isset($_GET['market_id']) ? $_GET['market_id'] : $default_mid;

// DYNAMIC SQL FIX: Fetch data based on BOTH selected commodity and market
$stmt = $pdo->prepare("
    SELECT date_recorded, price 
    FROM prices 
    WHERE commodity_id = :cid AND market_id = :mid 
    ORDER BY date_recorded ASC
");
$stmt->execute([
    'cid' => $selected_commodity_id, 
    'mid' => $selected_market_id
]);
$chartData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format Data for JavaScript Chart
$labels = [];
$prices = [];
foreach ($chartData as $row) {
    $labels[] = date('M d, Y', strtotime($row['date_recorded']));
    $prices[] = $row['price'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Trends - Okene Market Info System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { 'okene-green': '#115e3b', 'okene-light': '#e8f5e9' } } } }
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased flex min-h-screen">

    <aside class="w-64 bg-okene-green text-white flex flex-col justify-between hidden lg:flex shadow-xl z-20">
        <div>
            <div class="p-6 border-b border-green-800 flex items-center space-x-3">
                <span class="text-2xl">📈</span>
                <div>
                    <h1 class="font-bold text-sm tracking-wide">Okene Market</h1>
                    <p class="text-xs text-green-300">Trend Analytics</p>
                </div>
            </div>
            
            <nav class="p-4 space-y-1">
                <a href="dashboard.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>📊</span> <span>Dashboard</span></a>
                <a href="trends.php" class="flex items-center space-x-3 bg-white/10 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors"><span>📈</span> <span>Price Trends</span></a>
                
                <?php if ($userRole === 'Admin'): ?>
                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-green-400 uppercase tracking-wider">Administration</div>
                <a href="manage_users.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>👥</span> <span>Manage Users</span></a>
                <a href="manage_markets.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>🏛️</span> <span>Manage Markets</span></a>
                <a href="manage_crops.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>🌽</span> <span>Manage Crops</span></a>
                <a href="reports.php" class="flex items-center space-x-3 text-green-100 hover:bg-white/5 px-4 py-2.5 rounded-lg text-sm transition-colors"><span>📋</span> <span>Reports</span></a>
                <?php endif; ?>
            </nav>
        </div>
        <div class="p-4 border-t border-green-800 flex items-center space-x-3">
            <a href="logout.php" class="flex items-center space-x-2 text-sm text-red-300 hover:text-white transition-colors w-full"><span>🚪</span> <span>Secure Logout</span></a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
        
        <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shrink-0">
            <h2 class="text-lg font-bold text-gray-800">Historical Price Analytics</h2>
            <span class="text-sm font-medium text-gray-700">User: <?php echo htmlspecialchars($userFullName); ?></span>
        </header>

        <main class="flex-1 p-6 space-y-6 overflow-y-auto bg-gray-50">
            
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">Market Trend Visualizer</h3>
                    <p class="text-sm text-gray-500">Filter by commodity and specific market location.</p>
                </div>
                
                <form action="trends.php" method="GET" class="flex flex-col sm:flex-row items-center gap-2">
                    <select name="commodity_id" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-okene-green focus:outline-none bg-white w-full sm:w-auto text-sm">
                        <?php foreach($commodities as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($selected_commodity_id == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['commodity_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="market_id" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-okene-green focus:outline-none bg-white w-full sm:w-auto text-sm">
                        <?php foreach($markets as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php echo ($selected_market_id == $m['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($m['market_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="bg-okene-green hover:bg-green-800 text-white px-6 py-2 rounded-lg font-medium transition-colors shadow-sm w-full sm:w-auto text-sm">Analyze</button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm relative h-[500px] w-full">
                <?php if (empty($prices)): ?>
                    <div class="flex h-full items-center justify-center text-gray-400 font-medium">No historical data available for this specific commodity in this market.</div>
                <?php else: ?>
                    <canvas id="priceChart"></canvas>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <script>
        const labels = <?php echo json_encode($labels); ?>;
        const dataPoints = <?php echo json_encode($prices); ?>;

        if(labels.length > 0) {
            const ctx = document.getElementById('priceChart').getContext('2d');
            let gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(17, 94, 59, 0.4)');
            gradient.addColorStop(1, 'rgba(17, 94, 59, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Price (₦)',
                        data: dataPoints,
                        borderColor: '#115e3b',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#115e3b',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: { borderDash: [5, 5], color: '#e5e7eb' },
                            ticks: { callback: function(value) { return '₦' + value; }, font: { family: 'system-ui' } }
                        },
                        x: { grid: { display: false }, ticks: { font: { family: 'system-ui' } } }
                    }
                }
            });
        }
    </script>
</body>
</html>