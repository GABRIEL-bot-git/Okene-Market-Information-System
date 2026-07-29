<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$myId = $_SESSION['user_id'];
$chatWithId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message']) && $chatWithId) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$myId, $chatWithId, $message]);
        header("Location: chat.php?user_id=" . $chatWithId);
        exit();
    }
}

// Mark messages as read when a chat is opened
if ($chatWithId) {
    $markRead = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $markRead->execute([$chatWithId, $myId]);
}

// Determine who to show in the sidebar (Search Results vs Active Chats)
if (!empty($searchTerm)) {
    // User is searching: Search by Name, Username, or Phone Number
    $searchQuery = "%{$searchTerm}%";
    $stmtUsers = $pdo->prepare("
        SELECT id, fullname, role, username, 0 as unread_count
        FROM users 
        WHERE id != ? AND (fullname LIKE ? OR username LIKE ? OR phone_number LIKE ?)
        ORDER BY fullname ASC
    ");
    $stmtUsers->execute([$myId, $searchQuery, $searchQuery, $searchQuery]);
    $activeUsers = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Default View: Fetch active chats + Admins + Check for unread messages
    $stmtUsers = $pdo->prepare("
        SELECT u.id, u.fullname, u.role, u.username,
               (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
        FROM users u
        WHERE u.id != ?
          AND (
              u.role = 'Admin' 
              OR u.id IN (SELECT sender_id FROM messages WHERE receiver_id = ?)
              OR u.id IN (SELECT receiver_id FROM messages WHERE sender_id = ?)
          )
        ORDER BY unread_count DESC, u.fullname ASC
    ");
    $stmtUsers->execute([$myId, $myId, $myId, $myId]);
    $activeUsers = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch chat history if a specific user is selected
$messages = [];
$chatUser = null;
if ($chatWithId) {
    $stmt = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
    $stmt->execute([$chatWithId]);
    $chatUser = $stmt->fetchColumn();

    $msgStmt = $pdo->prepare("
        SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY sent_at ASC
    ");
    $msgStmt->execute([$myId, $chatWithId, $chatWithId, $myId]);
    $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Messages - Okene Market Info</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { 'okene-green': '#115e3b' } } } }</script>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    
    <!-- Sidebar / Active Conversations & Search -->
    <div class="w-80 bg-white border-r flex flex-col z-20 shadow-md">
        <div class="p-4 border-b bg-okene-green text-white flex justify-between items-center shrink-0">
            <h2 class="font-bold text-sm tracking-wide">System Chat</h2>
            <a href="dashboard.php" class="text-xs hover:text-green-200 transition-colors font-medium">← Dashboard</a>
        </div>
        
        <!-- Search Input Block -->
        <div class="p-3 border-b bg-gray-50 shrink-0">
            <form action="chat.php" method="GET" class="flex items-center">
                <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search by name, username..." class="flex-1 bg-white border border-gray-300 rounded-l-lg px-3 py-2 text-sm focus:outline-none focus:border-okene-green focus:ring-1 focus:ring-okene-green transition-shadow">
                <button type="submit" class="bg-okene-green text-white px-3 py-2 rounded-r-lg text-sm hover:bg-green-800 transition-colors">🔍</button>
            </form>
            <?php if(!empty($searchTerm)): ?>
                <div class="mt-2 text-xs">
                    <span class="text-gray-500">Results for "<?php echo htmlspecialchars($searchTerm); ?>"</span>
                    <a href="chat.php" class="text-red-500 font-medium ml-2 hover:underline">Clear</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- User List -->
        <div class="flex-1 overflow-y-auto">
            <?php if(empty($activeUsers)): ?>
                <div class="p-8 text-center text-gray-400">
                    <span class="text-3xl block mb-2">📭</span>
                    <p class="text-sm font-medium">No users found.</p>
                </div>
            <?php else: ?>
                <?php foreach($activeUsers as $u): ?>
                    <a href="chat.php?user_id=<?php echo $u['id']; ?>" class="block p-4 border-b hover:bg-gray-50 transition-colors relative <?php echo ($chatWithId == $u['id']) ? 'bg-green-50/50' : ''; ?>">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-bold text-sm text-gray-800 flex items-center space-x-1">
                                    <span><?php echo htmlspecialchars($u['fullname']); ?></span>
                                </p>
                                <p class="text-[11px] text-gray-500 font-medium mt-0.5">@<?php echo htmlspecialchars($u['username']); ?> • <?php echo htmlspecialchars($u['role']); ?></p>
                            </div>
                            
                            <!-- The New Message Tick Indicator -->
                            <?php if($u['unread_count'] > 0): ?>
                                <div class="flex items-center space-x-1" title="New Message">
                                    <span class="text-[10px] font-bold text-okene-green">NEW</span>
                                    <svg class="w-5 h-5 text-okene-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="flex-1 flex flex-col bg-gray-50 relative">
        <?php if(!$chatWithId): ?>
            <div class="flex-1 flex flex-col items-center justify-center text-gray-400">
                <span class="text-5xl mb-4 opacity-50">💬</span>
                <p class="font-medium text-gray-500">Search for a user or select a conversation to start.</p>
            </div>
        <?php else: ?>
            <!-- Chat Header -->
            <div class="p-4 border-b bg-white flex items-center justify-between shadow-sm z-10 shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-okene-green font-bold text-lg">
                        <?php echo substr($chatUser, 0, 1); ?>
                    </div>
                    <h3 class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($chatUser); ?></h3>
                </div>
                <a href="chat.php?user_id=<?php echo $chatWithId; ?>" class="text-xs bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-200 font-medium transition-colors flex items-center space-x-1">
                    <span>🔄 Refresh</span>
                </a>
            </div>
            
            <!-- Messages Container -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4" id="chatBox">
                <?php if(empty($messages)): ?>
                    <div class="text-center mt-10">
                        <span class="bg-gray-200 text-gray-500 text-xs px-3 py-1 rounded-full font-medium">This is the start of your conversation</span>
                    </div>
                <?php endif; ?>
                
                <?php foreach($messages as $m): 
                    $isMe = ($m['sender_id'] == $myId);
                ?>
                    <div class="flex <?php echo $isMe ? 'justify-end' : 'justify-start'; ?>">
                        <div class="max-w-[70%] rounded-2xl px-5 py-2.5 shadow-sm <?php echo $isMe ? 'bg-okene-green text-white rounded-br-sm' : 'bg-white border border-gray-100 text-gray-800 rounded-bl-sm'; ?>">
                            <p class="text-sm leading-relaxed"><?php echo htmlspecialchars($m['message']); ?></p>
                            <span class="text-[10px] <?php echo $isMe ? 'text-green-200' : 'text-gray-400'; ?> block text-right mt-1 font-medium tracking-wide">
                                <?php echo date('h:i A', strtotime($m['sent_at'])); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Input Box -->
            <div class="p-4 bg-white border-t shrink-0">
                <form action="chat.php?user_id=<?php echo $chatWithId; ?>" method="POST" class="flex space-x-3">
                    <input type="text" name="message" required autocomplete="off" placeholder="Write your message here..." class="flex-1 bg-gray-50 border border-gray-200 rounded-full px-5 py-3 text-sm focus:outline-none focus:border-okene-green focus:bg-white transition-colors">
                    <button type="submit" name="send_message" class="bg-okene-green text-white rounded-full px-8 py-3 text-sm font-bold hover:bg-green-800 transition-colors shadow-md">Send</button>
                </form>
            </div>
            
            <script>
                // Auto-scroll to the bottom of the chat box on load
                const chatBox = document.getElementById('chatBox');
                chatBox.scrollTop = chatBox.scrollHeight;
            </script>
        <?php endif; ?>
    </div>
</body>
</html>