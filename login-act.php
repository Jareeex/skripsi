<?php
session_start();
include 'include/conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password']; // Tidak di-hash, karena di DB masih plaintext

    $stmt = $db->prepare("SELECT * FROM saw_users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['username'] = $username;
        $_SESSION['status'] = "login";

        $stmt->close();
        $db->close();
        header("Location: index.php");
        exit();
    } else {
        header("Location: login.php?error=wrong_password");
        exit();
    }
}
?>
