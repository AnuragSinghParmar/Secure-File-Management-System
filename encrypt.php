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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $file_id = $_POST["file_id"];
    $password = $_POST["password"];

    // Fetch file
    $stmt = $conn->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $file_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();

    if ($file && !$file['is_encrypted']) {
        $original_path = $file['filepath'];
        $encrypted_path = $original_path . ".enc";

        $data = file_get_contents($original_path);
        $key = hash("sha256", $password);
        $encrypted_data = openssl_encrypt($data, "AES-256-CBC", $key, 0, substr($key, 0, 16));
        file_put_contents($encrypted_path, $encrypted_data);

        // Hide original
        unlink($original_path);

        // Update DB
        $stmt = $conn->prepare("UPDATE files SET filepath = ?, is_encrypted = 1, encryption_password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $encrypted_path, $password, $file_id);
        $stmt->execute();

        $success = "File encrypted successfully!";
    } else {
        $error = "Invalid file or already encrypted.";
    }
}

// Fetch user files
$stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$files = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">
<head>
  <meta charset="UTF-8">
  <title>Encrypt File</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col items-center justify-center py-10 px-4">
  <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow w-full max-w-lg">
    <h2 class="text-2xl font-bold mb-4 text-center">Encrypt a File</h2>

    <?php if ($success): ?>
      <p class="text-green-600 text-center"><?= $success ?></p>
    <?php elseif ($error): ?>
      <p class="text-red-600 text-center"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <label>Select File:</label>
      <select name="file_id" required class="w-full p-2 rounded border dark:bg-gray-700">
        <?php while ($row = $files->fetch_assoc()): ?>
          <option value="<?= $row['id'] ?>" <?= $row['is_encrypted'] ? 'disabled' : '' ?>>
            <?= $row['filename'] ?> <?= $row['is_encrypted'] ? '(Encrypted)' : '' ?>
          </option>
        <?php endwhile; ?>
      </select>

      <input type="password" name="password" placeholder="New password" required class="w-full p-2 rounded border dark:bg-gray-700" />

      <button type="submit" class="w-full bg-yellow-500 text-white py-2 rounded hover:bg-yellow-600">Encrypt</button>
    </form>

    <div class="mt-6 text-center">
      <a href="dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
    </div>
  </div>
</body>
</html>
