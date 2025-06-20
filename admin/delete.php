<?php
session_start(); // Start the session
include '../db.php'; // Correct path to db.php

// Add a quick check for the connection for debugging
if (!$conn) {
    // If connection failed, set an error and redirect
    $_SESSION['error'] = "Database connection failed in delete.php: " . mysqli_connect_error();
    header("Location: list.php");
    exit();
}

// Check if the ID is set in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare the DELETE statement
    $stmt = $conn->prepare("DELETE FROM submissions WHERE id = ?");

    // Check if the prepare was successful
    if ($stmt === false) {
        $_SESSION['error'] = "Database query preparation failed: " . $conn->error;
    } else {
        $stmt->bind_param("i", $id); // Bind the ID parameter

        // Execute the statement
        if ($stmt->execute()) {
            // Check if any row was affected (meaning the record existed and was deleted)
            if ($stmt->affected_rows > 0) {
                $_SESSION['message'] = "Record deleted successfully.";
            } else {
                $_SESSION['error'] = "No record found with ID: " . htmlspecialchars($id);
            }

        } else {
            $_SESSION['error'] = "Error deleting record: " . $stmt->error;
        }

        $stmt->close(); // Close the statement
    }

} else {
    // If no ID is provided, set an error message
    $_SESSION['error'] = "No record ID provided.";
}

// Redirect back to the list page
header("Location: list.php");
exit();
?>