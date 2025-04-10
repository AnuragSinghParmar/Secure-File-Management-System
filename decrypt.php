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
    $input_password = $_POST["password"];

    // Fetch file from DB
    $stmt = $conn->prepare("SELECT * FROM files WHERE id = ? AND user_id = ? AND is_encrypted = 1");
    $stmt->bind_param("ii", $file_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();

    if ($file) {
        $encrypted_path = $file['filepath'];
        $original_filename = $file['filename'];
        $saved_password = $file['encryption_password'];

        if ($input_password === $saved_password) {
            $encrypted_data = file_get_contents($encrypted_path);
            $key = hash("sha256", $input_password);
            $decrypted_data = openssl_decrypt($encrypted_data, "AES-256-CBC", $key, 0, substr($key, 0, 16));

            // Restore original file
            $new_path = "uploads/" . time() . "_" . $original_filename;
            file_put_contents($new_path, $decrypted_data);

            // Update DB
            $stmt = $conn->prepare("UPDATE files SET filepath = ?, is_encrypted = 0, encryption_password = NULL WHERE id = ?");
            $stmt->bind_param("si", $new_path, $file_id);
            $stmt->execute();

            // Delete encrypted file
            unlink($encrypted_path);

            $success = "File decrypted successfully!";
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "File not found or already decrypted.";
    }
}

// Get encrypted files
$stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ? AND is_encrypted = 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$files = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">
<head>
  <meta charset="UTF-8">
  <title>Decrypt File</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col items-center justify-center py-10 px-4">
  <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow w-full max-w-lg">
    <h2 class="text-2xl font-bold mb-4 text-center">Decrypt File</h2>

    <?php if ($success): ?>
      <p class="text-green-600 text-center"><?= $success ?></p>
    <?php elseif ($error): ?>
      <p class="text-red-600 text-center"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <label>Select Encrypted File:</label>
      <select name="file_id" required class="w-full p-2 rounded border dark:bg-gray-700">
        <?php while ($row = $files->fetch_assoc()): ?>
          <option value="<?= $row['id'] ?>"><?= $row['filename'] ?></option>
        <?php endwhile; ?>
      </select>

      <input type="password" name="password" placeholder="Enter decryption password" required class="w-full p-2 rounded border dark:bg-gray-700" />

      <button type="submit" class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600">Decrypt</button>
    </form>

    <div class="mt-6 text-center">
      <a href="dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
    </div>
  </div>
</body>
</html>
