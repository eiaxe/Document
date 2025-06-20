<?php
// MUST BE AT VERY TOP - NO WHITESPACE BEFORE
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require '../db.php'; // Make sure this path is correct

// Initialize lists for dropdowns
$departements_origine_list = [];
$departements_cible_list = [];
$divisions_list = []; // This will now come from the 'divisions' table
$signataires_list1 = [];
$observations_list = [];

    $sig_res = $conn->query("SELECT name FROM signataire ORDER BY name ASC");
    if ($sig_res) {
        while ($row = $sig_res->fetch_assoc()) {
            $signataires_list1[] = $row['name'];
        }
        $sig_res->free();
    } else {
        $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Error fetching divisions from 'divisions' table: " . $conn->error;
    }

// Fetch Signataires from signataire table
$signataires_result = $conn->query("SELECT id, name FROM signataire ORDER BY name ASC");
if ($signataires_result) {
    while ($row = $signataires_result->fetch_assoc()) {
        $signataires_list[$row['id']] = $row['name'];
    }
    $signataires_result->free();
} else {
    $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Error fetching signataires: " . $conn->error;
}
// Fetch organizations for dropdown (using division_fr as you want)
$sql_org = "SELECT id, division_fr FROM organizations ORDER BY division_fr ASC";
$result_org = $conn->query($sql_org);
if ($result_org && $result_org->num_rows > 0) {
    while ($row = $result_org->fetch_assoc()) {
        $organizations[] = $row;
    }
} else {
    $error_message = "Could not fetch organizations from the database.";
}
// Get unique values for dropdowns from the database
if ($conn) {
    // Fetch Departements d'origine (still from visa table for existing values)
    $departements_origine_result = $conn->query("SELECT DISTINCT departement_origine FROM visa WHERE departement_origine IS NOT NULL AND departement_origine != '' ORDER BY departement_origine ASC");
    if ($departements_origine_result) {
        while ($row = $departements_origine_result->fetch_assoc()) {
            $departements_origine_list[] = $row['departement_origine'];
        }
        $departements_origine_result->free();
    } else {
        $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Error fetching departements d'origine: " . $conn->error;
    }

    // Fetch Departements cible (still from visa table for existing values)
    $departements_cible_result = $conn->query("SELECT DISTINCT departement_cible FROM visa WHERE departement_cible IS NOT NULL AND departement_cible != '' ORDER BY departement_cible ASC");
    if ($departements_cible_result) {
        while ($row = $departements_cible_result->fetch_assoc()) {
            $departements_cible_list[] = $row['departement_cible'];
        }
        $departements_cible_result->free();
    } else {
        $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Error fetching departements cible: " . $conn->error;
    }

    // Fetch Divisions from the 'divisions' table
    // Assuming 'divisions' table has a 'name' column
    $divisions_result = $conn->query("SELECT name FROM divisions ORDER BY name ASC");
    if ($divisions_result) {
        while ($row = $divisions_result->fetch_assoc()) {
            $divisions_list[] = $row['name'];
        }
        $divisions_result->free();
    } else {
        $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Error fetching divisions from 'divisions' table: " . $conn->error;
    }


    // Fetch Signataires (still from visa table for existing values)
    $signataires_result = $conn->query("SELECT DISTINCT signataire_province FROM visa WHERE signataire_province IS NOT NULL AND signataire_province != '' ORDER BY signataire_province ASC");
    if ($signataires_result) {
        while ($row = $signataires_result->fetch_assoc()) {
            $signataires_list[] = $row['signataire_province'];
        }
        $signataires_result->free();
    } else {
        $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Error fetching signataires: " . $conn->error;
    }

    // Fetch Observations (still from visa table for existing values)
    $observations_result = $conn->query("SELECT DISTINCT observations FROM visa WHERE observations IS NOT NULL AND observations != '' ORDER BY observations ASC");
     if ($observations_result) {
        while ($row = $observations_result->fetch_assoc()) {
            $observations_list[] = $row['observations'];
        }
        $observations_result->free();
     } else {
         $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Error fetching observations: " . $conn->error;
     }
} else {
     $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Database connection failed for fetching lists.";
}


// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic validation - You can add more robust checks
    if (empty($_POST['reference']) || empty($_POST['date_envoi']) || empty($_POST['departement_origine']) || empty($_POST['departement_cible']) || empty($_POST['n_reception']) || empty($_POST['date_reception']) || empty($_POST['num_ordre']) || empty($_POST['date_depart']) || empty($_POST['objet']) || empty($_POST['division']) || empty($_POST['signataire_province']) || empty($_POST['observations'])) {
         $_SESSION['form_error'] = "All fields are required.";
    } else {
        $reference = $_POST['reference'];
        $date_envoi = $_POST['date_envoi'];
        $departement_origine = $_POST['departement_origine'];
        $departement_cible = $_POST['departement_cible'];
        $n_reception = $_POST['n_reception'];
        $date_reception = $_POST['date_reception'];
        $num_ordre = $_POST['num_ordre'];
        $date_depart = $_POST['date_depart'];
        $objet = $_POST['objet'];
        $division = $_POST['division'];
        $signataire_province = $_POST['signataire_province'];
        $observations = $_POST['observations'];
        $important = isset($_POST['important']) ? 1 : 0;

        // Check if num_ordre already exists
        $check = $conn->prepare("SELECT id FROM visa WHERE num_ordre = ?"); // Select id instead of * for efficiency
        if ($check === false) {
             $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Database check query preparation failed: " . $conn->error;
        } else {
            $check->bind_param("i", $num_ordre);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Num d'ordre already exists!";
            } else {
                 // Check if connection is still valid before inserting
                 if ($conn && $conn->ping()) {
                    $insert = $conn->prepare("INSERT INTO visa (reference, date_envoi, departement_origine, departement_cible, n_reception, date_reception, num_ordre, date_depart, objet, division, signataire_province, observations, important)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    if ($insert === false) {
                        $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Database insert query preparation failed: " . $conn->error;
                    } else {
                        // Bind parameters (check types match your DB schema)
                        // Assumes: i, s, s, s, i, s, i, s, s, s, s, s, i
                        $bind_success = $insert->bind_param("issssissssssi", $reference, $date_envoi, $departement_origine, $departement_cible, $n_reception, $date_reception, $num_ordre, $date_depart, $objet, $division, $signataire_province, $observations, $important);

                        if ($bind_success === false) {
                             $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Database bind_param failed: " . $insert->error;
                        } else {
                            if ($insert->execute()) {
                                $_SESSION['form_success'] = "Visa record added successfully!";
                                // Redirect to list.php
                                header("Location: list.php");
                                exit();
                            } else {
                                $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Error adding visa record: " . $insert->error;
                            }
                        }
                         $insert->close();
                    }
                 } else {
                     $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Database connection lost before insertion.";
                 }
            }
            $check->close();
        }
    }
}

// Close the database connection (optional but good practice)
// Only close if the script hasn't exited yet and connection is valid
if ($conn && $conn->ping()) {
    $conn->close();
}

// --- HTML Form Display ---
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Visa Record | Al Hoceima Employee Portal</title> <!-- Updated Title -->
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
            max-width: 700px; /* Smaller container for the form */
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Header Styles - Keep consistent (Assuming this page is part of a larger structure, but adding header for standalone testing) */
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


        /* Main Content Adaptation for Dashboard Structure */
        .dashboard-content { /* Used the class from your provided code */
            padding: 40px 20px; /* Add padding */
            max-width: 800px; /* Adjust max-width as needed for dashboard content */
            margin: 0 auto; /* Center the content */
        }

         .dashboard-content h2 { /* Styling for the main heading */
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            color: var(--primary-color);
         }

         .dashboard-form { /* Used the class from your provided code */
            background: var(--card-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            max-width: 700px; /* Form specific max-width */
            margin: 0 auto; /* Center the form within dashboard-content */
         }

        .form-row { /* Used the class from your provided code */
            display: flex;
            gap: 20px; /* Space between elements in a row */
            margin-bottom: 20px;
        }

        .form-group { /* Used the class from your provided code */
            flex: 1; /* Make form groups take equal width in a row */
            margin-bottom: 0; /* Remove bottom margin from form-group when inside form-row */
        }

        .form-group:last-child {
            margin-bottom: 0; /* Ensure no extra margin on the last one */
        }


        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-color);
        }
         .form-group input[type="text"],
         .form-group input[type="date"],
         .form-group input[type="number"],
         .form-group select,
         .form-group textarea,
         .form-control { /* Added .form-control to apply style to those elements */
            width: 100%;
            padding: 10px;
            border: 1px solid var(--input-border);
            border-radius: var(--border-radius);
            font-size: 1rem;
            color: var(--text-color);
            background-color: var(--card-color);
            transition: border-color 0.3s, box-shadow 0.3s;
         }
         .form-group input[type="text"]:focus,
         .form-group input[type="date"]:focus,
         .form-group input[type="number"]:focus,
         .form-group select:focus,
         .form-group textarea:focus,
         .form-control:focus { /* Added .form-control:focus */
             outline: none;
             border-color: var(--input-focus-border);
             box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.1);
         }
         .form-group textarea {
             min-height: 100px;
             resize: vertical;
         }

         .checkbox-label { /* Used the class from your provided code */
             display: flex; /* Align checkbox and text */
             align-items: center;
             margin-bottom: 20px;
             cursor: pointer;
             font-weight: 400; /* Reset font-weight from .form-group label */
         }
         .checkbox-label input[type="checkbox"] { /* Target the checkbox within the label */
             margin-right: 10px;
             width: 20px;
             height: 20px;
             accent-color: var(--primary-color); /* Style the checkbox */
         }


        .form-actions { /* Used the class from your provided code */
            display: flex;
            gap: 10px; /* Space between buttons */
            justify-content: flex-end; /* Align buttons to the right */
            margin-top: 30px;
        }

        .btn { /* Basic style for all buttons */
             display: inline-block;
            padding: 12px 20px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s, box-shadow 0.3s;
            text-align: center;
            text-decoration: none;
        }

        .btn-primary { /* Style for the primary button (Save) */
             background: var(--button-bg);
             color: white;
        }
        .btn-primary:hover {
            background: var(--button-hover-bg);
             transform: translateY(-2px);
             box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .btn-link { /* Style for the link button (View Records) */
            background-color: transparent; /* No background */
            color: var(--primary-color); /* Link color */
            padding: 12px 0; /* Adjust padding */
            font-weight: 500;
        }
         body.dark-mode .btn-link {
             color: var(--primary-color); /* Keep link color in dark mode */
         }

        .btn-link:hover {
            color: var(--secondary-color); /* Hover color */
             text-decoration: underline;
             transform: none; /* No transform on link hover */
             box-shadow: none; /* No shadow on link hover */
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
             .dashboard-form {
                 padding: 20px;
             }
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
             .form-row {
                 flex-direction: column; /* Stack form rows on small screens */
                 gap: 0; /* Remove gap when stacked */
             }
             .form-group {
                 margin-bottom: 20px; /* Add bottom margin back when stacked */
             }
             .form-actions {
                 flex-direction: column; /* Stack buttons on small screens */
                 gap: 10px;
             }
             .btn, .btn-link { /* Apply width to both button types */
                 width: 100%;
                 text-align: center;
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
        <h2 class="translate" data-key="add_visa">Add Visa Record</h2>

        <?php if(isset($_SESSION['form_error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['form_error']) ?>
                <?php unset($_SESSION['form_error']); ?>
            </div>
        <?php endif; ?>
         <?php if(isset($_SESSION['form_success'])): ?>
             <!-- Although you redirect on success, keeping this for consistency -->
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['form_success']) ?>
                <?php unset($_SESSION['form_success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="dashboard-form"> <!-- Form container -->
            <div class="form-row">
                <div class="form-group">
                    <label class="translate" data-key="reference">Reference</label>
                    <input type="number" name="reference" class="form-control" required value="<?= htmlspecialchars($_POST['reference'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="translate" data-key="date_envoi">Date d'envoi</label>
                    <input type="date" name="date_envoi" class="form-control" required value="<?= htmlspecialchars($_POST['date_envoi'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="translate" data-key="departement_origine">Departement d'origine</label>
                    <select name="departement_origine" class="form-control" required>
        <option value="">Select Destinataire</option>
        <?php foreach ($organizations as $org): ?>
            <option value="<?= $org['id'] ?>"><?= htmlspecialchars($org['division_fr']) ?></option>
        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="translate" data-key="departement_cible">Departement cible</label>
                    <select name="departement_cible" class="form-control" required>
        <option value="">Select Destinataire</option>
        <?php foreach ($organizations as $org): ?>
            <option value="<?= $org['id'] ?>"><?= htmlspecialchars($org['division_fr']) ?></option>
        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="translate" data-key="n_reception">N de reception</label>
                    <input type="number" name="n_reception" class="form-control" required value="<?= htmlspecialchars($_POST['n_reception'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="translate" data-key="date_reception">Date de reception</label>
                    <input type="date" name="date_reception" class="form-control" required value="<?= htmlspecialchars($_POST['date_reception'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="translate" data-key="num_ordre">Num d'ordre</label>
                    <input type="number" name="num_ordre" class="form-control" required value="<?= htmlspecialchars($_POST['num_ordre'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="translate" data-key="date_depart">Date depart</label>
                    <input type="date" name="date_depart" class="form-control" required value="<?= htmlspecialchars($_POST['date_depart'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="translate" data-key="objet">Objet</label>
                    <input type="text" name="objet" class="form-control" required value="<?= htmlspecialchars($_POST['objet'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="translate" data-key="division">Division</label>
                    <select name="division" class="form-control" required>
                        <option value="">-- Select Division --</option>
                        <?php
                        // Populate from the divisions_list fetched from the 'divisions' table
                        foreach ($divisions_list as $div) {
                            $selected = (isset($_POST['division']) && $_POST['division'] == $div) ? "selected" : "";
                            echo "<option value=\"" . htmlspecialchars($div) . "\" $selected>" . htmlspecialchars($div) . "</option>";
                        }
                        ?>
                    </select>
            </div>

            <div class="form-group">
                <label class="translate" data-key="signataire_province">Signataire province</label>
                    <select name="signataire_province" class="form-control" required>
                        <option value="">-- Select Signataire --</option>
                        <?php
                        // Populate from the divisions_list fetched from the 'divisions' table
                        foreach ($signataires_list1 as $div) {
                            $selected = (isset($_POST['signataire']) && $_POST['signataire'] == $div) ? "selected" : "";
                            echo "<option value=\"" . htmlspecialchars($div) . "\" $selected>" . htmlspecialchars($div) . "</option>";
                        }
                        ?>
                    </select>
             </div>

                <div class="form-group">
                    <label class="translate" data-key="observations">Observations</label>
                    <select name="observations" class="form-control" required>
                        <option value="">-- Select Observation --</option>
                        <option value="unknoen">unknoen</option>
                    </select>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="important" <?= isset($_POST['important']) ? 'checked' : '' ?>> <!-- Retain checked state -->
                    <span class="translate" data-key="important">Important</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary translate" data-key="save">Save</button>
                <a href="list.php" class="btn btn-link">View Visa Records</a> <!-- Updated link -->
            </div>
        </form>
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