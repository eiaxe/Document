<?php
session_start(); // Start the session to use session variables for messages

// Correct the include path for db.php
// Assuming db.php is in the parent directory (document_tracking) relative to personal/deletepersonal.php
require '../db.php'; // Use require instead of include for critical files

// Check if the database connection was established
if (!$conn) {
    // Handle connection error - log it or display a user-friendly message
    $_SESSION['list_error'] = "Database connection failed. Cannot delete record.";
    header("Location: list.php"); // Redirect back to the list page
    exit(); // Stop script execution
}


if (isset($_GET['id'])) {
    // Sanitize the input ID to ensure it's an integer
    $id = intval($_GET['id']);

    // Use prepared statement for security
    // *** CORRECTED TABLE NAME HERE ***
    $stmt = $conn->prepare("DELETE FROM personale WHERE id = ?");

    // Check if the statement preparation was successful
    if ($stmt === false) {
        $_SESSION['list_error'] = "Error preparing delete query: " . $conn->error;
    } else {
        // Bind the parameter and execute the statement
        if ($stmt->bind_param("i", $id) === false) {
             $_SESSION['list_error'] = "Error binding delete parameter: " . $stmt->error;
        } elseif ($stmt->execute()) {
            // Check if any rows were affected (meaning a record was deleted)
            if ($stmt->affected_rows > 0) {
                 $_SESSION['form_success'] = "Record deleted successfully!"; // Use form_success for consistency with add/edit
            } else {
                 $_SESSION['list_error'] = "No record found with ID: " . htmlspecialchars($id);
            }
        } else {
            // Error during execution
            $_SESSION['list_error'] = "Error executing delete query: " . $stmt->error;
        }
        // Close the statement
        $stmt->close();
    }

} else {
    // If no ID was provided in the GET request
    $_SESSION['list_error'] = "No record ID provided for deletion.";
}

// Close the database connection if it was opened and is still active
if ($conn && $conn->ping()) {
    $conn->close();
}

// Redirect back to the list page
header("Location: list.php");
exit;
?>