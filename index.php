<?php
session_start();
require_once "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["pending_user_id"] = $user_id;

            // Generate OTP
            $otp = rand(100000, 999999);
            $_SESSION["otp"] = $otp;

            // Send OTP (you must configure PHP mail)
            $to = $email;
            $subject = "Your OTP Code";
            $message = "Your OTP for login is: $otp";
            $headers = "From: no-reply@securefilemanager.com";

            mail($to, $subject, $message, $headers);

            header("Location: otp.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found.";
    }
}
?>
<!DOCTYPE html>
<html class="h-full bg-gray-50">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center">
  <div class="max-w-md w-full p-6 bg-white rounded-xl shadow-md space-y-6">
    <h2 class="text-2xl font-bold text-center">Login</h2>
    <?php if (isset($error)): ?>
      <p class="text-red-500 text-center"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
      <input type="email" name="email" placeholder="Email" required class="w-full border rounded px-3 py-2" />
      <input type="password" name="password" placeholder="Password" required class="w-full border rounded px-3 py-2" />
      <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Login</button>
      <p class="text-center text-sm">Don't have an account? <a href="signup.php" class="text-blue-600 hover:underline">Sign Up</a></p>
    </form>
  </div>
</body>
</html>
