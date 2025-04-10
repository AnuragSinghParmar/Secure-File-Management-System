<?php
session_start();
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user["password"])) {
            // Generate new OTP on login
            $otp = rand(100000, 999999);
            $update = $conn->prepare("UPDATE users SET otp = ?, otp_verified = 0 WHERE email = ?");
            $update->bind_param("ss", $otp, $email);
            $update->execute();

            // Store in session for verification
            $_SESSION["email"] = $email;
            $_SESSION["pending_user_id"] = $user["id"];
            $_SESSION["otp"] = $otp;

            // Send OTP
            mail($email, "Your Login OTP", "Your OTP is: $otp");

            header("Location: otp.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!-- HTML form -->
<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <form method="POST" class="bg-white p-6 rounded shadow-md w-full max-w-sm space-y-4">
    <h2 class="text-2xl font-bold text-center">Login</h2>
    <?php if (isset($error)) : ?>
      <p class="text-red-500"><?= $error ?></p>
    <?php endif; ?>
    <input type="email" name="email" placeholder="Email" required class="w-full px-3 py-2 border rounded" />
    <input type="password" name="password" placeholder="Password" required class="w-full px-3 py-2 border rounded" />
    <button type="submit" class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600">Login</button>
  </form>
</body>
</html>
