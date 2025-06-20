<?php
session_start(); // Start the session for messages

// Correct the include path for db.php
// Assuming db.php is in the parent directory (document_tracking) relative to depart/add.php
require '../db.php'; // Use require for critical files

// Check if the database connection was established
if (!$conn) {
    // Handle connection error
    $_SESSION['form_error'] = "Database connection failed. Cannot add record.";
    header("Location: add.php"); // Redirect back to the form
    exit();
}

// Initialize variables to hold form data in case of error
// Based on your 'depart' table columns
$num_ordre = '';
$date_envoi = '';
$destinataire = '';
$objet = '';
$division = '';
$responsable = '';
$observations = '';
$important = 0;

// --- Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    // Based on your form inputs and 'depart' table columns
    $num_ordre = intval($_POST['num_ordre']); // Ensure integer
    $date_envoi = htmlspecialchars($_POST['date_envoi']);
    $destinataire = htmlspecialchars($_POST['destinataire']);
    $objet = htmlspecialchars($_POST['objet']);
    $division = htmlspecialchars($_POST['division']);
    $responsable = htmlspecialchars($_POST['responsable']);
    $observations = htmlspecialchars($_POST['observations']);
    $important = isset($_POST['important']) ? 1 : 0;

    // Check if num_ordre exists in the 'depart' table
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM depart WHERE num_ordre = ?");

    if ($check_stmt === false) {
        $_SESSION['form_error'] = "Error preparing uniqueness check: " . $conn->error;
    } else {
        $check_stmt->bind_param("i", $num_ordre);
        if ($check_stmt->execute()) {
            $check_stmt->bind_result($count);
            $check_stmt->fetch();
            $check_stmt->close();

            if ($count > 0) {
                // Set error message in session
                $_SESSION['form_error'] = "⚠️ Ce numéro d'ordre ('$num_ordre') existe déjà dans la table 'depart'. Veuillez utiliser un numéro différent.";
                // Data is already in variables, form will be pre-filled
            } else {
                // Insert into 'depart' table
                // Match columns in the INSERT statement to your 'depart' table
                $insert_stmt = $conn->prepare("INSERT INTO depart (num_ordre, date_envoi, destinataire, objet, division, responsable, observations, important) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                if ($insert_stmt === false) {
                     $_SESSION['form_error'] = "Error preparing insert query: " . $conn->error;
                } else {
                    // Bind parameters - match data types: i=integer, s=string
                    $insert_stmt->bind_param("issssssi", $num_ordre, $date_envoi, $destinataire, $objet, $division, $responsable, $observations, $important);

                    if ($insert_stmt->execute()) {
                        // Set success message in session
                        $_SESSION['form_success'] = "✅ Information ajoutée avec succès dans la table 'depart'.";
                        // Redirect to prevent form resubmission on refresh
                        header("Location: add.php");
                        exit;
                    } else {
                        // Error during execution
                        $_SESSION['form_error'] = "❌ Erreur lors de l'ajout dans la table 'depart': " . $insert_stmt->error;
                         // Data is already in variables, form will be pre-filled
                    }
                    $insert_stmt->close();
                }
            }
        } else {
             $_SESSION['form_error'] = "Error executing uniqueness check: " . $check_stmt->error;
             // Data is already in variables, form will be pre-filled
        }
    }
}

// --- Fetch Destinataire Options from the 'destinataires' table ---
// Assuming 'destinataires' table has a column like 'name' or 'destinataire_name'
$destinataire_options = [];
$destinataire_query = $conn->prepare("SELECT id, division_fr FROM organizations WHERE division_fr IS NOT NULL AND division_fr != '' ORDER BY division_fr");

if ($destinataire_query === false) {
    $_SESSION['form_error'] = "Error fetching destinataire options: " . $conn->error;
} else {
    if ($destinataire_query->execute()) {
        $result = $destinataire_query->get_result();
        while ($row = $result->fetch_assoc()) {
            $destinataire_options[] = $row;
        }
        $destinataire_query->close();
    } else {
        $_SESSION['form_error'] = "Error executing destinataire fetch: " . $destinataire_query->error;
        $destinataire_query->close();
    }
}


// --- Fetch Division Options from the 'divisions' table ---
// Assuming 'divisions' table has a column like 'name' or 'division_name'
$division_options = [];
// **IMPORTANT:** Replace 'name' with the actual column name for division names in your 'divisions' table
$division_query = $conn->prepare("SELECT name FROM divisions WHERE name IS NOT NULL AND name != '' ORDER BY name");

if ($division_query === false) {
     $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Error fetching division options: " . $conn->error;
} else {
    if ($division_query->execute()) {
        $result = $division_query->get_result();
        while ($row = $result->fetch_assoc()) {
             // **IMPORTANT:** Replace 'name' with the actual column name you selected
            $division_options[] = $row['name'];
        }
        $division_query->close();
    } else {
         $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Error executing division fetch: " . $division_query->error;
         $division_query->close(); // Close even on error
    }
}

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
    <title>Add Depart Record</title>
    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <style>
        /* Add the CSS from your list.php here or link to a shared CSS file */
        /* Ensure this CSS is complete and includes all styles from the previous example */
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
            max-width: 800px; /* Container width for the form */
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

        /* Form Content */
        .form-content { /* Container for the form */
            padding: 40px 20px;
            max-width: 800px; /* Match container width */
            margin: 0 auto;
        }

         .form-content h1 { /* Styling for the main heading */
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

        /* Form Styling */
        .add-form { /* Class for the form */
            background: var(--card-color);
            padding: 30px; /* Increased padding */
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .add-form label {
            display: block;
            margin-bottom: 8px; /* Space below label */
            font-weight: 600;
            color: var(--text-color);
        }

        .add-form input[type="text"],
        .add-form input[type="number"],
        .add-form input[type="date"],
        .add-form select,
        .add-form textarea {
            width: 100%;
            padding: 12px; /* Increased padding */
            margin-bottom: 20px; /* Space below input */
            border: 1px solid var(--input-border);
            border-radius: var(--border-radius);
            box-sizing: border-box; /* Include padding and border in element's total width and height */
            font-size: 1rem;
            color: var(--text-color);
            background-color: var(--bg-color); /* Input background */
            transition: border-color 0.3s, box-shadow 0.3s;
        }
         body.dark-mode .add-form input[type="text"],
         body.dark-mode .add-form input[type="number"],
         body.dark-mode .add-form input[type="date"],
         body.dark-mode .add-form select,
         body.dark-mode .add-form textarea {
             background-color: #2a2a2a;
             color: var(--text-color);
             border-color: var(--input-border);
         }

        .add-form input[type="text"]:focus,
        .add-form input[type="number"]:focus,
        .add-form input[type="date"]:focus,
        .add-form select:focus,
        .add-form textarea:focus {
            outline: none;
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.1);
        }
         body.dark-mode .add-form input[type="text"]:focus,
         body.dark-mode .add-form input[type="number"]:focus,
         body.dark-mode .add-form input[type="date"]:focus,
         body.dark-mode .add-form select:focus,
         body.dark-mode .add-form textarea:focus {
              box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
         }


        .add-form textarea {
            resize: vertical; /* Allow vertical resizing */
            min-height: 100px; /* Minimum height for textarea */
        }

        .checkbox-container { /* Container for the important checkbox */
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .checkbox-container input[type="checkbox"] {
             width: auto; /* Don't make checkbox full width */
             margin-right: 10px;
             margin-bottom: 0; /* Remove bottom margin */
             transform: scale(1.2); /* Make checkbox slightly larger */
             accent-color: var(--primary-color); /* Style the checkbox itself */
        }

         .checkbox-container label {
             margin-bottom: 0; /* Remove bottom margin */
         }


        .form-actions { /* Container for buttons */
            display: flex;
            gap: 15px;
            justify-content: center; /* Center the buttons */
            margin-top: 30px; /* Space above buttons */
        }

        .form-actions button,
        .form-actions a.btn { /* Style the back link as a button */
            padding: 12px 25px; /* Increased padding */
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s, transform 0.1s;
             text-decoration: none; /* Remove underline from link */
             display: inline-flex; /* Align icon and text */
             align-items: center;
             gap: 5px; /* Space between icon and text */
             font-weight: 600;
        }

        .form-actions button[type="submit"] {
            background: var(--gradient);
            color: white;
        }
        .form-actions button[type="submit"]:hover {
            background: var(--button-hover-bg);
            transform: translateY(-2px);
        }

        .form-actions a.btn {
             background-color: #6c757d; /* Secondary button color */
             color: white;
        }
         body.dark-mode .form-actions a.btn {
             background-color: #5a6268;
         }
        .form-actions a.btn:hover {
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
              .form-content h1 {
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

              .add-form {
                  padding: 20px;
              }
              .form-actions {
                  flex-direction: column;
                  gap: 10px;
              }
               .form-actions button,
               .form-actions a.btn {
                   width: 100%;
                   justify-content: center; /* Center text/icon in stacked buttons */
               }
         }
         @media (max-width: 480px) {
              .form-content {
                  padding: 20px 10px;
              }
              .add-form {
                  padding: 15px;
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

    <div class="form-content">
        <h1>Add New Depart Record</h1>

        <?php
        // Display success message if it exists in the session
        if (isset($_SESSION['form_success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['form_success']) . '</div>';
            unset($_SESSION['form_success']); // Clear the message after displaying
        }
        // Display error message if it exists in the session
        if (isset($_SESSION['form_error'])) {
            echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['form_error']) . '</div>';
            unset($_SESSION['form_error']); // Clear the message after displaying
        }
        ?>

        <form method="post" action="" class="add-form">
            <label for="num_ordre">Num D'Ordre:</label>
            <input type="number" id="num_ordre" name="num_ordre" value="<?= htmlspecialchars($num_ordre) ?>" required><br>

            <label for="date_envoi">Date d'envoi:</label>
            <input type="date" id="date_envoi" name="date_envoi" value="<?= htmlspecialchars($date_envoi) ?>" required><br>

<label for="destinataire_id">Destinataire:</label>
<select id="destinataire_id" name="destinataire_id" required>
    <option value="">-- Sélectionnez un destinataire --</option>
    <?php
    foreach ($destinataire_options as $org) {
        $selected = ($destinataire == $org['id']) ? 'selected' : '';
        echo "<option value=\"" . htmlspecialchars($org['id']) . "\" $selected>" . 
             htmlspecialchars($org['division_fr']) . "</option>";
    }
    ?>
</select>

            <label for="objet">Objet:</label>
            <textarea id="objet" name="objet" required><?= htmlspecialchars($objet) ?></textarea><br>

            <label for="division">Division:</label>
             <select id="division" name="division" required>
                 <option value="">-- Sélectionnez une division --</option>
                 <?php
                 // Populate division options from the fetched data
                 foreach ($division_options as $option) {
                     $selected = ($division === $option) ? 'selected' : '';
                     echo "<option value=\"" . htmlspecialchars($option) . "\" $selected>" . htmlspecialchars($option) . "</option>";
                 }
                 // If you want an "Other" option to manually enter a new one, you'd need JS
                 // <option value="other">-- Autre (Entrez manuellement) --</option>
                 ?>
            </select><br>

             <label for="responsable">Responsable:</label>
            <input type="text" id="responsable" name="responsable" value="<?= htmlspecialchars($responsable) ?>" required><br>

             <label for="observations">Observations:</label>
            <textarea id="observations" name="observations"><?= htmlspecialchars($observations) ?></textarea><br>

            <div class="checkbox-container">
                <input type="checkbox" id="important" name="important" value="1" <?= $important ? 'checked' : '' ?>>
                <label for="important">Important</label>
            </div>

            <div class="form-actions">
                <button type="submit">Add Record</button>
                <a href="list.php" class="btn btn-secondary">Back to List</a>
            </div>
        </form>
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
                  <p id="morocco-fact">Did you know? Al Hoceima is known for its beautiful beaches and Rif Mountains.</p>
              </div>
          </div>
      </div>

    <script>
         // Your JavaScript for dark mode and time/facts goes here
          document.addEventListener('DOMContentLoaded', function() {
              initDarkMode();
              initTimeAndFacts();
              // Add any JS needed for 'Other' destinataire/division options if you implement them
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