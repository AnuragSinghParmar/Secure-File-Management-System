<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch all files of this user
$stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">
<head>
  <meta charset="UTF-8">
  <title>File Metadata</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col items-center justify-center py-10 px-4">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg w-full max-w-4xl">
    <h2 class="text-2xl font-bold mb-6 text-center">File Metadata</h2>

    <div class="overflow-x-auto">
      <table class="min-w-full border dark:border-gray-700">
        <thead>
          <tr class="bg-gray-200 dark:bg-gray-700 text-left">
            <th class="p-3">Filename</th>
            <th class="p-3">Upload Time</th>
            <th class="p-3">Size</th>
            <th class="p-3">Encrypted</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800">
          <?php while ($file = $result->fetch_assoc()): ?>
            <tr class="border-t dark:border-gray-700">
              <td class="p-3"><?= htmlspecialchars($file['filename']) ?></td>
              <td class="p-3"><?= htmlspecialchars($file['upload_time']) ?></td>
              <td class="p-3">
                <?php
                  $size = filesize($file['filepath']);
                  echo $size < 1024 * 1024 ? round($size / 1024, 2) . " KB" : round($size / (1024 * 1024), 2) . " MB";
                ?>
              </td>
              <td class="p-3">
                <?= $file['is_encrypted'] ? '✅' : '❌' ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-6 text-center">
      <a href="dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
    </div>
  </div>
</body>
</html>
