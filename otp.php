<?php
session_start();
include 'includes/db.php'; // DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = $_POST["otp"];
    $email = $_SESSION["email"] ?? null;

    if ($email) {
        $stmt = $conn->prepare("SELECT otp FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($entered_otp == $row["otp"]) {
                // Mark user as verified
                $_SESSION["user_id"] = $_SESSION["pending_user_id"];
                unset($_SESSION["otp"]);
                unset($_SESSION["pending_user_id"]);

                // Update OTP verification in DB
                $update = $conn->prepare("UPDATE users SET otp_verified = 1 WHERE email = ?");
                $update->bind_param("s", $email);
                $update->execute();

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid OTP!";
            }
        } else {
            $error = "User not found.";
        }
    } else {
        $error = "Session expired. Please login again.";
    }
}
?>

<!DOCTYPE html>
<html class="h-full bg-gray-50">
<head>
  <meta charset="UTF-8">
  <title>Enter OTP</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center">
  <div class="max-w-md w-full p-6 bg-white rounded-xl shadow-md space-y-6">
    <h2 class="text-2xl font-bold text-center">Enter OTP</h2>
    <?php if (isset($error)) : ?>
      <p class="text-red-500 text-center"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
      <input type="text" name="otp" placeholder="6-digit OTP" required class="w-full border rounded px-3 py-2" />
      <button type="submit" class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600">Verify OTP</button>
    </form>
  </div>
</body>
</html>
