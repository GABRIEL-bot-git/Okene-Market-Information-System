<?php
// view_profile.php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = $_SESSION['user_id'];
$targetUserId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Prevent users from viewing a broken profile if no ID is passed
if ($targetUserId === 0) {
    header("Location: dashboard.php");
    exit();
}

// Fetch the target user's public data
$stmt = $pdo->prepare("SELECT id, fullname, username, role, phone_number, profile_pic, created_at FROM users WHERE id = :id");
$stmt->execute(['id' => $targetUserId]);
$targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$targetUser) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($targetUser['fullname']); ?> - Market Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { 'okene-green': '#115e3b', 'okene-light': '#e8f5e9' } } } }</script>
</head>
<body class="bg-gray-50 font-sans antialiased flex min-h-screen items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <!-- Header Banner -->
        <div class="h-32 bg-okene-green relative">
            <a href="dashboard.php" class="absolute top-4 left-4 text-white/80 hover:text-white bg-black/20 p-2 rounded-full backdrop-blur-sm transition-all text-sm font-medium flex items-center space-x-1">
                <span>← Back</span>
            </a>
        </div>

        <div class="px-8 pb-8 relative text-center">
            <!-- Profile Picture -->
            <div class="w-28 h-28 mx-auto -mt-14 bg-white rounded-full border-4 border-white shadow-lg overflow-hidden flex items-center justify-center">
                <img src="uploads/<?php echo htmlspecialchars($targetUser['profile_pic'] ?? 'default.png'); ?>" 
                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($targetUser['fullname']); ?>&background=115e3b&color=fff&size=128'" 
                     class="w-full h-full object-cover">
            </div>

            <!-- Profile Info -->
            <div class="mt-4">
                <h2 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($targetUser['fullname']); ?></h2>
                <p class="text-gray-500 text-sm mt-1">@<?php echo htmlspecialchars($targetUser['username']); ?></p>
                
                <div class="mt-3 inline-block bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wide">
                    Verified <?php echo htmlspecialchars($targetUser['role']); ?>
                </div>
            </div>

            <!-- Stats & Contact -->
            <div class="mt-6 border-t border-gray-100 pt-6 grid grid-cols-2 gap-4 text-left">
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Trust Rating</p>
                    <p class="font-bold text-gray-800 flex items-center space-x-1 mt-1">
                        <span class="text-yellow-400">⭐⭐⭐⭐⭐</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-400 font-semibold uppercase">Member Since</p>
                    <p class="font-bold text-gray-800 mt-1"><?php echo date('M Y', strtotime($targetUser['created_at'])); ?></p>
                </div>
            </div>

            <!-- Contact Info Block -->
            <?php if (!empty($targetUser['phone_number'])): ?>
                <div class="mt-6 bg-green-50/50 p-4 rounded-xl border border-green-100 flex items-center justify-between text-left">
                    <div class="flex items-center space-x-3">
                        <div class="bg-white p-2 rounded-lg shadow-sm text-lg">📞</div>
                        <div>
                            <p class="text-[11px] text-green-700 font-bold uppercase tracking-wider">Direct Contact</p>
                            <p class="font-bold text-gray-900 text-sm mt-0.5"><?php echo htmlspecialchars($targetUser['phone_number']); ?></p>
                        </div>
                    </div>
                    <a href="tel:<?php echo htmlspecialchars($targetUser['phone_number']); ?>" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-xs font-bold transition-colors shadow-sm">
                        Call Now
                    </a>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="mt-6 space-y-3">
                <?php if ($currentUserId !== $targetUserId): ?>
                    <a href="chat.php?user_id=<?php echo $targetUser['id']; ?>" class="w-full flex items-center justify-center space-x-2 bg-okene-green hover:bg-green-800 text-white font-semibold py-3 rounded-xl transition-all shadow-md hover:shadow-lg">
                        <span>💬</span>
                        <span>Send Direct Message</span>
                    </a>
                <?php else: ?>
                    <a href="profile.php" class="w-full flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-xl transition-all border border-gray-200">
                        Edit My Profile
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>