<?php
session_start(); // Start the session for messages

// Include the database connection file
require '../db.php'; // Assuming db.php is in the parent directory

// Check if the database connection was established
if (!$conn) {
    $_SESSION['error_message'] = "Database connection failed: " . mysqli_connect_error();
    // Consider redirecting or showing an error page if DB connection fails
    // header("Location: error_page.php"); exit();
}

$num_ordre = null; // Initialize num_ordre
$record_data = null; // Initialize variable to hold fetched record data
$error_message = '';
$success_message = '';

// --- Handle GET request (display form with existing data) ---
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Check if num_ordre is provided in the URL
    if (isset($_GET['num_ordre']) && !empty($_GET['num_ordre'])) {
        // Sanitize the input num_ordre
        $num_ordre = filter_input(INPUT_GET, 'num_ordre', FILTER_SANITIZE_NUMBER_INT);

        // Prepare and execute query to fetch the record
        $stmt = $conn->prepare("SELECT * FROM depart WHERE num_ordre = ?");
        if ($stmt === false) {
            $error_message = "Error preparing fetch query: " . $conn->error;
        } else {
            $stmt->bind_param('i', $num_ordre); // 'i' for integer
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows === 1) {
                    // Fetch the record data
                    $record_data = $result->fetch_assoc();
                } else {
                    // Record not found
                    $error_message = "Record with Num d'ordre " . htmlspecialchars($num_ordre) . " not found.";
                }
            } else {
                $error_message = "Error executing fetch query: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        // No num_ordre provided in GET request
        $error_message = "No record specified for editing.";
    }
}

// --- Handle POST request (process form submission) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize submitted data
    $num_ordre = filter_input(INPUT_POST, 'num_ordre', FILTER_SANITIZE_NUMBER_INT);
    $date_envoi = filter_input(INPUT_POST, 'date_envoi', FILTER_SANITIZE_SPECIAL_CHARS);
    $destinataire = filter_input(INPUT_POST, 'destinataire', FILTER_SANITIZE_SPECIAL_CHARS);
    $objet = filter_input(INPUT_POST, 'objet', FILTER_SANITIZE_SPECIAL_CHARS);
    $division = filter_input(INPUT_POST, 'division', FILTER_SANITIZE_SPECIAL_CHARS);
    $responsable = filter_input(INPUT_POST, 'responsable', FILTER_SANITIZE_SPECIAL_CHARS);
    $observations = filter_input(INPUT_POST, 'observations', FILTER_SANITIZE_SPECIAL_CHARS);
    $important = isset($_POST['important']) ? 1 : 0; // Checkbox value (1 if checked, 0 if not)

    // Basic Validation (add more robust validation as needed)
    if (empty($num_ordre) || empty($date_envoi) || empty($destinataire) || empty($objet) || empty($division) || empty($responsable)) {
        $error_message = "All fields except Observations are required.";
        // To repopulate the form with submitted (but invalid) data on error:
        $record_data = [
            'num_ordre' => $num_ordre,
            'date_envoi' => $date_envoi,
            'destinataire' => $destinataire,
            'objet' => $objet,
            'division' => $division,
            'responsable' => $responsable,
            'observations' => $observations,
            'important' => $important
        ];
    } else {
        // Data is valid, proceed with update
        // Use the original num_ordre from the hidden field for the WHERE clause
        $original_num_ordre = filter_input(INPUT_POST, 'original_num_ordre', FILTER_SANITIZE_NUMBER_INT);

         // Check if the num_ordre was changed and if the new num_ordre already exists
         if ($num_ordre != $original_num_ordre) {
             $check_stmt = $conn->prepare("SELECT num_ordre FROM depart WHERE num_ordre = ?");
             if ($check_stmt === false) {
                 $error_message = "Error preparing check query: " . $conn->error;
             } else {
                 $check_stmt->bind_param('i', $num_ordre);
                 $check_stmt->execute();
                 $check_result = $check_stmt->get_result();
                 if ($check_result->num_rows > 0) {
                     $error_message = "Error: Num d'ordre " . htmlspecialchars($num_ordre) . " already exists.";
                     // Repopulate form with submitted data on error
                     $record_data = [
                         'num_ordre' => $num_ordre,
                         'date_envoi' => $date_envoi,
                         'destinataire' => $destinataire,
                         'objet' => $objet,
                         'division' => $division,
                         'responsable' => $responsable,
                         'observations' => $observations,
                         'important' => $important
                     ];
                 }
                 $check_stmt->close();
             }
         }

        // Only proceed with update if no validation errors occurred
        if (empty($error_message)) {
            // Prepare the UPDATE statement
            $stmt = $conn->prepare("UPDATE depart SET num_ordre = ?, date_envoi = ?, destinataire = ?, objet = ?, division = ?, responsable = ?, observations = ?, important = ? WHERE num_ordre = ?");

            if ($stmt === false) {
                $error_message = "Error preparing update query: " . $conn->error;
            } else {
                // Bind parameters
                // 'i' for integer, 's' for string
                $stmt->bind_param('issssssii', $num_ordre, $date_envoi, $destinataire, $objet, $division, $responsable, $observations, $important, $original_num_ordre);

                // Execute the statement
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $_SESSION['success_message'] = "Record updated successfully!";
                    } else {
                         // This might happen if no data was actually changed, or if the original_num_ordre wasn't found (less likely if fetched correctly)
                         $_SESSION['success_message'] = "Record updated successfully (no changes made)."; // Or a different message
                    }
                    // Redirect back to the list page
                    header("Location: list.php");
                    exit();
                } else {
                    $error_message = "Error executing update query: " . $stmt->error;
                     // Repopulate form with submitted data on error
                     $record_data = [
                         'num_ordre' => $num_ordre,
                         'date_envoi' => $date_envoi,
                         'destinataire' => $destinataire,
                         'objet' => $objet,
                         'division' => $division,
                         'responsable' => $responsable,
                         'observations' => $observations,
                         'important' => $important
                     ];
                }
                $stmt->close();
            }
        }
    }
}

// Fetch Organization Options for the dropdown (from organizations table)
$organization_options = [];
if ($conn) { // Only try to fetch if connection is valid
    // Use prepared statement with LIMIT for large dataset
    $org_query = $conn->prepare("SELECT id, division_fr FROM organizations ORDER BY division_fr LIMIT 500");
    if ($org_query === false) {
         $error_message .= (empty($error_message) ? "" : "<br>") . "Error fetching organization options: " . $conn->error;
    } else {
        if ($org_query->execute()) {
            $org_result = $org_query->get_result();
            while ($row = $org_result->fetch_assoc()) {
                $organization_options[$row['id']] = $row['division_fr'];
            }
             $org_result->free(); // Free result set memory
        } else {
             $error_message .= (empty($error_message) ? "" : "<br>") . "Error executing organization options fetch: " . $org_query->error;
        }
        $org_query->close();
    }
}

// Fetch Division Options for the dropdown (from organizations table)
$division_options = [];
if ($conn) { // Only try to fetch if connection is valid
    // Use DISTINCT and LIMIT for large dataset
    $division_query = $conn->prepare("SELECT DISTINCT division_fr FROM organizations WHERE division_fr IS NOT NULL AND division_fr != '' ORDER BY division_fr LIMIT 500");
     if ($division_query === false) {
          $error_message .= (empty($error_message) ? "" : "<br>") . "Error fetching division options: " . $conn->error;
     } else {
        if ($division_query->execute()) {
            $division_result = $division_query->get_result();
            while ($row = $division_result->fetch_assoc()) {
                $division_options[] = $row['division_fr'];
            }
             $division_result->free(); // Free result set memory
        } else {
             $error_message .= (empty($error_message) ? "" : "<br>") . "Error executing division options fetch: " . $division_query->error;
        }
        $division_query->close();
     }
}

// Close the database connection if it was successfully opened and is still active
if (isset($conn) && $conn && $conn->ping()) {
    $conn->close();
}

// If there was an error fetching the record initially (GET request)
// or if there was a validation/DB error on POST, display an error page
// and prevent showing the form.
$display_form = ($record_data !== null && empty($error_message));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Depart Record</title>
     <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Include Select2 for better dropdowns with search -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
         /* Re-use the CSS from list.php */
         :root {
            --bg-color: #f5f7fa;
            --text-color: #333;
            --card-color: #fff;
            --primary-color: #2575fc;
            --secondary-color: #6a11cb;
            --accent-color: #ff6a00;
            --gradient: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            --header-height: 70px;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            --input-border: #ccc;
            --input-focus-border: var(--primary-color);
            --button-bg: var(--gradient);
            --button-hover-bg: linear-gradient(135deg, #5a32a3, #3a85f7);
            --table-header-bg: var(--primary-color);
            --table-border: #ddd;
            --row-hover-bg: #f1f1f1;
            --delete-color: #dc3545; /* Red for delete */
            --edit-color: #ffc107; /* Yellow for edit */
            --error-color: #dc3545; /* Red for errors */
             --success-color: #28a745; /* Green for success */
              --search-bg: #e9ecef; /* Light background for search form */
         }
        body.dark-mode {
            --bg-color: #121212;
            --text-color: #f0f0f0;
            --card-color: #1e1e1e;
            --primary-color: #4a90e2;
            --secondary-color: #8a2be2;
            --box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            --input-border: #555;
            --input-focus-border: var(--primary-color);
            --button-bg: linear-gradient(135deg, var(--secondary-color), var(--primary-color)); /* Adjusted for dark mode */
            --button-hover-bg: linear-gradient(135deg, #9b4ee4, #5a9ce6);
            --table-header-bg: #2a60a0; /* Darker header */
            --table-border: #444;
            --row-hover-bg: #2a2a2a;
            --delete-color: #e74c3c; /* Darker red */
            --edit-color: #f39c12; /* Darker yellow */
             --error-color: #e74c3c; /* Darker red */
             --success-color: #2ecc71; /* Darker green */
             --search-bg: #2c2c2c; /* Darker background for search form */
         }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            transition: background 0.4s, color 0.4s;
            line-height: 1.6;
            min-height: 100vh;
            padding-bottom: 100px; /* Add padding for the fixed widget/footer */
             padding-top: var(--header-height); /* Add padding for the sticky header */
        }
         .container {
            max-width: 800px; /* Adjusted max-width for the form */
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Header Styles - Keep consistent */
        header {
            background: var(--card-color);
            box-shadow: var(--box-shadow);
            position: fixed; /* Changed to fixed */
            top: 0;
            left: 0;
            right: 0;
            width: 100%; /* Ensure it spans full width */
            z-index: 100;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: var(--header-height);
            padding: 0 20px;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.2rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .logo img {
            height: 30px; /* Make sure you have a logo image and adjust path if needed */
        }
        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--gradient);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.3s;
            font-size: 1.1rem; /* Adjust icon size */
        }
        .btn-icon:hover {
            transform: scale(1.1);
        }

        /* Main Content */
        .main-content {
            padding: 40px 20px;
        }

         .main-content h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
            color: var(--primary-color);
         }

         /* Alert Messages */
         .alert {
             padding: 15px;
             margin-bottom: 20px;
             border-radius: var(--border-radius);
             font-size: 0.95rem;
             font-weight: 500;
             border: 1px solid transparent;
         }
         .alert-error {
             background-color: #f8d7da;
             color: #721c24;
             border-color: #f5c6cb;
         }
          body.dark-mode .alert-error {
              background-color: #4a252c;
              color: #f5a9b8;
              border-color: #6a3a4a;
          }
         .alert-success {
              background-color: #d4edda;
              color: #155724;
              border-color: #c3e6cb;
         }
          body.dark-mode .alert-success {
              background-color: #1f3a2e;
              color: #a3e6b3;
              border-color: #284d38;
          }

        /* Form Styling (Re-used and adapted from add.php/list.php search form) */
        .data-form {
            background: var(--card-color);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .data-form .form-group {
            margin-bottom: 20px;
        }

        .data-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 1rem;
        }

         .data-form input[type="text"],
         .data-form input[type="number"],
         .data-form input[type="date"],
         .data-form select,
         .data-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--input-border);
            border-radius: var(--border-radius);
            box-sizing: border-box;
            font-size: 1rem;
            color: var(--text-color);
            background-color: var(--bg-color); /* Input background */
            transition: border-color 0.3s, box-shadow 0.3s;
         }
         body.dark-mode .data-form input[type="text"],
         body.dark-mode .data-form input[type="number"],
         body.dark-mode .data-form input[type="date"],
         body.dark-mode .data-form select,
         body.dark-mode .data-form textarea {
             background-color: #1e1e1e; /* Darker input background */
             color: var(--text-color);
             border-color: var(--input-border);
         }

         .data-form input[type="text"]:focus,
         .data-form input[type="number"]:focus,
         .data-form input[type="date"]:focus,
         .data-form select:focus,
         .data-form textarea:focus {
            outline: none;
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.1);
         }
          body.dark-mode .data-form input[type="text"]:focus,
          body.dark-mode .data-form input[type="number"]:focus,
          body.dark-mode .data-form input[type="date"]:focus,
          body.dark-mode .data-form select:focus,
          body.dark-mode .data-form textarea:focus {
               box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
          }

        .data-form textarea {
            resize: vertical; /* Allow vertical resizing */
            min-height: 100px; /* Minimum height for textarea */
        }

        .data-form .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .data-form .checkbox-group input[type="checkbox"] {
             width: auto; /* Don't make checkbox full width */
             margin-right: 5px;
        }

        .data-form .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .data-form button {
            padding: 12px 25px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s, transform 0.1s;
            font-weight: 600;
        }

        .data-form button[type="submit"] {
            background: var(--gradient);
            color: white;
        }
        .data-form button[type="submit"]:hover {
            background: var(--button-hover-bg);
            transform: translateY(-2px);
        }

        .data-form .cancel-link {
            display: inline-flex; /* Align icon and text */
            align-items: center;
            gap: 8px; /* Space between icon and text */
            padding: 12px 25px;
            background-color: #6c757d; /* Secondary button color */
            color: white;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: background 0.3s, transform 0.1s;
        }
         body.dark-mode .data-form .cancel-link {
             background-color: #5a6268;
         }
        .data-form .cancel-link:hover {
            background-color: #545b62;
            transform: translateY(-2px);
        }


        /* Interactive Widget (Footer-like positioning) - Keep consistent */
         .interactive-widget {
             background: var(--card-color);
             padding: 20px 0;
             box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
             text-align: center;
             position: fixed; /* Fixed position to stay at bottom */
             bottom: 0; /* Align to bottom */
             left: 0;
             right: 0;
             width: 100%;
             z-index: 99; /* Ensure it's above main content but below header */
         }

         .widget-container {
             max-width: 800px;
             margin: 0 auto;
             padding: 0 20px;
         }

         .weather-time {
             display: flex;
             justify-content: center;
             align-items: center;
             gap: 30px;
             margin-bottom: 20px;
         }

         .time-display {
             text-align: center;
         }

         #current-time {
             font-size: 2rem;
             font-weight: 700;
             background: var(--gradient);
             -webkit-background-clip: text;
             -webkit-text-fill-color: transparent;
         }

         #current-date {
             font-size: 0.9rem;
             opacity: 0.8;
         }

         .weather-animation {
             position: relative;
             width: 80px;
             height: 80px;
         }

         .sun {
             position: absolute;
             width: 40px;
             height: 40px;
             background: #FFD700;
             border-radius: 50%;
             box-shadow: 0 0 20px #FFD700;
             top: 5px;
             left: 20px;
             animation: pulse 3s infinite alternate;
         }

         .cloud {
             position: absolute;
             width: 50px;
             height: 20px;
             background: #FFF;
             border-radius: 20px;
             top: 30px;
             left: 5px;
             animation: move 10s linear infinite;
         }

         .cloud:before, .cloud:after {
             content: '';
             position: absolute;
             background: #FFF;
             border-radius: 50%;
         }

         .cloud:before {
             width: 20px;
             height: 20px;
             top: -10px;
             left: 10px;
         }

         .cloud:after {
             width: 15px;
             height: 15px;
             top: -8px;
             right: 10px;
         }

         .fun-fact {
             font-size: 0.8rem;
             padding: 8px;
             background: rgba(106, 17, 203, 0.1);
             border-radius: var(--border-radius);
             max-width: 600px;
             margin: 0 auto;
         }
          body.dark-mode .fun-fact {
              background: rgba(138, 43, 226, 0.15);
          }


         @keyframes pulse {
             0% { transform: scale(1); }
             100% { transform: scale(1.1); }
         }

         @keyframes move {
             0% { transform: translateX(0); opacity: 1; }
             50% { transform: translateX(30px); opacity: 0.8; }
             100% { transform: translateX(0); opacity: 1; }
         }

         /* Select2 Customization */
         .select2-container--default .select2-selection--single {
             height: 46px;
             border: 1px solid var(--input-border);
             border-radius: var(--border-radius);
             background-color: var(--bg-color);
         }
         body.dark-mode .select2-container--default .select2-selection--single {
             background-color: #1e1e1e;
             border-color: var(--input-border);
         }
         .select2-container--default .select2-selection--single .select2-selection__rendered {
             color: var(--text-color);
             line-height: 44px;
         }
         .select2-container--default .select2-selection--single .select2-selection__arrow {
             height: 44px;
         }
         .select2-container--default .select2-results__option--highlighted[aria-selected] {
             background-color: var(--primary-color);
         }
         .select2-container--default .select2-search--dropdown .select2-search__field {
             border: 1px solid var(--input-border);
             background-color: var(--bg-color);
             color: var(--text-color);
         }
         body.dark-mode .select2-container--default .select2-search--dropdown .select2-search__field {
             background-color: #1e1e1e;
         }
         .select2-dropdown {
             border: 1px solid var(--input-border);
             background-color: var(--card-color);
         }
         body.dark-mode .select2-dropdown {
             background-color: var(--card-color);
         }
         .select2-container--default .select2-results__option[aria-selected=true] {
             background-color: var(--row-hover-bg);
         }

         /* Responsive */
         @media (max-width: 768px) {
              .main-content h1 {
                  font-size: 2rem;
              }
              .weather-time {
                  gap: 15px;
              }
              #current-time {
                  font-size: 1.5rem;
              }
              .weather-animation {
                  width: 60px;
                  height: 60px;
              }
              .sun {
                  width: 30px;
                  height: 30px;
                  top: 5px;
                  left: 15px;
              }
              .cloud {
                  width: 40px;
                  top: 25px;
                  left: 5px;
              }
              .cloud:before {
                  width: 15px;
                  height: 15px;
                  top: -8px;
                  left: 8px;
              }
               .cloud:after {
                  width: 12px;
                  height: 12px;
                  top: -6px;
                  right: 8px;
               }
              .data-form .button-group {
                  flex-direction: column;
                  gap: 10px;
              }
               .data-form button,
               .data-form .cancel-link {
                   width: 100%; /* Make buttons full width when stacked */
                   text-align: center; /* Center text in buttons */
               }
         }
         @media (max-width: 480px) {
              .main-content {
                  padding: 20px 10px;
              }
              .data-form {
                  padding: 20px;
              }
         }

    </style>
</head>
<body>
<header>
<div class="header-content container">
    <div class="logo">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2c/Flag_of_Morocco.svg/1200px-Flag_of_Morocco.svg.png" alt="Morocco Flag" />
        <span>Al Hoceima Employee Portal</span>
    </div>
    <div style="display:flex; align-items:center; gap:10px;">
        <!-- Dark Mode Toggle -->
        <button class="btn-icon" id="modeToggle"><i class="fas fa-moon"></i></button>
        <!-- House Button to go back to index.html -->
        <a href="../index.html" class="btn-icon" title="Back to Home" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">
            <i class="fas fa-home"></i>
        </a>
    </div>
</div>
</header>

    <div class="main-content container">
        <h1>Edit Depart Record</h1>

        <?php
        // Display error message if any
        if (!empty($error_message)) {
            echo '<div class="alert alert-error">' . htmlspecialchars($error_message) . '</div>';
        }
        ?>

        <?php if ($display_form): ?>
            <div class="data-form">
                <form action="edit.php" method="post">
                    <!-- Hidden field to pass the original num_ordre -->
                    <input type="hidden" name="original_num_ordre" value="<?= htmlspecialchars($record_data['num_ordre']) ?>">

                    <div class="form-group">
                        <label for="num_ordre">Num D'Ordre:</label>
                        <input type="number" id="num_ordre" name="num_ordre" value="<?= htmlspecialchars($record_data['num_ordre']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="date_envoi">Date d'envoi:</label>
                        <input type="date" id="date_envoi" name="date_envoi" value="<?= htmlspecialchars($record_data['date_envoi']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="destinataire">Destinataire:</label>
                        <select id="destinataire" name="destinataire" class="select2-dropdown" required>
                             <option value="">Select Organization</option>
                             <?php
                             // Populate options from the organizations table
                             foreach ($organization_options as $id => $division_fr) {
                                 $selected = ($record_data['destinataire'] == $division_fr) ? 'selected' : '';
                                 echo "<option value=\"" . htmlspecialchars($division_fr) . "\" $selected>" . htmlspecialchars($division_fr) . "</option>";
                             }
                             ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="objet">Objet:</label>
                        <textarea id="objet" name="objet" rows="4" required><?= htmlspecialchars($record_data['objet']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="division">Division:</label>
                         <select id="division" name="division" class="select2-dropdown" required>
                             <option value="">Select Division</option>
                             <?php
                             // Populate options from the organizations table
                             foreach ($division_options as $option) {
                                 $selected = ($record_data['division'] === $option) ? 'selected' : '';
                                 echo "<option value=\"" . htmlspecialchars($option) . "\" $selected>" . htmlspecialchars($option) . "</option>";
                             }
                             ?>
                         </select>
                    </div>

                    <div class="form-group">
                        <label for="responsable">Responsable:</label>
                        <input type="text" id="responsable" name="responsable" value="<?= htmlspecialchars($record_data['responsable']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="observations">Observations:</label>
                        <textarea id="observations" name="observations" rows="4"><?= htmlspecialchars($record_data['observations']) ?></textarea>
                    </div>

                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="important" name="important" value="1" <?= $record_data['important'] ? 'checked' : '' ?>>
                        <label for="important">Important</label>
                    </div>

                    <div class="button-group">
                        <button type="submit"><i class="fas fa-save"></i> Update Record</button>
                        <a href="list.php" class="cancel-link"><i class="fas fa-times-circle"></i> Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- If display_form is false, it means there was an error fetching the record -->
             <p style="text-align: center; margin-top: 30px;">
                 <a href="list.php" class="cancel-link"><i class="fas fa-arrow-circle-left"></i> Back to List</a>
             </p>
        <?php endif; ?>

    </div>

     <!-- Include the interactive widget -->
     <div class="interactive-widget">
          <div class="widget-container">
              <div class="weather-time">
                  <div class="time-display">
                      <div id="current-time"></div>
                      <div id="current-date"></div>
                  </div>
                  <div class="weather-animation">
                      <div class="sun"></div>
                      <div class="cloud"></div>
                  </div>
              </div>
              <div class="fun-fact">
                  <p id="morocco-fact">Did you know? Al Hoceima is part of the Rif region, known for its Berber culture.</p>
              </div>
          </div>
      </div>

    <!-- Include jQuery and Select2 for enhanced dropdowns -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
         // Your JavaScript for dark mode and time/facts goes here
          document.addEventListener('DOMContentLoaded', function() {
              initDarkMode();
              initTimeAndFacts();
              initSelect2Dropdowns(); // Initialize Select2 dropdowns
          });

          function initDarkMode() {
              const modeToggle = document.getElementById('modeToggle');

              // Check for saved mode preference
              const savedMode = localStorage.getItem('alhoceima_darkMode');

              if (savedMode === 'enabled') {
                  document.body.classList.add('dark-mode');
                  const icon = modeToggle.querySelector('i');
                  if (icon.classList.contains('fa-moon')) {
                       icon.classList.replace('fa-moon', 'fa-sun');
                  }
              } else {
                   const icon = modeToggle.querySelector('i');
                   if (icon.classList.contains('fa-sun')) {
                        icon.classList.replace('fa-sun', 'fa-moon');
                   }
              }

              // Toggle dark mode
              modeToggle.addEventListener('click', () => {
                  document.body.classList.toggle('dark-mode');
                  const icon = modeToggle.querySelector('i');

                  if (document.body.classList.contains('dark-mode')) {
                      icon.classList.replace('fa-moon', 'fa-sun');
                      localStorage.setItem('alhoceima_darkMode', 'enabled');
                  } else {
                      icon.classList.replace('fa-sun', 'fa-moon');
                      localStorage.setItem('alhoceima_darkMode', 'disabled');
                  }
              });
          }

          function initTimeAndFacts() {
              const facts = [
                  "Al Hoceima is part of the Rif region, known for its Berber culture.",
                  "The Al Hoceima National Park is a biodiversity hotspot.",
                  "The city was rebuilt after a major earthquake in 2004.",
                  "Al Hoceima's beaches are among the most beautiful in Morocco.",
                  "The local cuisine features seafood and traditional Berber dishes."
              ];

              function updateTime() {
                  const now = new Date();
                  const hours = now.getHours().toString().padStart(2, '0');
                  const minutes = now.getMinutes().toString().padStart(2, '0');
                  const seconds = now.getSeconds().toString().padStart(2, '0');

                  document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds}`;

                  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                  document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', options);
              }

              function showRandomFact() {
                  const randomFact = facts[Math.floor(Math.random() * facts.length)];
                  document.getElementById('morocco-fact').textContent = 'Did you know? ' + randomFact;
              }

              // Initialize and update every second
              updateTime();
              setInterval(updateTime, 1000);

              // Change fact every 10 seconds
              showRandomFact();
              setInterval(showRandomFact, 10000);
          }

          function initSelect2Dropdowns() {
              // Initialize Select2 for both dropdowns
              $('.select2-dropdown').select2({
                  width: '100%',
                  placeholder: $(this).data('placeholder'),
                  allowClear: true,
                  theme: 'default'
              });

              // Update dark mode for Select2 when toggled
              document.getElementById('modeToggle').addEventListener('click', function() {
                  setTimeout(function() {
                      $('.select2-dropdown').select2({
                          theme: document.body.classList.contains('dark-mode') ? 'dark' : 'default'
                      });
                  }, 100);
              });
          }
    </script>
</body>
</html>