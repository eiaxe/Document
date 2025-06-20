<?php
session_start();
// Correct the include path for db.php
// Assuming db.php is in the parent directory (document_tracking) relative to personal/add.php
require '../db.php'; // Use require instead of include for critical files

// Check if the database connection was established
if (!$conn) {
    // Handle connection error - log it or display a user-friendly message
    // Avoid exposing raw database errors to users in production
    $_SESSION['form_error'] = "Database connection failed.";
    // Optionally redirect or display an error page
    // header("Location: error.php"); // Example redirect
    // exit();
    // For now, we'll let the script continue and display the error later
}


// Ensure the form submission is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if database connection is available before processing POST
    if (!$conn) {
         $_SESSION['form_error'] = "Cannot process submission: Database connection failed.";
         header("Location: add.php"); // Redirect back to the form
         exit();
    }

    // Retrieve form data - basic sanitization using htmlspecialchars
    $groupe = htmlspecialchars($_POST['groupe'] ?? '');
    $nom_complet = htmlspecialchars($_POST['nom_complet'] ?? '');
    $province = htmlspecialchars($_POST['province'] ?? '');
    $adresse = htmlspecialchars($_POST['adresse'] ?? '');
    $tel = htmlspecialchars($_POST['tel'] ?? '');
    $date_envoi = $_POST['date_envoi'] ?? null; // Keep date as is for DB, validate later
    $numero_ordre = $_POST['numero_ordre'] ?? null; // Keep number as is for DB, validate later
    $date_arrive = $_POST['date_arrive'] ?? null; // Keep date as is for DB, validate later
    $objet = htmlspecialchars($_POST['objet'] ?? '');
    $division = htmlspecialchars($_POST['division'] ?? '');
    $important = isset($_POST['important']) ? 1 : 0;

    // Basic Server-side Validation (Add more as needed)
    $errors = [];
    if (empty($groupe)) $errors[] = "Groupe destinataires is required.";
    if (empty($nom_complet)) $errors[] = "Nom complet is required.";
    if (empty($province)) $errors[] = "Province is required.";
    if (empty($adresse)) $errors[] = "Adresse is required.";
    if (empty($tel)) $errors[] = "Téléphone is required.";
    if (empty($date_envoi)) $errors[] = "Date d'envoi is required.";
    // Allow 0 as a valid number for numero_ordre, check if it's set and numeric
    if (!isset($_POST['numero_ordre']) || $_POST['numero_ordre'] === '' || !is_numeric($numero_ordre)) {
         $errors[] = "Numéro d'ordre is required and must be a number.";
    } else {
        $numero_ordre = (int)$numero_ordre; // Cast to integer after validation
    }
    if (empty($date_arrive)) $errors[] = "Date d'arrivée is required.";
    if (empty($objet)) $errors[] = "Objet is required.";
    if (empty($division)) $errors[] = "Division is required.";

    // If there are validation errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['form_error'] = "Validation errors: " . implode("<br>", $errors);
        // Optionally store the submitted data in session to repopulate the form
        // $_SESSION['form_data'] = $_POST;
        header("Location: add.php");
        exit();
    }


    // Ensure unique order number (only if validation passes)
    // Use prepared statement to prevent SQL injection
    // *** CORRECTED TABLE NAME HERE ***
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM personale WHERE numero_ordre = ?");
    if ($stmt_check === false) {
        $_SESSION['form_error'] = "Database check query preparation failed: " . $conn->error;
        header("Location: add.php");
        exit();
    }
    $stmt_check->bind_param("i", $numero_ordre);
    $stmt_check->execute();
    $stmt_check->bind_result($exists);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($exists > 0) {
        $_SESSION['form_error'] = "⚠️ Ce numéro d'ordre existe déjà.";
        // Optionally store the submitted data in session to repopulate the form
        // $_SESSION['form_data'] = $_POST;
        header("Location: add.php"); // Redirect back to the form
        exit();
    } else {
        // Insert into database (only if order number is unique and validation passes)
        // *** CORRECTED TABLE NAME HERE ***
        // Also added 'created_at' column to the INSERT statement
        $stmt_insert = $conn->prepare("INSERT INTO personale (groupe, nom_complet, province, adresse, tel, date_envoi, numero_ordre, date_arrive, objet, division, important, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        if ($stmt_insert === false) {
            $_SESSION['form_error'] = "Database insert query preparation failed: " . $conn->error;
            header("Location: add.php");
            exit();
        }

        // Bind parameters - check types: ssssssisssi (11 parameters for the first 11 columns)
        // NOW() is handled by the database, so we bind 11 parameters.
        $bind_types = "ssssssisssi"; // groupe(s), nom_complet(s), province(s), adresse(s), tel(s), date_envoi(s), numero_ordre(i), date_arrive(s), objet(s), division(s), important(i)

        if ($stmt_insert->bind_param($bind_types, $groupe, $nom_complet, $province, $adresse, $tel, $date_envoi, $numero_ordre, $date_arrive, $objet, $division, $important) === false) {
             $_SESSION['form_error'] = "Database bind_param failed: " . $stmt_insert->error;
             header("Location: add.php");
             exit();
        }


        if ($stmt_insert->execute()) {
            $_SESSION['form_success'] = "Record added successfully!";
            // Unset form data from session if you were storing it
            // unset($_SESSION['form_data']);
            header("Location: list.php"); // Redirect to the list page
            exit;
        } else {
            $_SESSION['form_error'] = "❌ Error adding record: " . $stmt_insert->error;
            // Optionally store the submitted data in session to repopulate the form
            // $_SESSION['form_data'] = $_POST;
            header("Location: add.php"); // Redirect back to the form on insert error
            exit();
        }
        $stmt_insert->close();
    }
}

// If it's a GET request or POST failed before redirect, display the form

// Get unique values for dropdowns (optional, but good for consistency with list/edit)
// Fetching lists for province, division, groupe if needed for dynamic dropdowns
// Or use a static list as you currently do for provinces.
// For 'groupe' and 'division', you might want to fetch distinct values if they
// are not fixed lists. Let's assume they are fixed for now based on your HTML.

// Province list - static as you defined
$provinces = ["Al Hoceima","Tanger","Rabat","Casablanca","Fès","Agadir","Bni Mellal","Divers","Guelmim",
"Laâyoune","Marrakech","Oued Dahab","Oujda","Rachidia","Al Haouz","Aousserd","Assa-Zag","Azilal","Benslimane",
"Berkane","Berrachid","Boujdour","Boulemane","Chefchaoun","Chichaoua","Chtouka-Aït Baha","Driouch","El Hajeb",
"El Jadida","El Kelaâ Des Sraghna","Essaouira","Fahs-Anjra","Figuig","Fquih Ben Salah","Guercif","Ifrane",
"Inezgane","Jerada","Kénitra","Khémisset","Khénifra","Khouribga","Larache","M'Diq","Médiouna","Meknès",
"Midelt","Mohammédia","Moulay Yaâcoub","Nador","Nouacer","Ouarzazate","Ouezzane","Rehamna","Safi","Salé",
"Séfrou","Settat","Sidi Bennour","Sidi Ifni","Sidi Kacem","Sidi Slimane","Skhirate-Témara","Smara","Tan-Tan",
"Taounate","Taourirt","Tarfaya","Taroudant","Tata","Taza","Tétouan","Tinghir","Tiznit","Youssoufia","Zagora",
"Bruxelles","Toulouse","Anvers","Barcelone","Madrid","Lille","Bilbao","Dusseldorf","Orléans","Marseille",
"Oran","Targuist","Bays bas","Pays bas","Villemomble","Amsterdam","U.S.A","Montpellier","Tarragona",
"Denbosch","يثىلا","France","Norvége","Mallorca","Italy","Belgigue","Bilgigue","Britain","Danemark","TETOUAN"];

// Close the database connection if it was opened and is still active
if ($conn && $conn->ping()) {
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Personal Record</title>
     <!-- Add your CSS includes here -->
     <!-- For consistency, you might want to use the same CSS as list.php -->
     <style>
         /* Paste the relevant CSS from your list.php or edit.php here */
         /* This should include styles for body, .dashboard-content, h2, alerts,
            .dashboard-form, .form-row, .form-group, label, .form-control,
            .checkbox-label, .form-actions, .btn, .btn-primary, .btn-secondary,
            and the interactive widget if you want it on this page too. */
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

             /* Main Content Adaptation for Dashboard Structure */
             .dashboard-content { /* Used the class from your provided code */
                 padding: 40px 20px; /* Add padding */
                 max-width: 800px; /* Adjusted max-width for form */
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

             /* Form Styling (from your add.php/edit.php code) */
             .dashboard-form {
                 background: var(--card-color);
                 border-radius: var(--border-radius);
                 box-shadow: var(--box-shadow);
                 padding: 30px;
             }

             .form-row {
                 display: flex;
                 gap: 20px;
                 margin-bottom: 20px;
             }

             .form-group {
                 flex: 1; /* Each form group takes equal space in the row */
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
              textarea.form-control {
                  min-height: 100px; /* Give textarea some height */
                  resize: vertical; /* Allow vertical resizing */
              }


             /* Checkbox styling */
             .checkbox-label {
                 display: flex;
                 align-items: center;
                 gap: 10px;
                 cursor: pointer;
                 font-weight: 600;
                 font-size: 1rem;
                 color: var(--text-color);
             }

             .checkbox-label input[type="checkbox"] {
                 /* Hide default checkbox */
                 appearance: none;
                 -webkit-appearance: none;
                 -moz-appearance: none;
                 width: 20px;
                 height: 20px;
                 border: 2px solid var(--input-border);
                 border-radius: 4px;
                 background-color: var(--card-color);
                 display: inline-block;
                 position: relative;
                 cursor: pointer;
                 transition: background-color 0.3s, border-color 0.3s;
             }
              body.dark-mode .checkbox-label input[type="checkbox"] {
                  background-color: #2a2a2a;
                   border-color: var(--input-border);
              }

             .checkbox-label input[type="checkbox"]:checked {
                 background-color: var(--primary-color);
                 border-color: var(--primary-color);
             }

             .checkbox-label input[type="checkbox"]:checked::after {
                 content: '\f00c'; /* Font Awesome checkmark icon */
                 font-family: 'Font Awesome 6 Free';
                 font-weight: 900; /* Solid icon */
                 color: white;
                 position: absolute;
                 top: 50%;
                 left: 50%;
                 transform: translate(-50%, -50%);
                 font-size: 14px;
             }

             /* Form Actions */
             .form-actions {
                 margin-top: 30px;
                 display: flex;
                 justify-content: flex-end; /* Align buttons to the right */
                 gap: 15px; /* Space between buttons */
             }

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

             .btn-primary { /* Primary button style (Save) */
                 background: var(--gradient);
                 color: white;
             }

             .btn-primary:hover {
                 background: var(--button-hover-bg);
                 transform: translateY(-2px); /* Slight lift effect */
             }

             .btn-secondary { /* Secondary button style (Cancel) */
                 background-color: #6c757d;
                 color: white;
             }
              body.dark-mode .btn-secondary {
                  background-color: #5a6268;
              }

             .btn-secondary:hover {
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

                  /* Stack form rows */
                  .form-row {
                      flex-direction: column;
                      gap: 15px;
                  }
                  .form-actions {
                      flex-direction: column;
                      gap: 10px;
                      align-items: stretch; /* Stretch buttons */
                  }
                   .form-actions .btn {
                       width: 100%; /* Make buttons full width */
                       text-align: center;
                   }
             }
              @media (max-width: 480px) {
                   .dashboard-form {
                       padding: 20px; /* Reduce form padding */
                   }
                   .form-group label {
                       font-size: 0.9rem;
                   }
                   .form-control {
                       padding: 8px;
                       font-size: 0.9rem;
                   }
                   .checkbox-label {
                        font-size: 0.9rem;
                   }
              }
     </style>
     <!-- Include Font Awesome for icons -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <h2>Add New Personal Record</h2>

        <?php if(isset($_SESSION['form_success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['form_success']) ?>
                <?php unset($_SESSION['form_success']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['form_error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['form_error']) ?>
                <?php unset($_SESSION['form_error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="dashboard-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Groupe destinataires:</label>
                    <select name="groupe" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="Partis Politiques (الأحزاب السياسية)">Partis Politiques (الأحزاب السياسية)</option>
                        <option value="Syndicats (النقابات)">Syndicats (النقابات)</option>
                        <option value="Associations (الجمعيات)">Associations (الجمعيات)</option>
                        <option value="Coopératives (التعاونيات)">Coopératives (التعاونيات)</option>
                        <option value="Entreprises et societés (المقاولات والشركات)">Entreprises et societés (المقاولات والشركات)</option>
                        <option value="Citoyens (المواطنون)">Citoyens (المواطنون)</option>
                        <option value="Groupe de population (مجموعة من الساكنة)">Groupe de population (مجموعة من الساكنة)</option>
                        <option value="Fonctionnaires (الموظفون)">Fonctionnaires (الموظفون)</option>
                        <option value="Avocats (المحامون)">Avocats (المحامون)</option>
                    </select>
                </div>
                 <div class="form-group">
                    <label>Nom complet:</label>
                    <input type="text" name="nom_complet" class="form-control" required>
                </div>
            </div>

             <div class="form-row">
                <div class="form-group">
                    <label>Province:</label>
                    <select name="province" class="form-control" required>
                        <option value="">-- Select --</option>
                        <?php
                        // Use the static $provinces array defined above
                        foreach ($provinces as $province) {
                            echo "<option value='" . htmlspecialchars($province) . "'>" . htmlspecialchars($province) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                 <div class="form-group">
                    <label>Adresse:</label>
                    <input type="text" name="adresse" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                 <div class="form-group">
                    <label>Téléphone:</label>
                    <input type="tel" name="tel" class="form-control" required>
                </div>
                 <div class="form-group">
                    <label>Date d'envoi:</label>
                    <input type="date" name="date_envoi" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                 <div class="form-group">
                    <label>Numéro d'ordre:</label>
                    <input type="number" name="numero_ordre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Date d'arrivée:</label>
                    <input type="date" name="date_arrive" class="form-control" required>
                </div>
            </div>

             <div class="form-row">
                <div class="form-group">
                    <label>Division:</label>
                    <input type="text" name="division" class="form-control" required>
                </div>
                 <div class="form-group">
                    <label class="checkbox-label">
                         <input type="checkbox" name="important">
                         <span>Important</span>
                     </label>
                 </div>
            </div>

            <div class="form-group">
                <label>Objet:</label>
                <textarea name="objet" class="form-control" required></textarea>
            </div>


            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save</button>
                 <!-- Assuming list.php is the list page -->
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
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