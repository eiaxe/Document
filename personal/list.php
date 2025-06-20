<?php
session_start();
// Correct the include path for db.php
// Assuming db.php is in the parent directory (document_tracking) relative to personal/list.php
require '../db.php'; // Use require instead of include for critical files

// Check if the database connection was established
if (!$conn) {
    // Handle connection error - log it or display a user-friendly message
    // Avoid exposing raw database errors to users in production
    $_SESSION['list_error'] = "Database connection failed.";
    // Optionally redirect or display an error page
    // header("Location: error.php"); // Example redirect
    // exit();
    // For now, we'll let the script continue and display the error later
    $db_connected = false; // Flag to indicate connection status
} else {
    $db_connected = true;
}


// Check if form was just submitted to show success message from add.php
$showSuccess = false;
if (isset($_SESSION['form_success'])) { // Changed from form_submitted to form_success
    $showSuccess = true;
    // You might want to display the actual success message from session
    $successMessage = htmlspecialchars($_SESSION['form_success']);
    unset($_SESSION['form_success']); // Clear the session variable
}

// Get search parameters from GET request - basic sanitization
$groupe = htmlspecialchars($_GET['destinataire'] ?? '');
$reference = htmlspecialchars($_GET['reference'] ?? ''); // Keep as string for input value
$objet = htmlspecialchars($_GET['objet'] ?? '');
$date_envoi_start = htmlspecialchars($_GET['date_envoi_start'] ?? '');
$date_envoi_end = htmlspecialchars($_GET['date_envoi_end'] ?? '');
$date_arrivee_start = htmlspecialchars($_GET['date_arrivee_start'] ?? '');
$date_arrivee_end = htmlspecialchars($_GET['date_arrivee_end'] ?? '');

// Initialize variables for distinct values
$destinataires = [];
$references = [];

// Get distinct values for dropdowns ONLY if the database is connected
if ($db_connected) {
    // *** CORRECTED TABLE NAME HERE ***
    $stmt_dest = $conn->prepare("SELECT DISTINCT groupe AS destinataire FROM personale ORDER BY groupe");
    if ($stmt_dest && $stmt_dest->execute()) {
        $result_dest = $stmt_dest->get_result();
        while($row = $result_dest->fetch_assoc()) {
            $destinataires[] = $row['destinataire'];
        }
        $stmt_dest->close();
    } else {
         $_SESSION['list_error'] = "Error fetching distinct groupes: " . ($stmt_dest ? $stmt_dest->error : $conn->error);
    }

    // *** CORRECTED TABLE NAME HERE ***
    $stmt_ref = $conn->prepare("SELECT DISTINCT numero_ordre AS reference_number FROM personale ORDER BY numero_ordre");
     if ($stmt_ref && $stmt_ref->execute()) {
        $result_ref = $stmt_ref->get_result();
        while($row = $result_ref->fetch_assoc()) {
            $references[] = $row['reference_number'];
        }
        $stmt_ref->close();
     } else {
         $_SESSION['list_error'] = "Error fetching distinct numero_ordres: " . ($stmt_ref ? $stmt_ref->error : $conn->error);
     }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal List</title>
    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Add the CSS from your add.php/edit.php here or link to a shared CSS file */
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
        }
         .container {
            max-width: 1400px; /* Wider container for the table */
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Header Styles - Keep consistent */
        header {
            background: var(--card-color);
            box-shadow: var(--box-shadow);
            position: sticky;
            top: 0;
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

        /* Dashboard Content */
        .dashboard-content {
            padding: 40px 20px;
            max-width: 1400px; /* Match container width */
            margin: 0 auto;
        }

         .dashboard-content h1 { /* Styling for the main heading */
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
            color: var(--primary-color);
         }

        .alert { /* Styling for alert messages */
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            font-size: 0.95rem;
             font-weight: 500;
             border: 1px solid transparent; /* Default border */
        }
        .alert-error { /* Specific style for error alerts */
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
         body.dark-mode .alert-error {
             background-color: #4a252c;
             color: #f5a9b8;
             border-color: #6a3a4a;
         }
         .alert-success { /* Specific style for success alerts */
             background-color: #d4edda;
             color: #155724;
             border-color: #c3e6cb;
         }
          body.dark-mode .alert-success {
              background-color: #1f3a2e;
              color: #a3e6b3;
              border-color: #284d38;
          }

        /* Add New Record Link */
        .add-new-link { /* Changed class name for clarity */
            display: inline-block;
            margin-bottom: 20px; /* Space below the button */
            padding: 10px 20px; /* Increased padding */
            background: var(--gradient); /* Use gradient */
            color: white;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: background 0.3s, transform 0.1s;
        }
        .add-new-link:hover {
             background: var(--button-hover-bg);
             transform: translateY(-2px);
        }

        /* Search Form */
        #searchForm {
            margin-bottom: 30px; /* More space below form */
            padding: 20px; /* Increased padding */
            background: var(--card-color); /* Use card background */
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow); /* Add shadow */
        }
        #searchForm label {
            display: block; /* Labels on their own line */
            margin-bottom: 5px; /* Space below label */
            font-weight: 600;
            color: var(--text-color);
        }
        #searchForm select, #searchForm input[type="text"], #searchForm input[type="date"] {
            padding: 10px; /* Increased padding */
            margin-bottom: 15px;
            border: 1px solid var(--input-border); /* Use input border variable */
            border-radius: var(--border-radius);
            width: 100%;
            box-sizing: border-box;
            font-size: 1rem;
            color: var(--text-color);
            background-color: var(--bg-color); /* Input background */
             transition: border-color 0.3s, box-shadow 0.3s;
        }
         body.dark-mode #searchForm select,
         body.dark-mode #searchForm input[type="text"],
         body.dark-mode #searchForm input[type="date"] {
             background-color: #2a2a2a; /* Darker input background */
             color: var(--text-color);
             border-color: var(--input-border);
         }

         #searchForm select:focus, #searchForm input[type="text"]:focus, #searchForm input[type="date"]:focus {
             outline: none;
             border-color: var(--input-focus-border);
             box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.1);
         }
         body.dark-mode #searchForm select:focus,
         body.dark-mode #searchForm input[type="text"]:focus,
         body.dark-mode #searchForm input[type="date"]:focus {
              box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
         }

        .search-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 10px; /* Space between rows */
        }
        .search-col {
            flex: 1;
            min-width: 220px; /* Slightly larger min-width */
        }

         .search-actions { /* Container for Search/Reset buttons */
             display: flex;
             gap: 15px;
             justify-content: flex-end; /* Align buttons to the right */
             margin-top: 20px;
         }

        #searchForm input[type="submit"],
         .search-actions a.btn { /* Style the Reset link as a button */
             padding: 10px 20px;
             border: none;
             border-radius: var(--border-radius);
             font-size: 1rem;
             cursor: pointer;
             transition: background 0.3s, transform 0.1s;
             text-decoration: none;
             display: inline-flex;
             align-items: center;
             gap: 5px;
             font-weight: 600;
         }

        #searchForm input[type="submit"] {
            background: var(--gradient);
            color: white;
        }
        #searchForm input[type="submit"]:hover {
            background: var(--button-hover-bg);
            transform: translateY(-2px);
        }

         .search-actions a.btn {
             background-color: #6c757d;
             color: white;
         }
          body.dark-mode .search-actions a.btn {
              background-color: #5a6268;
          }
         .search-actions a.btn:hover {
             background-color: #545b62;
             transform: translateY(-2px);
         }


        /* Table Styling */
        .data-table { /* Changed class name for clarity */
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: var(--card-color);
            border-radius: var(--border-radius);
            overflow: hidden; /* Ensures rounded corners on table */
            box-shadow: var(--box-shadow);
        }
        .data-table th, .data-table td {
            border: 1px solid var(--table-border); /* Use table border variable */
            padding: 12px 15px; /* Increased padding */
            text-align: left;
             word-break: break-word; /* Prevent long words from overflowing */
        }
        .data-table th {
            background-color: var(--table-header-bg); /* Use table header background */
            color: white; /* Header text color */
            font-weight: 600;
             position: sticky; /* Sticky header */
             top: var(--header-height); /* Stick below main header */
             z-index: 10; /* Ensure header is above table rows */
        }
        .data-table tbody tr:nth-child(even) {
            background-color: var(--bg-color); /* Alternating row color */
        }
         body.dark-mode .data-table tbody tr:nth-child(even) {
             background-color: #1a1a1a; /* Dark mode alternating color */
         }
        .data-table tbody tr:hover {
            background-color: var(--row-hover-bg); /* Hover effect */
        }

        .important-cell { /* Class for the important column */
            text-align: center; /* Center the star */
        }
        .important-star {
            color: var(--accent-color); /* Use accent color for star */
            font-weight: bold;
            font-size: 1.2em;
        }

        /* Action Links */
        .action-links a {
            margin-right: 10px; /* Space between links */
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
         .action-links a:last-child {
             margin-right: 0;
         }

        .action-links a.edit-link {
            color: var(--edit-color);
        }
         .action-links a.edit-link:hover {
             color: darken(var(--edit-color), 10%); /* Example hover color */
         }

        .action-links a.delete-link {
            color: var(--delete-color);
        }
         .action-links a.delete-link:hover {
             color: darken(var(--delete-color), 10%); /* Example hover color */
         }

         /* No records message */
         .data-table td[colspan] {
             text-align: center;
             font-style: italic;
             color: #666;
         }
          body.dark-mode .data-table td[colspan] {
              color: #bbb;
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
          @media (max-width: 768px) {
               .dashboard-content h1 {
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

               /* Stack search form columns */
               .search-row {
                   flex-direction: column;
                   gap: 15px;
               }
                .search-actions {
                    flex-direction: column;
                    gap: 10px;
                    align-items: stretch;
                }
                 .search-actions input[type="submit"],
                 .search-actions a.btn {
                     width: 100%;
                     text-align: center;
                 }

               /* Make table scrollable horizontally */
               .table-container { /* Add a container for the table */
                   overflow-x: auto;
                   -webkit-overflow-scrolling: touch; /* Enable smooth scrolling on touch devices */
               }
               .data-table {
                   width: 800px; /* Give table a minimum width to ensure scrollability */
               }

                .data-table th, .data-table td {
                   padding: 10px; /* Reduce padding */
                   font-size: 0.9rem;
                }
           }
           @media (max-width: 480px) {
                .dashboard-content {
                    padding: 20px 10px;
                }
                 .add-new-link {
                     width: 100%;
                     text-align: center;
                 }
                #searchForm {
                    padding: 15px;
                }
                 .data-table {
                     width: 700px; /* Further reduce min-width for smaller screens */
                 }
           }
    </style>
</head>
<body class=""> <!-- Add dark-mode class here if you want it on by default -->

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

    <div class="dashboard-content">
        <h1>Personal Records</h1>

        <?php if(isset($_SESSION['list_error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['list_error']) ?>
                <?php unset($_SESSION['list_error']); ?>
            </div>
        <?php endif; ?>

        <?php if ($showSuccess): ?>
            <div class="alert alert-success">
                <?= $successMessage ?? 'Record added successfully!' ?>
            </div>
        <?php endif; ?>

        <a href="addpersonal.php" class="add-new-link">Add New Record</a> <!-- Link to the add page -->

        <form id="searchForm" method="GET">
            <div class="search-row">
                <div class="search-col">
                    <label for="destinataire">Groupe Destinataire:</label>
                    <select name="destinataire" id="destinataire" class="form-control">
                        <option value="">-- All --</option>
                        <?php foreach($destinataires as $dest): ?>
                            <option value="<?= htmlspecialchars($dest) ?>" <?= $dest === $groupe ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dest) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="search-col">
                    <label for="reference">Numéro d'ordre:</label>
                    <select name="reference" id="reference" class="form-control">
                        <option value="">-- All --</option>
                        <?php foreach($references as $ref): ?>
                            <option value="<?= htmlspecialchars($ref) ?>" <?= $ref == $reference ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ref) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="search-col">
                    <label for="objet">Objet:</label>
                    <input type="text" id="objet" name="objet" value="<?= htmlspecialchars($objet) ?>" class="form-control">
                </div>
            </div>

            <div class="search-row">
                <div class="search-col">
                    <label for="date_envoi_start">Date d'envoi (start):</label>
                    <input type="date" id="date_envoi_start" name="date_envoi_start" value="<?= htmlspecialchars($date_envoi_start) ?>" class="form-control">
                </div>

                <div class="search-col">
                    <label for="date_envoi_end">Date d'envoi (end):</label>
                    <input type="date" id="date_envoi_end" name="date_envoi_end" value="<?= htmlspecialchars($date_envoi_end) ?>" class="form-control">
                </div>

                <div class="search-col">
                    <label for="date_arrivee_start">Date d'arrivée (start):</label>
                    <input type="date" id="date_arrivee_start" name="date_arrivee_start" value="<?= htmlspecialchars($date_arrivee_start) ?>" class="form-control">
                </div>

                <div class="search-col">
                    <label for="date_arrivee_end">Date d'arrivée (end):</label>
                    <input type="date" id="date_arrivee_end" name="date_arrivee_end" value="<?= htmlspecialchars($date_arrivee_end) ?>" class="form-control">
                </div>
            </div>

            <div class="search-actions">
                <input type="submit" value="Search" class="btn btn-primary">
                <a href="list.php" class="btn btn-secondary">Reset</a> <!-- Link to reset search -->
            </div>
        </form>

        <!-- Add a container for horizontal scrolling on small screens -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Group</th>
                        <th>Full Name</th>
                        <th>Province</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Send Date</th>
                        <th>Arrival Date</th>
                        <th>Subject</th>
                        <th>Division</th>
                        <th class="important-cell">Important</th>
                        <th>Actions</th> <!-- Added Actions column header -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Only attempt to fetch data if the database is connected
                    if ($db_connected) {
                        // Build the SQL query with filters
                        // *** CORRECTED TABLE NAME HERE ***
                        $sql = "SELECT * FROM personale WHERE 1=1";
                        $params = [];
                        $types = '';

                        if (!empty($groupe)) {
                            $sql .= " AND groupe = ?";
                            $params[] = $groupe;
                            $types .= 's';
                        }

                         // Ensure reference is treated as an integer if not empty
                        if (!empty($reference) || $reference === '0') { // Include '0' as a valid reference
                            $sql .= " AND numero_ordre = ?";
                            $params[] = (int)$reference; // Cast to int for binding
                            $types .= 'i';
                        }

                        if (!empty($objet)) {
                            $sql .= " AND objet LIKE ?";
                            $params[] = '%' . $objet . '%';
                            $types .= 's';
                        }

                        if (!empty($date_envoi_start)) {
                            $sql .= " AND date_envoi >= ?";
                            $params[] = $date_envoi_start;
                            $types .= 's';
                        }

                        if (!empty($date_envoi_end)) {
                            $sql .= " AND date_envoi <= ?";
                            $params[] = $date_envoi_end;
                            $types .= 's';
                        }

                        if (!empty($date_arrivee_start)) {
                            $sql .= " AND date_arrive >= ?";
                            $params[] = $date_arrivee_start;
                            $types .= 's';
                        }

                        if (!empty($date_arrivee_end)) {
                            $sql .= " AND date_arrive <= ?";
                            $params[] = $date_arrivee_end;
                            $types .= 's';
                        }

                        $sql .= " ORDER BY date_arrive DESC, numero_ordre DESC";

                        // Prepare and execute the query for the main data
                        $stmt_data = $conn->prepare($sql);

                         if ($stmt_data === false) {
                              echo "<tr><td colspan='12' class='alert alert-error'>Error preparing data query: " . $conn->error . "</td></tr>";
                         } else {
                            if (!empty($params)) {
                                 // Use call_user_func_array for bind_param with dynamic parameters
                                 // Prepend the statement object to the params array
                                 array_unshift($params, $types);
                                 call_user_func_array([$stmt_data, 'bind_param'], $params);
                            }

                            if ($stmt_data->execute()) {
                                $result = $stmt_data->get_result();

                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['numero_ordre']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['groupe']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['nom_complet']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['province']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['adresse']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['tel']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['date_envoi']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['date_arrive']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['objet']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['division']) . "</td>";
                                        echo "<td class='important-cell'>" . ($row['important'] ? '<span class="important-star">★</span>' : '') . "</td>";
                                        // Action Links
                                        echo "<td class='action-links'>";
                                        echo "<a href='edit.php?id=" . htmlspecialchars($row['id']) . "' class='edit-link'>✏️ Edit</a>";
                                        echo "<a href='delete.php?id=" . htmlspecialchars($row['id']) . "' class='delete-link' onclick=\"return confirm('Are you sure you want to delete this record?');\">🗑️ Delete</a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='12'>No records found</td></tr>";
                                }
                                $stmt_data->close();
                            } else {
                                 echo "<tr><td colspan='12' class='alert alert-error'>Error executing data query: " . $stmt_data->error . "</td></tr>";
                            }
                         }

                        // Close the database connection if it was opened
                        $conn->close();
                    } else {
                         // Display a message if DB connection failed
                         echo "<tr><td colspan='12'>Database connection failed. Cannot load data.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include the interactive widget if desired -->
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
                 <p id="morocco-fact">Did you know? Al Hoceima is known for its beautiful beaches and Rif Mountains.</p>
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