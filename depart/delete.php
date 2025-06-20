<?php
session_start(); // Start the session

require '../db.php'; // Include your database connection

// Check if num_ordre is provided in the URL
if (isset($_GET['num_ordre']) && !empty($_GET['num_ordre'])) {
    $num_ordre_to_delete = filter_input(INPUT_GET, 'num_ordre', FILTER_SANITIZE_NUMBER_INT);

    // Check if the database connection was established
    if ($conn) {
        // Prepare the delete statement
        $stmt = $conn->prepare("DELETE FROM depart WHERE num_ordre = ?");

        if ($stmt) {
            $stmt->bind_param("i", $num_ordre_to_delete);

            // Execute the statement
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $_SESSION['success_message'] = "Record (Num d'ordre: " . htmlspecialchars($num_ordre_to_delete) . ") deleted successfully.";
                } else {
                    $_SESSION['error_message'] = "Record with Num d'ordre " . htmlspecialchars($num_ordre_to_delete) . " not found or could not be deleted.";
                }
            } else {
                $_SESSION['error_message'] = "Error deleting record: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Error preparing delete query: " . $conn->error;
        }
    } else {
         $_SESSION['error_message'] = "Database connection failed.";
    }

} else {
    $_SESSION['error_message'] = "No record specified for deletion.";
}

// Close the database connection if it was successfully opened and is still active
if (isset($conn) && $conn && $conn->ping()) {
    $conn->close();
}

// Redirect back to the list page
header("Location: list.php");
exit(); // Stop script execution
?>