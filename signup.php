<?php
session_start();
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $otp = rand(100000, 999999); // Generate 6-digit OTP

    // Check if email already exists
    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        $error = "Email already registered!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, otp, otp_verified) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("ssss", $username, $email, $password, $otp);

        if ($stmt->execute()) {
            // Save session for OTP verification
            $_SESSION["email"] = $email;
            $_SESSION["pending_user_id"] = $conn->insert_id;
            $_SESSION["otp"] = $otp;

            // Send OTP (email logic can be improved later)
            mail($email, "Your OTP Code", "Your OTP is: $otp");

            header("Location: otp.php");
            exit();
        } else {
            $error = "Signup failed!";
        }
    }
}
?>

<!-- HTML form -->
<!DOCTYPE html>
<html>
<head>
  <title>Sign Up</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <form method="POST" class="bg-white p-6 rounded shadow-md w-full max-w-sm space-y-4">
    <h2 class="text-2xl font-bold text-center">Sign Up</h2>
    <?php if (isset($error)) : ?>
      <p class="text-red-500"><?= $error ?></p>
    <?php endif; ?>
    <input type="text" name="username" placeholder="Username" required class="w-full px-3 py-2 border rounded" />
    <input type="email" name="email" placeholder="Email" required class="w-full px-3 py-2 border rounded" />
    <input type="password" name="password" placeholder="Password" required class="w-full px-3 py-2 border rounded" />
    <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Sign Up</button>
  </form>
</body>
</html>
