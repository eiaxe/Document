<?php
session_start(); // Start the session

// Correct the include path for db.php
// Assuming db.php is in the parent directory (document_tracking) relative to depart/list.php
require '../db.php'; // Use require for critical files

// Initialize variables for search filters and form values
$search_num_ordre = '';
$search_division = '';
$search_destinataire = '';
$search_date_from = '';
$search_date_to = '';
$search_objet = '';

// Initialize variables for query building
$where_clauses = [];
$params = [];
$param_types = '';

// Check if the database connection was established
if (!$conn) {
    // Handle connection error gracefully
    $error_message = "Database connection failed: " . mysqli_connect_error();
    $result = false; // Ensure $result is false if connection fails
} else {
    // --- Handle Search Form Submission ---
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
        // Retrieve and sanitize search form data
        $search_num_ordre = filter_input(INPUT_GET, 'num_ordre', FILTER_SANITIZE_NUMBER_INT);
        $search_division = filter_input(INPUT_GET, 'division', FILTER_SANITIZE_SPECIAL_CHARS);
        $search_destinataire = filter_input(INPUT_GET, 'destinataire', FILTER_SANITIZE_SPECIAL_CHARS);
        $search_date_from = filter_input(INPUT_GET, 'date_from', FILTER_SANITIZE_SPECIAL_CHARS);
        $search_date_to = filter_input(INPUT_GET, 'date_to', FILTER_SANITIZE_SPECIAL_CHARS);
        $search_objet = filter_input(INPUT_GET, 'objet', FILTER_SANITIZE_SPECIAL_CHARS);

        // Build the WHERE clause based on provided search terms
        if (!empty($search_num_ordre)) {
            $where_clauses[] = "num_ordre = ?";
            $params[] = $search_num_ordre;
            $param_types .= 'i'; // integer
        }
        if (!empty($search_division)) {
            $where_clauses[] = "division LIKE ?";
            $params[] = "%" . $search_division . "%";
            $param_types .= 's'; // string
        }
        if (!empty($search_destinataire)) {
            $where_clauses[] = "destinataire LIKE ?";
            $params[] = "%" . $search_destinataire . "%";
            $param_types .= 's'; // string
        }
         if (!empty($search_date_from)) {
             $where_clauses[] = "date_envoi >= ?";
             $params[] = $search_date_from;
             $param_types .= 's'; // string (date)
         }
         if (!empty($search_date_to)) {
             $where_clauses[] = "date_envoi <= ?";
             $params[] = $search_date_to;
             $param_types .= 's'; // string (date)
         }
        if (!empty($search_objet)) {
            $where_clauses[] = "objet LIKE ?";
            $params[] = "%" . $search_objet . "%";
            $param_types .= 's'; // string
        }

        // Construct the final query
        $query = "SELECT * FROM depart";
        if (!empty($where_clauses)) {
            $query .= " WHERE " . implode(" AND ", $where_clauses);
        }
        $query .= " ORDER BY num_ordre ASC"; // Always order results

        // Prepare and execute the statement
        $stmt = $conn->prepare($query);

         if ($stmt === false) {
             $error_message = "Error preparing search query: " . $conn->error;
             $result = false; // Ensure $result is false
         } else {
             if (!empty($params)) {
                 // Use call_user_func_array to bind parameters dynamically
                 $bind_params = [$param_types];
                 foreach ($params as $key => $value) {
                     $bind_params[] = &$params[$key]; // Pass parameters by reference
                 }
                 call_user_func_array([$stmt, 'bind_param'], $bind_params);
             }

             if ($stmt->execute()) {
                 $result = $stmt->get_result();
             } else {
                 $error_message = "Error executing search query: " . $stmt->error;
                 $result = false; // Ensure $result is false
             }
             $stmt->close();
         }

    } else {
        // --- Default Fetch (No Search) ---
        $query = "SELECT * FROM depart ORDER BY num_ordre ASC";
        $result = $conn->query($query);

        if (!$result) {
            $error_message = "Query Error: " . $conn->error;
            $result = false; // Ensure $result is false
        }
    }

     // --- Fetch Destinataire Options for the Search Filter ---
    $destinataire_options = [];
    $destinataire_query = $conn->prepare("SELECT DISTINCT destinataire FROM depart WHERE destinataire IS NOT NULL AND destinataire != '' ORDER BY destinataire");

    if ($destinataire_query === false) {
        $error_message = (isset($error_message) ? $error_message . "<br>" : "") . "Error fetching destinataire filter options: " . $conn->error;
    } else {
        if ($destinataire_query->execute()) {
            $destinataire_result = $destinataire_query->get_result();
            while ($row = $destinataire_result->fetch_assoc()) {
                $destinataire_options[] = $row['destinataire'];
            }
            $destinataire_query->close();
        } else {
             $error_message = (isset($error_message) ? $error_message . "<br>" : "") . "Error executing destinataire filter fetch: " . $destinataire_query->error;
             $destinataire_query->close(); // Close even on error
        }
    }

    // --- Fetch Division Options for the Search Filter ---
    $division_options = [];
    $division_query = $conn->prepare("SELECT DISTINCT division FROM depart WHERE division IS NOT NULL AND division != '' ORDER BY division");

    if ($division_query === false) {
         $error_message = (isset($error_message) ? $error_message . "<br>" : "") . "Error fetching division filter options: " . $conn->error;
    } else {
        if ($division_query->execute()) {
            $division_result = $division_query->get_result();
            while ($row = $division_result->fetch_assoc()) {
                $division_options[] = $row['division'];
            }
            $division_query->close();
        } else {
             $error_message = (isset($error_message) ? $error_message . "<br>" : "") . "Error executing division filter fetch: " . $division_query->error;
             $division_query->close(); // Close even on error
        }
    }
}

// Close the database connection if it was successfully opened and is still active
if (isset($conn) && $conn && $conn->ping()) {
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Depart Records</title>
    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Add the CSS from your add.php here or link to a shared CSS file */
        /* Ensure this CSS is complete and includes all styles from the previous examples */
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
            max-width: 1200px; /* Wider container for the table */
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Header Styles - Keep consistent */
        header {
            background: var(--card-color);
            box-shadow: var(--box-shadow);
            position: fixed; /* Changed to fixed for list page */
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

        /* Search Form Styling */
        .search-form {
            background: var(--search-bg);
            padding: 20px;
            margin-bottom: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .search-form h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: var(--text-color);
            text-align: center;
        }

        .search-form .form-row {
            display: flex;
            flex-wrap: wrap; /* Allow items to wrap on small screens */
            gap: 20px; /* Space between form groups */
            margin-bottom: 15px; /* Space below rows */
        }

         .search-form .form-group {
             flex: 1 1 200px; /* Allow flex item to grow, shrink, and have a base width of 200px */
             display: flex;
             flex-direction: column;
         }

        .search-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .search-form input[type="text"],
        .search-form input[type="number"],
        .search-form input[type="date"],
        .search-form select {
            width: 100%; /* Fill the flex container */
            padding: 10px;
            border: 1px solid var(--input-border);
            border-radius: var(--border-radius);
            box-sizing: border-box;
            font-size: 1rem;
            color: var(--text-color);
            background-color: var(--bg-color); /* Input background */
            transition: border-color 0.3s, box-shadow 0.3s;
        }
         body.dark-mode .search-form input[type="text"],
         body.dark-mode .search-form input[type="number"],
         body.dark-mode .search-form input[type="date"],
         body.dark-mode .search-form select {
             background-color: #1e1e1e; /* Darker input background */
             color: var(--text-color);
             border-color: var(--input-border);
         }

        .search-form input[type="text"]:focus,
        .search-form input[type="number"]:focus,
        .search-form input[type="date"]:focus,
        .search-form select:focus {
            outline: none;
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.1);
        }
         body.dark-mode .search-form input[type="text"]:focus,
         body.dark-mode .search-form input[type="number"]:focus,
         body.dark-mode .search-form input[type="date"]:focus,
         body.dark-mode .search-form select:focus {
              box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
         }

         .search-form .date-range-group {
             display: flex;
             gap: 10px; /* Space between date inputs */
         }

         .search-form .date-range-group .form-group {
             flex: 1; /* Allow each date input group to take half the space */
         }

         .search-form .search-buttons {
             display: flex;
             gap: 15px;
             justify-content: center;
             margin-top: 20px;
         }

         .search-form button {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s, transform 0.1s;
             font-weight: 600;
         }

         .search-form button[type="submit"] {
            background: var(--gradient);
            color: white;
         }
         .search-form button[type="submit"]:hover {
             background: var(--button-hover-bg);
             transform: translateY(-2px);
         }

         .search-form button[type="reset"] {
             background-color: #6c757d; /* Secondary button color */
             color: white;
         }
          body.dark-mode .search-form button[type="reset"] {
             background-color: #5a6268;
         }
         .search-form button[type="reset"]:hover {
             background-color: #545b62;
             transform: translateY(-2px);
         }


        /* Table Styling */
        .data-table {
            width: 100%;
            border-collapse: collapse; /* Remove space between borders */
            margin-top: 20px;
            box-shadow: var(--box-shadow);
            background: var(--card-color); /* Table background */
            border-radius: var(--border-radius); /* Apply border-radius to the table */
             overflow: hidden; /* Ensure border-radius works with collapsed border */
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px; /* Add padding */
            text-align: left;
            border-bottom: 1px solid var(--table-border); /* Add bottom border */
        }

        .data-table th {
            background-color: var(--table-header-bg);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

         .data-table tbody tr:last-child td {
             border-bottom: none; /* Remove bottom border from last row */
         }

        .data-table tbody tr:hover {
            background-color: var(--row-hover-bg);
             transition: background-color 0.2s;
        }

         .data-table td {
             vertical-align: top; /* Align cell content to the top */
             word-break: break-word; /* Break long words */
         }

         /* Optional: Style for Important column */
         .important-cell {
             text-align: center; /* Center the check/cross marks */
             font-size: 1.2rem; /* Make icons larger */
         }

        /* Actions Column Styling */
         .actions-cell {
             text-align: center;
             white-space: nowrap; /* Prevent wrapping action links */
         }

         .action-link {
             display: inline-block;
             margin: 0 5px;
             font-size: 1rem;
             color: var(--primary-color); /* Default link color */
             text-decoration: none;
             transition: color 0.2s;
         }

         .action-link:hover {
             color: var(--secondary-color); /* Hover color */
         }

         .action-link.edit-link {
             color: var(--edit-color); /* Edit color */
         }
         .action-link.edit-link:hover {
             color: #d39e00; /* Darker edit color on hover */
         }

         .action-link.delete-link {
             color: var(--delete-color); /* Delete color */
         }
         .action-link.delete-link:hover {
             color: #c82333; /* Darker delete color on hover */
         }


        /* Add/Action Link Styling */
         .add-link {
             display: inline-flex; /* Align icon and text */
             align-items: center;
             gap: 8px; /* Space between icon and text */
             margin-top: 20px;
             padding: 10px 20px;
             background: var(--gradient);
             color: white;
             text-decoration: none;
             border-radius: var(--border-radius);
             font-weight: 600;
             transition: background 0.3s, transform 0.1s;
         }
         .add-link:hover {
             background: var(--button-hover-bg);
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

         /* Responsive */
         @media (max-width: 992px) {
             .search-form .form-row {
                 gap: 15px;
             }
             .search-form .form-group {
                 flex: 1 1 180px; /* Adjust base width for slightly smaller screens */
             }
         }

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

              .search-form .form-row {
                 flex-direction: column; /* Stack form groups on smaller screens */
                 gap: 10px;
                 margin-bottom: 0;
             }
             .search-form .form-group {
                 flex: unset; /* Remove flex properties when stacked */
                 width: 100%; /* Make stacked items full width */
             }
              .search-form .date-range-group {
                  flex-direction: column; /* Stack date inputs */
                  gap: 10px;
              }
              .search-form .search-buttons {
                  flex-direction: column;
                  gap: 10px;
              }
               .search-form button {
                   width: 100%; /* Make buttons full width when stacked */
               }
         }
         @media (max-width: 480px) {
              .main-content {
                  padding: 20px 10px;
              }
              .search-form {
                  padding: 15px;
              }
         }

         /* Add horizontal scrolling for small screens */
         .table-responsive {
             overflow-x: auto;
             -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
         }

         .data-table th,
         .data-table td {
             min-width: 100px; /* Ensure minimum width for columns on small screens */
             /* white-space: nowrap; /* Prevent wrapping in cells - Re-enabled for most cells */
         }
          .data-table th:nth-child(4), /* Objet header */
          .data-table th:nth-child(7), /* Observations header */
          .data-table td:nth-child(4), /* Objet column */
          .data-table td:nth-child(7) { /* Observations column */
              white-space: normal; /* Allow wrapping for long text fields */
              min-width: 180px; /* Give more space to these columns */
          }
          .data-table th:nth-child(1), /* Num d'ordre */
          .data-table th:nth-child(8), /* Important */
          .data-table td:nth-child(1), /* Num d'ordre */
          .data-table td:nth-child(8) { /* Important */
              white-space: nowrap; /* Prevent wrapping for these */
              min-width: unset; /* Remove min-width for these */
          }
           /* Ensure Actions column has enough width */
           .data-table th:last-child,
           .data-table td:last-child {
               min-width: 100px; /* Adjust as needed */
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
        <h1>List of Depart Records</h1>

        <?php
        // Display error message if connection or query failed
        if (isset($error_message)) {
            echo '<div class="alert alert-error">' . htmlspecialchars($error_message) . '</div>';
        }
         // Display success message if set from edit/delete
         if (isset($_SESSION['success_message'])) {
             echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
             unset($_SESSION['success_message']); // Clear the message after displaying
         }
          if (isset($_SESSION['error_message'])) {
              echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
              unset($_SESSION['error_message']); // Clear the message after displaying
          }
        ?>

        <!-- Search Form -->
        <div class="search-form">
            <h3>Search Records</h3>
            <form method="get" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search_num_ordre">Num D'Ordre:</label>
                        <input type="number" id="search_num_ordre" name="num_ordre" value="<?= htmlspecialchars($search_num_ordre) ?>">
                    </div>
                     <div class="form-group">
                         <label for="search_division">Division:</label>
                         <select id="search_division" name="division">
                             <option value="">-- All Divisions --</option>
                             <?php
                             // Populate division options from the fetched data
                             foreach ($division_options as $option) {
                                 $selected = ($search_division === $option) ? 'selected' : '';
                                 echo "<option value=\"" . htmlspecialchars($option) . "\" $selected>" . htmlspecialchars($option) . "</option>";
                             }
                             ?>
                         </select>
                     </div>
                    <div class="form-group">
                        <label for="search_destinataire">Destinataire:</label>
                        <select id="search_destinataire" name="destinataire">
                            <option value="">-- All Destinataires --</option>
                            <?php
                            // Populate destinataire options from the fetched data
                            foreach ($destinataire_options as $option) {
                                $selected = ($search_destinataire === $option) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($option) . "\" $selected>" . htmlspecialchars($option) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Removed the Signataire input field -->
                     <div class="form-group date-range-group">
                         <div class="form-group">
                              <label for="search_date_from">Envoyé entre le:</label>
                              <input type="date" id="search_date_from" name="date_from" value="<?= htmlspecialchars($search_date_from) ?>">
                         </div>
                         <div class="form-group">
                             <label for="search_date_to">et le:</label>
                             <input type="date" id="search_date_to" name="date_to" value="<?= htmlspecialchars($search_date_to) ?>">
                         </div>
                    </div>
                </div>

                <div class="form-row">
                     <div class="form-group" style="flex: 1 1 100%;">
                         <label for="search_objet">Objet:</label>
                         <input type="text" id="search_objet" name="objet" value="<?= htmlspecialchars($search_objet) ?>">
                     </div>
                </div>


                <div class="search-buttons">
                    <button type="submit" name="search"><i class="fas fa-search"></i> Search</button>
                     <button type="reset" onclick="window.location.href='list.php'"><i class="fas fa-sync-alt"></i> Reset</button> <!-- Reset button -->
                </div>
            </form>
        </div>


        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Num d'ordre</th>
                        <th>Date d'envoi</th>
                        <th>Destinataire</th>
                        <th>Objet</th>
                        <th>Division</th>
                        <th>Responsable</th>
                        <th>Observations</th>
                        <th>Important</th>
                        <th>Actions</th> <!-- New column for actions -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if $result is valid before looping
                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['num_ordre']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_envoi']); ?></td>
                        <td><?php echo htmlspecialchars($row['destinataire']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['objet'])); ?></td> <!-- Use nl2br for newlines in textarea -->
                        <td><?php echo htmlspecialchars($row['division']); ?></td>
                        <td><?php echo htmlspecialchars($row['responsable']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['observations'])); ?></td> <!-- Use nl2br for newlines -->
                        <td class="important-cell"><?php echo $row['important'] ? '✅' : '❌'; ?></td>
                         <td class="actions-cell">
                             <!-- Edit Link -->
                             <a href="edit.php?num_ordre=<?php echo urlencode($row['num_ordre']); ?>" class="action-link edit-link" title="Edit Record">
    <i class="fas fa-edit"></i>
</a>
                             <!-- Delete Link -->
                             <a href="delete.php?num_ordre=<?php echo urlencode($row['num_ordre']); ?>" class="action-link delete-link" title="Delete Record" onclick="return confirm('Are you sure you want to delete this record (Num d\'ordre: <?php echo htmlspecialchars($row['num_ordre']); ?>)?');">
                                 <i class="fas fa-trash-alt"></i>
                             </a>
                         </td>
                    </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px;">No records found matching your criteria.</td> <!-- colspan increased -->
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add/Action Link -->
        <p style="text-align: center; margin-top: 30px;">
            <a href="add.php" class="add-link">
                <i class="fas fa-plus-circle"></i> Add New Record
            </a>
        </p>

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

    <script>
         // Your JavaScript for dark mode and time/facts goes here
          document.addEventListener('DOMContentLoaded', function() {
              initDarkMode();
              initTimeAndFacts();
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
    </script>
</body>
</html>