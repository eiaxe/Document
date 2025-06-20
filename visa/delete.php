<?php
// MUST BE AT VERY TOP - NO WHITESPACE BEFORE
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require '../db.php'; // Make sure this path is correct relative to delete.php

// Check if ID exists
if (!isset($_GET['id'])) {
    $_SESSION['form_error'] = "No record ID specified for deletion.";
    // Redirect directly to list.php
    header("Location: list.php");
    exit();
}

$id = (int)$_GET['id'];

// Prepare and execute the delete statement
if ($conn) {
    $stmt = $conn->prepare("DELETE FROM visa WHERE id = ?");

    if ($stmt === false) {
        $_SESSION['form_error'] = "Database delete query preparation failed: " . $conn->error;
    } else {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Check if any row was actually deleted
            if ($stmt->affected_rows > 0) {
                 $_SESSION['form_success'] = "Record deleted successfully.";
            } else {
                 $_SESSION['form_error'] = "No record found with ID " . htmlspecialchars($id) . " to delete.";
            }
        } else {
            $_SESSION['form_error'] = "Error deleting record: " . $stmt->error;
        }
         $stmt->close();
    }
     // Close the database connection
     $conn->close();
} else {
    $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Database connection failed for deletion.";
}


// Redirect back to the list.php page after processing
header("Location: list.php");
exit(); // Ensure script stops execution after redirect
?>