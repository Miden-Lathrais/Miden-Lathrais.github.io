<?php
require_once 'db_config.php'; // Include database configuration

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        die("Please fill in all fields.");
    }

    // Prepare statement to check if the user exists and fetch their hashed password
    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        die("User not found.");
    }

    // Bind result
    $stmt->bind_result($id, $stored_password_hash);

    // Fetch result
    $stmt->fetch();

    // Verify the password
    if (password_verify($password, $stored_password_hash)) {
        // If login is successful, redirect to frontend.html
        header("Location: frontend.html"); // Redirect to your frontend page
        exit; // Ensure the script stops after the redirect
    } else {
        die("Incorrect password.");
    }

    $stmt->close();
}

$conn->close();
?>
