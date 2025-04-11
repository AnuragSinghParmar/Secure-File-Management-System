<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$upload_success = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $file = $_FILES["file"];
    $targetDir = "uploads/";
    $filename = basename($file["name"]);
    $targetFile = $targetDir . time() . "_" . $filename;

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        // Save file details to DB
        $stmt = $conn->prepare("INSERT INTO files (user_id, filename, filepath) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $filename, $targetFile);
        $stmt->execute();

        $upload_success = true;
    } else {
        $error = "Failed to upload file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">
<head>
  <meta charset="UTF-8">
  <title>Upload File</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col items-center justify-center py-10 px-4">
  <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow w-full max-w-md">
    <h2 class="text-2xl font-bold mb-4 text-center">Upload File</h2>
    <button id="theme-toggle" class="fixed top-4 right-4 z-50 px-4 py-2 bg-gray-300 dark:bg-gray-700 rounded shadow text-black dark:text-white transition duration-300">
    Toggle Mode
    </button>


    <?php if ($upload_success): ?>
      <p class="text-green-600 text-center">✅ File uploaded successfully!</p>
    <?php elseif ($error): ?>
      <p class="text-red-600 text-center"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="file" name="file" required class="w-full border p-2 rounded bg-white dark:bg-gray-700" />
      <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Upload</button>
    </form>

    <div class="mt-6 text-center">
      <a href="dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
    </div>
  </div>
  <script src="assets/js/theme-toggle.js"></script>
</body>
</html>
