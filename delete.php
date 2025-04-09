<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $file_id = $_POST["file_id"];
    $user_id = $_SESSION["user_id"];

    // Verify file belongs to user
    $stmt = $conn->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $file_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();

    if ($file) {
        $filepath = $file['filepath'];

        // Delete file from server
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        // Delete record from DB
        $deleteStmt = $conn->prepare("DELETE FROM files WHERE id = ?");
        $deleteStmt->bind_param("i", $file_id);
        $deleteStmt->execute();

        $_SESSION["success"] = "File deleted successfully.";
    } else {
        $_SESSION["error"] = "File not found or access denied.";
    }
}

header("Location: dashboard.php");
exit();
