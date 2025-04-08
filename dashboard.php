<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$files = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <script>
  tailwind.config = {
    darkMode: 'class',
  }
</script>
<script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="min-h-screen flex flex-col items-center py-10 px-4">

  <!-- Header -->
  <div class="w-full max-w-4xl mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold">Secure File Manager</h1>
    
<button id="theme-toggle" class="fixed top-4 right-4 z-50 px-4 py-2 bg-gray-300 dark:bg-gray-700 rounded shadow text-black dark:text-white transition duration-300">
  🌗 Toggle Mode
</button>

  </div>

  <!-- Dashboard Options -->
  <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 w-full max-w-4xl mb-10">
    <a href="upload.php" class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-xl text-center shadow">📤 Upload File</a>
    <a href="encrypt.php" class="bg-yellow-500 hover:bg-yellow-600 text-white p-4 rounded-xl text-center shadow">🔒 Encrypt File</a>
    <a href="decrypt.php" class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-xl text-center shadow">🔓 Decrypt File</a>
    <a href="preview.php" class="bg-indigo-500 hover:bg-indigo-600 text-white p-4 rounded-xl text-center shadow">👁️ Preview File</a>
    <a href="metadata.php" class="bg-pink-500 hover:bg-pink-600 text-white p-4 rounded-xl text-center shadow">🧾 File Metadata</a>
    <a href="share.php" class="bg-teal-500 hover:bg-teal-600 text-white p-4 rounded-xl text-center shadow">🔗 Share File</a>
  </div>

  <!-- Delete File Section -->
  <div class="bg-white dark:bg-gray-800 w-full max-w-2xl p-6 rounded-xl shadow">
    <h2 class="text-xl font-semibold mb-4 text-center">🗑️ Delete a File</h2>
    <form action="delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this file?');" class="space-y-4">
      <select name="file_id" required class="w-full p-2 rounded border dark:bg-gray-700">
        <option value="">Select file to delete</option>
        <?php while ($row = $files->fetch_assoc()): ?>
          <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['filename']) ?></option>
        <?php endwhile; ?>
      </select>
      <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded">Delete File</button>
    </form>
  </div>

  <!-- Logout -->
  <div class="mt-10">
    <a href="logout.php" class="text-sm text-red-500 hover:underline">Logout</a>
  </div>

  <!-- Include Theme Toggle Script -->
  <script src="assets/js/theme-toggle.js"></script>
</body>
</html>
