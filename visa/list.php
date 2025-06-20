<?php
// MUST BE AT VERY TOP - NO WHITESPACE BEFORE
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require '../db.php'; // Make sure this path is correct

// Get current section/action - keep these if list.php is included in dashboard_berau.php
// If list.php is standalone, these might be redundant for filtering the *data*,
// but could be used for navigation/highlighting in a dashboard menu.
// For the core search logic, we rely on $_GET parameters directly.
$section = $_GET['section'] ?? 'visa';
$action = $_GET['action'] ?? 'list';

// Search Filter
$conditions = [];
$params = [];
$types = '';

// IMPORTANT: Ensure your database connection ($conn) is valid before preparing statements

// Check if connection is valid before proceeding with database operations
if (!$conn) {
     $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Database connection failed.";
     // We can't query the database, so set result to null and skip query execution
     $result = null;
     // Skip fetching dropdown data if connection failed
     $departements_origine_list = [];
     $departements_cible_list = [];
     $divisions_list = [];

} else {
    // Process search parameters if connection is successful

    // num_ordre (assuming INT)
    if (!empty($_GET['num_ordre'])) {
        $conditions[] = 'num_ordre = ?';
        $params[] = $_GET['num_ordre'];
        $types .= 'i';
    }

    // reference (assuming INT)
    if (!empty($_GET['reference'])) {
        $conditions[] = 'reference = ?';
        $params[] = $_GET['reference'];
        $types .= 'i';
    }

    // Use LIKE for text fields to allow partial matches
    // departement_origine (assuming VARCHAR/TEXT)
    if (!empty($_GET['departement_origine'])) {
        $conditions[] = 'departement_origine LIKE ?';
        $params[] = '%'.$_GET['departement_origine'].'%';
        $types .= 's';
    }

    // departement_cible (assuming VARCHAR/TEXT)
    if (!empty($_GET['departement_cible'])) {
        $conditions[] = 'departement_cible LIKE ?';
        $params[] = '%'.$_GET['departement_cible'].'%';
        $types .= 's';
    }

    // division (assuming VARCHAR/TEXT)
    if (!empty($_GET['division'])) {
        $conditions[] = 'division LIKE ?';
        $params[] = '%'.$_GET['division'].'%';
        $types .= 's';
    }

    // objet (assuming VARCHAR/TEXT)
    if (!empty($_GET['objet'])) {
        $conditions[] = 'objet LIKE ?';
        $params[] = '%'.$_GET['objet'].'%';
        $types .= 's';
    }

    // Construct the main query
    $query = "SELECT * FROM visa";
    if ($conditions) {
        $query .= " WHERE " . implode(' AND ', $conditions);
    }
    $query .= " ORDER BY id DESC"; // Add default ordering

    $result = null; // Initialize result variable

    $stmt = $conn->prepare($query);

    if ($stmt === false) {
         $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Database query preparation failed: " . $conn->error;
    } else {
        if ($params) {
            // call_user_func_array requires parameters to be passed by reference
            // We need to create references for the parameters array
            $params_ref = [];
            foreach ($params as $key => $val) {
                $params_ref[$key] = &$params[$key];
            }

            if (call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params_ref)) === false) {
                 $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Database bind_param failed: " . $stmt->error;
            } else {
                if ($stmt->execute()) {
                     $result = $stmt->get_result();
                } else {
                     $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Database query execution failed: " . $stmt->error;
                }
            }
        } else {
            // No parameters to bind, just execute
             if ($stmt->execute()) {
                 $result = $stmt->get_result();
             } else {
                  $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Database query execution failed: " . $stmt->error;
             }
        }
         $stmt->close();
    }

    // Get unique values for dropdowns for the search form
    $departements_origine_list = [];
    $departements_cible_list = [];
    $divisions_list = [];

    // Using prepared statements for DISTINCT queries is safer, although less critical here
    // as user input isn't directly in the query string.
    // Let's stick to your current approach for simplicity unless you need the extra security.
    $departements_origine_res = $conn->query("SELECT DISTINCT departement_origine FROM visa WHERE departement_origine IS NOT NULL AND departement_origine != '' ORDER BY departement_origine ASC");
    if ($departements_origine_res) {
         while($row = $departements_origine_res->fetch_assoc()) $departements_origine_list[] = $row['departement_origine'];
         $departements_origine_res->free();
    }

    $departements_cible_res = $conn->query("SELECT DISTINCT departement_cible FROM visa WHERE departement_cible IS NOT NULL AND departement_cible != '' ORDER BY departement_cible ASC");
     if ($departements_cible_res) {
        while($row = $departements_cible_res->fetch_assoc()) $departements_cible_list[] = $row['departement_cible'];
        $departements_cible_res->free();
     }

    $divisions_res = $conn->query("SELECT DISTINCT division FROM visa WHERE division IS NOT NULL AND division != '' ORDER BY division ASC");
    if ($divisions_res) {
         while($row = $divisions_res->fetch_assoc()) $divisions_list[] = $row['division'];
         $divisions_res->free();
    }

} // End of if ($conn) block

// Close the database connection (optional but good practice)
// Only close if the script hasn't exited yet and connection is valid
if ($conn && $conn->ping()) {
    $conn->close();
}

// --- HTML Display ---
// (The HTML part remains largely the same, ensuring form element names match $_GET keys)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Visa Records List | Al Hoceima Employee Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- Paste the CSS from the list.php file here --- */
        /* Ensure all the styles including dark mode, form-row, form-group, buttons, alerts, etc. are included */
        /* Copy the entire <style> block from the list.php file */
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
             max-width: 1200px; /* Wider container for the table */
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

         /* Main Content Adaptation */
         .dashboard-content { /* Used the class from your provided code */
             padding: 40px 20px; /* Add padding */
             max-width: 1200px; /* Adjusted max-width for table */
             margin: 0 auto; /* Center the content */
         }

          .dashboard-content h2 { /* Styling for the main heading */
             text-align: center;
             margin-bottom: 30px;
             font-size: 2rem;
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

         /* Search Form Styling */
         .search-form-container {
             background: var(--card-color);
             border-radius: var(--border-radius);
             box-shadow: var(--box-shadow);
             padding: 20px;
             margin-bottom: 30px;
         }

         #visaSearchForm > div { /* Styling for the rows of form groups */
             display: flex;
             gap: 20px;
             margin-bottom: 20px; /* Space between rows */
             flex-wrap: wrap; /* Allow wrapping on smaller screens */
         }
          #visaSearchForm > div:last-child {
              margin-bottom: 0; /* No margin after the last row (buttons) */
              justify-content: flex-end; /* Align buttons to the right */
          }

         .form-group {
             flex: 1; /* Each form group takes equal space */
             min-width: 150px; /* Minimum width before wrapping */
             margin-bottom: 0; /* Remove default margin bottom */
         }

         .form-group label {
             display: block; /* Label on its own line */
             margin-bottom: 8px; /* Space between label and input */
             font-weight: 600; /* Make labels bold */
             color: var(--text-color);
         }

         .form-control {
             width: 100%; /* Full width of its container */
             padding: 10px;
             border: 1px solid var(--input-border);
             border-radius: var(--border-radius);
             font-size: 1rem;
             color: var(--text-color);
             background-color: var(--bg-color); /* Input background */
             transition: border-color 0.3s, box-shadow 0.3s;
         }
          body.dark-mode .form-control {
              background-color: #2a2a2a; /* Darker input background */
              color: var(--text-color);
              border-color: var(--input-border);
          }

         .form-control:focus {
             outline: none; /* Remove default outline */
             border-color: var(--input-focus-border); /* Highlight on focus */
             box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.1); /* Add a subtle glow */
         }
          body.dark-mode .form-control:focus {
               box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
          }

         /* Button Styling */
         .btn { /* General button style */
             padding: 10px 20px;
             border: none;
             border-radius: var(--border-radius);
             font-size: 1rem;
             cursor: pointer;
             transition: background 0.3s, transform 0.1s;
             text-decoration: none; /* For anchor tags styled as buttons */
             display: inline-flex; /* Align icon and text if any */
             align-items: center;
             gap: 5px;
         }

         .btn-primary { /* Primary button style (Search, Add) */
             background: var(--gradient);
             color: white;
         }

         .btn-primary:hover {
             background: var(--button-hover-bg);
             transform: translateY(-2px); /* Slight lift effect */
         }

         .btn-link { /* Link styled as button (Reset) */
             background: none;
             color: var(--primary-color); /* Or a neutral color */
             border: 1px solid var(--primary-color);
             padding: 9px 19px; /* Adjust padding to match primary button */
             transition: color 0.3s, border-color 0.3s;
         }
          body.dark-mode .btn-link {
               color: var(--primary-color);
               border-color: var(--primary-color);
          }

         .btn-link:hover {
              color: var(--secondary-color);
              border-color: var(--secondary-color);
             transform: translateY(-2px);
         }

         .btn-add {
             margin-left: 20px; /* Space between Reset and Add buttons */
         }


         /* Table Styling */
         .table-container {
             overflow-x: auto; /* Add horizontal scroll for small screens */
         }

         table {
             width: 100%;
             border-collapse: collapse; /* Remove space between borders */
             margin-top: 20px;
             background: var(--card-color);
             box-shadow: var(--box-shadow);
             border-radius: var(--border-radius);
             overflow: hidden; /* Ensures border-radius works on table */
         }

         th, td {
             padding: 12px 15px;
             text-align: left;
             border-bottom: 1px solid var(--table-border);
         }

         th {
             background-color: var(--table-header-bg);
             color: white;
             font-weight: 600;
             text-transform: uppercase; /* Make headers uppercase */
             font-size: 0.9rem;
         }

         tbody tr:nth-child(even) {
             background-color: var(--bg-color); /* Subtle stripe effect */
         }
          body.dark-mode tbody tr:nth-child(even) {
              background-color: #1a1a1a;
          }


         tbody tr:hover {
             background-color: var(--row-hover-bg); /* Highlight on hover */
         }

         .actions a {
             margin-right: 10px;
             color: var(--primary-color);
             text-decoration: none;
             transition: color 0.3s;
         }
          body.dark-mode .actions a {
              color: var(--primary-color);
          }

         .actions a:hover {
             color: var(--secondary-color);
         }
          body.dark-mode .actions a:hover {
               color: var(--secondary-color);
          }


         .text-center {
             text-align: center;
         }

         /* Interactive Widget (Footer-like positioning) */
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
              .dashboard-content h2 {
                  font-size: 1.8rem;
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

              /* Stack search form rows */
              #visaSearchForm > div {
                  flex-direction: column;
                  gap: 15px;
              }
               #visaSearchForm > div:last-child {
                   flex-direction: column; /* Stack buttons */
                   align-items: stretch; /* Stretch buttons */
               }
               #visaSearchForm .btn, #visaSearchForm .btn-link {
                   width: 100%; /* Make buttons full width */
                   text-align: center;
               }
               .btn-add {
                   margin-left: 0; /* Remove left margin when stacked */
                   margin-top: 10px; /* Add space above Add button */
               }

               th, td {
                   padding: 8px 10px; /* Reduce table padding */
                   font-size: 0.9rem;
               }
               .actions a {
                   margin-right: 5px;
               }
         }
          @media (max-width: 480px) {
               .search-form-container {
                   padding: 15px;
               }
               .form-group label {
                   font-size: 0.9rem;
               }
               .form-control {
                   padding: 8px;
                   font-size: 0.9rem;
               }
               th, td {
                   font-size: 0.8rem;
               }
               .actions a {
                    font-size: 0.8rem;
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

    <div class="dashboard-content"> <!-- Main container for dashboard content -->
        <h2 class="translate" data-key="visa_records">Visa Records</h2>

        <?php if (isset($_SESSION['form_success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['form_success']); unset($_SESSION['form_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['form_error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['form_error']); unset($_SESSION['form_error']); ?></div>
        <?php endif; ?>

        <!-- Search Form -->
        <div class="search-form-container">
            <!-- Form action points to list.php -->
            <form method="GET" id="visaSearchForm" action="list.php">
                 <!-- Keep these if list.php is included in dashboard_berau.php -->
                 <!-- Otherwise, consider removing them if they are not used for routing -->
                <input type="hidden" name="section" value="visa">
                <input type="hidden" name="action" value="list">

                <!-- Row 1 -->
                <div>
                    <div class="form-group">
                        <label class="translate" data-key="num_ordre">Num d'ordre</label>
                        <input type="number" name="num_ordre" class="form-control"
                               value="<?= htmlspecialchars($_GET['num_ordre'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="translate" data-key="reference">Reference</label>
                        <input type="number" name="reference" class="form-control"
                               value="<?= htmlspecialchars($_GET['reference'] ?? '') ?>">
                    </div>
                </div>

                <!-- Row 2 -->
                <div>
                    <div class="form-group">
                        <label class="translate" data-key="departement_origine">Departement Origine</label>
                        <select name="departement_origine" class="form-control">
                            <option value="">-- All --</option>
                            <?php foreach ($departements_origine_list as $dep): ?>
                                <option value="<?= htmlspecialchars($dep) ?>"
                                    <?= (isset($_GET['departement_origine']) && $_GET['departement_origine'] == $dep) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dep) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="translate" data-key="departement_cible">Departement Cible</label>
                        <select name="departement_cible" class="form-control">
                            <option value="">-- All --</option>
                             <?php foreach ($departements_cible_list as $dep): ?>
                                <option value="<?= htmlspecialchars($dep) ?>"
                                    <?= (isset($_GET['departement_cible']) && $_GET['departement_cible'] == $dep) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dep) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Row 3 -->
                <div>
                    <div class="form-group">
                        <label class="translate" data-key="division">Division</label>
                        <select name="division" class="form-control">
                            <option value="">-- All --</option>
                             <?php foreach ($divisions_list as $div): ?>
                                <option value="<?= htmlspecialchars($div) ?>"
                                    <?= (isset($_GET['division']) && $_GET['division'] == $div) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($div) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="translate" data-key="objet">Objet</label>
                        <input type="text" name="objet" class="form-control"
                               value="<?= htmlspecialchars($_GET['objet'] ?? '') ?>">
                    </div>
                </div>

                <!-- Row 4 (Buttons) -->
                <div>
                    <button type="submit" class="btn btn-primary translate" data-key="search">Search</button>
                    <!-- Reset button clears the form and redirects to list.php -->
                    <a href="list.php" class="btn btn-link translate" data-key="reset">Reset</a>
                    <!-- Assuming add.php is the page to add new records -->
                    <a href="add.php" class="btn btn-primary btn-add translate" data-key="add_new">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Reference</th>
                        <th>Date Envoi</th>
                        <th>Departement Origine</th>
                        <th>Departement Cible</th>
                        <th>N Reception</th>
                        <th>Date Reception</th>
                        <th>Num Ordre</th>
                        <th>Date Depart</th>
                        <th>Objet</th>
                        <th>Division</th>
                        <th>Important</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="visaResults">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['reference']) ?></td>
                            <td><?= htmlspecialchars($row['date_envoi']) ?></td>
                            <td><?= htmlspecialchars($row['date_envoi']) ? date('Y-m-d', strtotime($row['date_envoi'])) : '' ?></td>
                            <td><?= htmlspecialchars($row['departement_origine']) ?></td>
                            <td><?= htmlspecialchars($row['departement_cible']) ?></td>
                            <td><?= htmlspecialchars($row['n_reception']) ?></td>
                            <td><?= htmlspecialchars($row['date_reception']) ? date('Y-m-d', strtotime($row['date_reception'])) : '' ?></td>
                            <td><?= htmlspecialchars($row['num_ordre']) ?></td>
                            <td><?= htmlspecialchars($row['date_depart']) ? date('Y-m-d', strtotime($row['date_depart'])) : '' ?></td>
                            <td><?= htmlspecialchars($row['objet']) ?></td>
                            <td><?= htmlspecialchars($row['division']) ?></td>
                            <td><?= $row['important'] ? '<span style="color: var(--accent-color);">★</span>' : '' ?></td> <!-- Styled star -->
                            <td class="actions">
                                <!-- Updated Edit and Delete links -->
                                <a href="edit.php?id=<?= htmlspecialchars($row['id']) ?>" class="translate" data-key="edit">Edit</a>
                                <a href="delete.php?id=<?= htmlspecialchars($row['id']) ?>"
                                   onclick="return confirm('Are you sure you want to delete this record?')"
                                   class="translate" data-key="delete">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13" class="text-center translate" data-key="no_records_found">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div> <!-- Close .dashboard-content -->

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
        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize dark mode
            initDarkMode();

            // Initialize time and facts
            initTimeAndFacts();

            // Initialize AJAX search
            initAjaxSearch();
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

        function initAjaxSearch() {
            const searchForm = document.getElementById('visaSearchForm');
            const resultsTableBody = document.getElementById('visaResults');

            if (!searchForm || !resultsTableBody) {
                console.error("Search form or results table body not found.");
                return;
            }

            searchForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                const formData = new FormData(this);
                const params = new URLSearchParams(formData);

                // Construct the URL for the AJAX request.
                // It should point to the same script (list.php)
                // and include the search parameters.
                const url = 'list.php?' + params.toString(); // Use list.php as the endpoint

                fetch(url, {
                    method: 'GET', // Use GET for search
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Optional header to identify AJAX requests
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.text();
                })
                .then(html => {
                    // Parse the received HTML
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Find the #visaResults tbody from the parsed document
                    const newResultsBody = doc.getElementById('visaResults');

                    if (newResultsBody) {
                         // Replace the current table body content with the new content
                         resultsTableBody.innerHTML = newResultsBody.innerHTML;

                         // Update the browser's URL without a full page reload
                         // This allows users to bookmark or share search results
                         history.pushState(null, '', url);

                    } else {
                         console.error("Could not find #visaResults in the AJAX response.");
                         // Optionally display an error message to the user
                    }

                     // Handle potential alert messages from the AJAX response
                     const newAlertSuccess = doc.querySelector('.alert-success');
                     const newAlertError = doc.querySelector('.alert-error');
                     const existingAlertSuccess = document.querySelector('.alert-success');
                     const existingAlertError = document.querySelector('.alert-error');

                     // Remove existing alerts
                     if(existingAlertSuccess) existingAlertSuccess.remove();
                     if(existingAlertError) existingAlertError.remove();

                     // Add new alerts if they exist in the response
                     if (newAlertSuccess) {
                         document.querySelector('.dashboard-content').prepend(newAlertSuccess);
                     }
                     if (newAlertError) {
                          document.querySelector('.dashboard-content').prepend(newAlertError);
                     }


                })
                .catch(error => {
                    console.error('Error during AJAX search:', error);
                    // Display an error message to the user if the fetch fails
                    const errorDiv = document.createElement('div');
                    errorDiv.classList.add('alert', 'alert-error');
                    errorDiv.textContent = 'An error occurred while performing the search.';
                    const existingAlertError = document.querySelector('.alert-error');
                     if(existingAlertError) existingAlertError.remove(); // Remove previous error
                    document.querySelector('.dashboard-content').prepend(errorDiv);
                });
            });

            // Handle browser back/forward buttons for AJAX search results
            window.addEventListener('popstate', function(event) {
                 // Get the URL from the history state
                 const url = window.location.href;

                 // Re-trigger the search based on the URL parameters
                 // We can do this by creating a temporary form and submitting it
                 // or by directly calling the fetch logic with the current URL parameters.
                 // Direct fetch is cleaner:
                 const params = new URLSearchParams(window.location.search);
                 const tempForm = document.createElement('form');
                 tempForm.style.display = 'none'; // Hide the temporary form

                 // Populate temporary form with current URL params
                 for (const [key, value] of params) {
                     const input = document.createElement('input');
                     input.type = 'hidden';
                     input.name = key;
                     input.value = value;
                     tempForm.appendChild(input);
                 }

                 // Append to body (needed for FormData) and submit
                 document.body.appendChild(tempForm);
                 const popStateFormData = new FormData(tempForm);
                 const popStateParams = new URLSearchParams(popStateFormData);

                 fetch('list.php?' + popStateParams.toString(), { // Fetch from list.php
                     method: 'GET',
                     headers: {
                          'X-Requested-With': 'XMLHttpRequest'
                     }
                 })
                 .then(response => response.text())
                 .then(html => {
                      const parser = new DOMParser();
                      const doc = parser.parseFromString(html, 'text/html');
                      const newResultsBody = doc.getElementById('visaResults');
                      if (newResultsBody) {
                           resultsTableBody.innerHTML = newResultsBody.innerHTML;
                      }
                       // Also update search form fields to reflect the URL state
                       const newSearchForm = doc.getElementById('visaSearchForm');
                       if (newSearchForm) {
                           // A bit more complex: iterate through elements in the *current* form
                           // and update their values based on the *new* form's elements.
                           // This ensures we don't lose event listeners on the existing form.
                           const currentFormElements = searchForm.elements;
                           const newFormElements = newSearchForm.elements;

                           for (let i = 0; i < currentFormElements.length; i++) {
                                const currentElement = currentFormElements[i];
                                if (currentElement.name) {
                                     const newElement = newFormElements.namedItem(currentElement.name); // Use namedItem for easier access
                                     if (newElement) {
                                         if (currentElement.type === 'checkbox' || currentElement.type === 'radio') {
                                             currentElement.checked = newElement.checked;
                                         } else {
                                             currentElement.value = newElement.value;
                                         }
                                     } else {
                                         // If a field is present in the current form but not in the new one (e.g., filtered out),
                                         // you might want to reset its value or state.
                                         if (currentElement.type !== 'submit' && currentElement.type !== 'button' && currentElement.type !== 'hidden') {
                                             if (currentElement.type === 'checkbox' || currentElement.type === 'radio') {
                                                 currentElement.checked = false;
                                             } else if (currentElement.tagName === 'SELECT') {
                                                 currentElement.selectedIndex = 0; // Select the first option (usually "All")
                                             } else {
                                                  currentElement.value = '';
                                             }
                                         }
                                     }
                                }
                           }
                       }

                       // Handle potential alert messages from the AJAX response
                       const newAlertSuccess = doc.querySelector('.alert-success');
                       const newAlertError = doc.querySelector('.alert-error');
                       const existingAlertSuccess = document.querySelector('.alert-success');
                       const existingAlertError = document.querySelector('.alert-error');

                       // Remove existing alerts
                       if(existingAlertSuccess) existingAlertSuccess.remove();
                       if(existingAlertError) existingAlertError.remove();

                       // Add new alerts if they exist in the response
                       if (newAlertSuccess) {
                           document.querySelector('.dashboard-content').prepend(newAlertSuccess);
                       }
                       if (newAlertError) {
                            document.querySelector('.dashboard-content').prepend(newAlertError);
                       }

                 })
                 .catch(error => {
                     console.error('Error during popstate AJAX fetch:', error);
                     const errorDiv = document.createElement('div');
                     errorDiv.classList.add('alert', 'alert-error');
                     errorDiv.textContent = 'An error occurred while loading previous results.';
                     const existingAlertError = document.querySelector('.alert-error');
                      if(existingAlertError) existingAlertError.remove();
                     document.querySelector('.dashboard-content').prepend(errorDiv);
                 })
                 .finally(() => {
                     // Remove the temporary form
                     if (tempForm && tempForm.parentNode) {
                          tempForm.parentNode.removeChild(tempForm);
                     }
                 });
            });
        }
    </script>

</body>
</html>