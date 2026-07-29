<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$userId = $_SESSION['user_id'];
$successMsg = ''; $errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $phone = trim($_POST['phone_number']);
    $profile_pic = $_POST['current_pic'];

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $newName = "user_" . $userId . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], "uploads/" . $newName)) {
                $profile_pic = $newName;
            }
        }
    }
    $stmt = $pdo->prepare("UPDATE users SET phone_number = :phone, profile_pic = :pic WHERE id = :id");
    $stmt->execute(['phone' => $phone, 'pic' => $profile_pic, 'id' => $userId]);
    $successMsg = "Profile updated successfully!";
}

$userData = $pdo->prepare("SELECT phone_number, profile_pic FROM users WHERE id = :id");
$userData->execute(['id' => $userId]);
$user = $userData->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profile - Okene Market Info System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex min-h-screen">
    <!-- Main Content -->
    <div class="flex-1 p-8 flex justify-center items-start">
        <div class="w-full max-w-md bg-white rounded-xl shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">My Profile</h2>
                <a href="dashboard.php" class="text-sm text-blue-600 hover:underline">Back to Dashboard</a>
            </div>
            
            <?php if($successMsg) echo "<div class='text-green-700 bg-green-50 p-3 mb-4 rounded'>$successMsg</div>"; ?>
            
            <form action="profile.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="current_pic" value="<?php echo htmlspecialchars($user['profile_pic'] ?? 'default.png'); ?>">
                
                <div class="flex items-center space-x-4 mb-4">
                    <img src="uploads/<?php echo htmlspecialchars($user['profile_pic'] ?? 'default.png'); ?>" onerror="this.src='https://ui-avatars.com/api/?name=User&background=115e3b&color=fff'" class="w-20 h-20 rounded-full object-cover border">
                    <input type="file" name="profile_image" class="text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Phone Number</label>
                    <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" class="w-full border p-2 rounded">
                </div>
                <button type="submit" name="update_profile" class="w-full bg-[#115e3b] text-white p-2 rounded">Save Profile</button>
            </form>
        </div>
    </div>
</body>
</html>