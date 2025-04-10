<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$success = "";
$error = "";

// On form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $file_id = $_POST["file_id"];
    $recipient_email = $_POST["recipient"];

    // Check recipient exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $recipient_email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $recipient = $res->fetch_assoc();
        $recipient_id = $recipient["id"];

        // Add share record
        $stmt = $conn->prepare("INSERT INTO file_shares (file_id, owner_id, recipient_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $file_id, $user_id, $recipient_id);
        if ($stmt->execute()) {
            $success = "File shared successfully with $recipient_email";
        } else {
            $error = "Failed to share file.";
        }
    } else {
        $error = "Recipient not found.";
    }
}

// Get files owned by current user
$stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$files = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">
<head>
  <meta charset="UTF-8">
  <title>Share File</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col items-center justify-center py-10 px-4">
  <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow w-full max-w-lg">
    <h2 class="text-2xl font-bold mb-4 text-center">Share a File</h2>

    <?php if ($success): ?>
      <p class="text-green-600 text-center"><?= $success ?></p>
    <?php elseif ($error): ?>
      <p class="text-red-600 text-center"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <label>Select File to Share:</label>
      <select name="file_id" required class="w-full p-2 rounded border dark:bg-gray-700">
        <?php while ($row = $files->fetch_assoc()): ?>
          <option value="<?= $row['id'] ?>"><?= $row['filename'] ?></option>
        <?php endwhile; ?>
      </select>

      <input type="email" name="recipient" placeholder="Recipient's email" required class="w-full p-2 rounded border dark:bg-gray-700" />

      <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Share</button>
    </form>

    <div class="mt-6 text-center">
      <a href="dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
    </div>
  </div>
</body>
</html>
