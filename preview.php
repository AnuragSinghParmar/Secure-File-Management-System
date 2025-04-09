<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$preview_content = "";
$preview_type = "";
$error = "";

// On form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $file_id = $_POST["file_id"];

    // Get file info
    $stmt = $conn->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $file_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();

    if ($file) {
        if ($file["is_encrypted"]) {
            $error = "This file is encrypted. Please decrypt it first.";
        } else {
            $filepath = $file["filepath"];
            if (file_exists($filepath)) {
                $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
                $allowedImages = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $allowedText = ['txt', 'md', 'csv', 'html', 'php'];
                $allowedPDFs = ['pdf'];
                $allowedVideos = ['mp4', 'webm', 'ogg'];

                if (in_array($ext, $allowedImages)) {
                    $preview_type = "image";
                    $preview_content = $filepath;
                } elseif (in_array($ext, $allowedText)) {
                    $preview_type = "text";
                    $preview_content = htmlspecialchars(file_get_contents($filepath));
                } elseif (in_array($ext, $allowedPDFs)) {
                    $preview_type = "pdf";
                    $preview_content = $filepath;
                } elseif (in_array($ext, $allowedVideos)) {
                    $preview_type = "video";
                    $preview_content = $filepath;
                } else {
                    $error = "Preview not supported for this file type.";
                }
            } else {
                $error = "File not found on server.";
            }
        }
    } else {
        $error = "Invalid file selection.";
    }
}

// Get all files for current user
$stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$files = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">
<head>
  <meta charset="UTF-8">
  <title>Preview File</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col items-center justify-center py-10 px-4">
  <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow w-full max-w-2xl">
    <h2 class="text-2xl font-bold mb-4 text-center">Preview File</h2>

    <?php if ($error): ?>
      <p class="text-red-600 text-center"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4 mb-6">
      <label>Select a File:</label>
      <select name="file_id" required class="w-full p-2 rounded border dark:bg-gray-700">
        <?php while ($row = $files->fetch_assoc()): ?>
          <option value="<?= $row['id'] ?>"><?= $row['filename'] ?> <?= $row['is_encrypted'] ? '(Encrypted)' : '' ?></option>
        <?php endwhile; ?>
      </select>
      <button type="submit" class="w-full bg-indigo-500 text-white py-2 rounded hover:bg-indigo-600">Preview</button>
    </form>

    <?php if ($preview_content && $preview_type === "text"): ?>
      <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded max-h-96 overflow-y-auto border dark:border-gray-600">
        <pre><?= $preview_content ?></pre>
      </div>
    <?php elseif ($preview_content && $preview_type === "image"): ?>
      <div class="flex justify-center">
        <img src="<?= $preview_content ?>" alt="Image Preview" class="max-w-full max-h-96 rounded shadow" />
      </div>
    <?php elseif ($preview_content && $preview_type === "pdf"): ?>
      <div class="aspect-video w-full h-[500px]">
        <iframe src="<?= $preview_content ?>" class="w-full h-full rounded border dark:border-gray-600"></iframe>
      </div>
    <?php elseif ($preview_content && $preview_type === "video"): ?>
      <div class="flex justify-center">
        <video controls class="w-full max-h-96 rounded shadow">
          <source src="<?= $preview_content ?>" type="video/<?= pathinfo($preview_content, PATHINFO_EXTENSION) ?>">
          Your browser does not support the video tag.
        </video>
      </div>
    <?php endif; ?>

    <div class="mt-6 text-center">
      <a href="dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
    </div>
  </div>
</body>
</html>
