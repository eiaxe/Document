<?php
// MUST BE AT VERY TOP - NO WHITESPACE BEFORE
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require '../db.php'; // Make sure this path is correct relative to edit.php

// Fetch department options from organizations table
$organizations = [];
$organizations_result = $conn->query("SELECT id, division_fr FROM organizations ORDER BY division_fr ASC");
if ($organizations_result) {
    while($row = $organizations_result->fetch_assoc()) {
        $organizations[] = $row;
    }
    $organizations_result->free();
} else {
    $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Error fetching organizations: " . $conn->error;
}

// Fetch division options from divisions table
$divisions_list = [];
$divisions_result = $conn->query("SELECT id, name FROM divisions ORDER BY name ASC");
if ($divisions_result) {
    while($row = $divisions_result->fetch_assoc()) {
        $divisions_list[] = $row;
    }
    $divisions_result->free();
} else {
    $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Error fetching divisions: " . $conn->error;
}

// Fetch signataires from signataire table
$signataires_list = [];
$signataires_result = $conn->query("SELECT id, name FROM signataire ORDER BY name ASC");
if ($signataires_result) {
    while($row = $signataires_result->fetch_assoc()) {
        $signataires_list[] = $row;
    }
    $signataires_result->free();
} else {
    $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Error fetching signataires: " . $conn->error;
}

// Fetch unique observations for dropdown
$observations_distinct_list = [];
$observations_distinct_res = $conn->query("SELECT DISTINCT observations FROM visa WHERE observations IS NOT NULL AND observations != '' ORDER BY observations ASC");
if ($observations_distinct_res) {
    while($row = $observations_distinct_res->fetch_assoc()) {
        $observations_distinct_list[] = $row['observations'];
    }
    $observations_distinct_res->free();
}

// --- Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'])) {
        $_SESSION['form_error'] = "Record ID missing for update.";
        header("Location: list.php");
        exit();
    }

    $id = (int)$_POST['id'];

    // Basic validation
    if (empty($_POST['reference']) || empty($_POST['date_envoi']) || empty($_POST['departement_origine']) ||
        empty($_POST['departement_cible']) || empty($_POST['n_reception']) || empty($_POST['date_reception']) ||
        empty($_POST['num_ordre']) || empty($_POST['date_depart']) || empty($_POST['objet']) ||
        empty($_POST['division']) || empty($_POST['signataire_province']) || empty($_POST['observations'])) {
         $_SESSION['form_error'] = "All fields are required.";
         header("Location: edit.php?id=" . urlencode($id));
         exit();
    }

    // Prepare update statement
    $stmt = $conn->prepare("UPDATE visa SET
        reference = ?,
        date_envoi = ?,
        departement_origine = ?,
        departement_cible = ?,
        n_reception = ?,
        date_reception = ?,
        num_ordre = ?,
        date_depart = ?,
        objet = ?,
        division = ?,
        signataire_province = ?,
        observations = ?,
        important = ?
        WHERE id = ?");

    if ($stmt === false) {
         $_SESSION['form_error'] = "Database update query preparation failed: " . $conn->error;
         header("Location: list.php");
         exit();
    }

    $important = isset($_POST['important']) ? 1 : 0;
    $bind_types = "issssissssssii";

    if ($stmt->bind_param($bind_types,
        $_POST['reference'],
        $_POST['date_envoi'],
        $_POST['departement_origine'],
        $_POST['departement_cible'],
        $_POST['n_reception'],
        $_POST['date_reception'],
        $_POST['num_ordre'],
        $_POST['date_depart'],
        $_POST['objet'],
        $_POST['division'],
        $_POST['signataire_province'],
        $_POST['observations'],
        $important,
        $id
    ) === false) {
         $_SESSION['form_error'] = "Database bind_param failed: " . $stmt->error;
         header("Location: edit.php?id=" . urlencode($id));
         exit();
    }

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['form_success'] = "Record updated successfully!";
        } else {
            $_SESSION['form_success'] = "Record found, but no changes were made.";
        }
    } else {
        $_SESSION['form_error'] = "Error updating record: " . $stmt->error;
        header("Location: edit.php?id=" . urlencode($id));
        exit();
    }

    $stmt->close();
    $conn->close();

    header("Location: list.php");
    exit();
}

// --- Display Form (GET Request or POST failure) ---
if (!isset($_GET['id'])) {
    $_SESSION['form_error'] = "No record ID specified for editing.";
    header("Location: list.php");
    exit();
}

$id = (int)$_GET['id'];

// Fetch the record to be edited
if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM visa WHERE id = ?");
    if ($stmt === false) {
        $_SESSION['form_error'] = "Database query preparation failed: " . $conn->error;
        header("Location: list.php");
        exit();
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    $stmt->close();

    if (!$record) {
        $_SESSION['form_error'] = "Record not found with ID " . htmlspecialchars($id) . ".";
        header("Location: list.php");
        exit();
    }
} else {
     $_SESSION['form_error'] = (isset($_SESSION['form_error']) ? $_SESSION['form_error'] . "<br>" : "") . "Database connection failed.";
     header("Location: list.php");
     exit();
}

if ($conn && $conn->ping()) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Visa Record | Al Hoceima Employee Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Your existing CSS styles here */
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
             --button-bg: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
             --button-hover-bg: linear-gradient(135deg, #9b4ee4, #5a9ce6);
             --table-header-bg: #2a60a0;
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
             padding-bottom: 100px;
         }
          .container {
             max-width: 1200px;
             margin: 0 auto;
             padding: 0 20px;
         }
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
             height: 30px;
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
             font-size: 1.1rem;
         }
         .btn-icon:hover {
             transform: scale(1.1);
         }
         .dashboard-content {
             padding: 40px 20px;
             max-width: 800px;
             margin: 0 auto;
         }
          .dashboard-content h2 {
             text-align: center;
             margin-bottom: 30px;
             font-size: 2rem;
             color: var(--primary-color);
          }
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
             flex: 1;
             margin-bottom: 0;
         }
         .form-group label {
             display: block;
             margin-bottom: 8px;
             font-weight: 600;
             color: var(--text-color);
         }
         .form-control {
             width: 100%;
             padding: 10px;
             border: 1px solid var(--input-border);
             border-radius: var(--border-radius);
             font-size: 1rem;
             color: var(--text-color);
             background-color: var(--bg-color);
             transition: border-color 0.3s, box-shadow 0.3s;
         }
          body.dark-mode .form-control {
              background-color: #2a2a2a;
              color: var(--text-color);
              border-color: var(--input-border);
          }
         .form-control:focus {
             outline: none;
             border-color: var(--input-focus-border);
             box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.1);
         }
          body.dark-mode .form-control:focus {
               box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
          }
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
             content: '\f00c';
             font-family: 'Font Awesome 6 Free';
             font-weight: 900;
             color: white;
             position: absolute;
             top: 50%;
             left: 50%;
             transform: translate(-50%, -50%);
             font-size: 14px;
         }
         .form-actions {
             margin-top: 30px;
             display: flex;
             justify-content: flex-end;
             gap: 15px;
         }
         .btn {
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
         }
         .btn-primary {
             background: var(--gradient);
             color: white;
         }
         .btn-primary:hover {
             background: var(--button-hover-bg);
             transform: translateY(-2px);
         }
         .btn-secondary {
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
         .interactive-widget {
             background: var(--card-color);
             padding: 20px 0;
             box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
             text-align: center;
             position: fixed;
             bottom: 0;
             left: 0;
             right: 0;
             width: 100%;
             z-index: 99;
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
              .form-row {
                  flex-direction: column;
                  gap: 15px;
              }
              .form-actions {
                  flex-direction: column;
                  gap: 10px;
                  align-items: stretch;
               }
               .form-actions .btn {
                   width: 100%;
                   text-align: center;
               }
         }
          @media (max-width: 480px) {
               .dashboard-form {
                   padding: 20px;
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
</head>
<body class="">

<header>
<div class="header-content container">
    <div class="logo">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2c/Flag_of_Morocco.svg/1200px-Flag_of_Morocco.svg.png" alt="Morocco Flag" />
        <span>Al Hoceima Employee Portal</span>
    </div>
    <div style="display:flex; align-items:center; gap:10px;">
        <button class="btn-icon" id="modeToggle"><i class="fas fa-moon"></i></button>
        <a href="../index.html" class="btn-icon" title="Back to Home" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">
            <i class="fas fa-home"></i>
        </a>
    </div>
</div>
</header>

    <div class="dashboard-content">
        <h2 class="translate" data-key="edit_visa">Edit Visa Record</h2>

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
            <input type="hidden" name="id" value="<?= htmlspecialchars($record['id']) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label class="translate" data-key="reference">Reference</label>
                    <input type="number" name="reference" class="form-control"
                           value="<?= htmlspecialchars($record['reference']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="translate" data-key="date_envoi">Date d'envoi</label>
                    <input type="date" name="date_envoi" class="form-control"
                           value="<?= htmlspecialchars($record['date_envoi']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="translate" data-key="departement_origine">Departement d'origine</label>
                     <select name="departement_origine" class="form-control" required>
                         <option value="">-- Select --</option>
                         <?php foreach ($organizations as $org): ?>
                             <option value="<?= htmlspecialchars($org['id']) ?>"
                                 <?= ($org['id'] == $record['departement_origine']) ? 'selected' : '' ?>>
                                 <?= htmlspecialchars($org['division_fr']) ?>
                             </option>
                         <?php endforeach; ?>
                     </select>
                </div>

                <div class="form-group">
                    <label class="translate" data-key="departement_cible">Departement cible</label>
                    <select name="departement_cible" class="form-control" required>
                        <option value="">-- Select --</option>
                         <?php foreach ($organizations as $org): ?>
                             <option value="<?= htmlspecialchars($org['id']) ?>"
                                 <?= ($org['id'] == $record['departement_cible']) ? 'selected' : '' ?>>
                                 <?= htmlspecialchars($org['division_fr']) ?>
                             </option>
                         <?php endforeach; ?>
                     </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="translate" data-key="n_reception">N de reception</label>
                    <input type="number" name="n_reception" class="form-control"
                           value="<?= htmlspecialchars($record['n_reception']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="translate" data-key="date_reception">Date de reception</label>
                    <input type="date" name="date_reception" class="form-control"
                           value="<?= htmlspecialchars($record['date_reception']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="translate" data-key="num_ordre">Num d'ordre</label>
                    <input type="number" name="num_ordre" class="form-control"
                           value="<?= htmlspecialchars($record['num_ordre']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="translate" data-key="date_depart">Date depart</label>
                    <input type="date" name="date_depart" class="form-control"
                           value="<?= htmlspecialchars($record['date_depart']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="translate" data-key="objet">Objet</label>
                    <input type="text" name="objet" class="form-control"
                           value="<?= htmlspecialchars($record['objet']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="translate" data-key="division">Division</label>
                    <select name="division" class="form-control" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($divisions_list as $division): ?>
                            <option value="<?= htmlspecialchars($division['id']) ?>"
                                <?= ($division['id'] == $record['division']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($division['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="translate" data-key="signataire_province">Signataire province</label>
                    <select name="signataire_province" class="form-control" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($signataires_list as $signataire): ?>
                            <option value="<?= htmlspecialchars($signataire['id']) ?>"
                                <?= ($signataire['id'] == $record['signataire_province']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($signataire['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="translate" data-key="observations">Observations</label>
                    <select name="observations" class="form-control" required>
                        <option value="">-- Select --</option>
                         <?php foreach ($observations_distinct_list as $obs): ?>
                             <option value="<?= htmlspecialchars($obs) ?>"
                                 <?= ($obs == $record['observations']) ? 'selected' : '' ?>>
                                 <?= htmlspecialchars($obs) ?>
                             </option>
                         <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="important" <?= $record['important'] ? 'checked' : '' ?>>
                    <span class="translate" data-key="important">Important</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary translate" data-key="save">Save</button>
                <a href="list.php" class="btn btn-secondary translate" data-key="cancel">Cancel</a>
            </div>
        </form>
    </div>

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
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initTimeAndFacts();
        });

        function initDarkMode() {
            const modeToggle = document.getElementById('modeToggle');
            const savedMode = localStorage.getItem('alhoceima_darkMode');

            if (savedMode === 'enabled') {
                document.body.classList.add('dark-mode');
                const icon = modeToggle.querySelector('i');
                if (icon.classList.contains('fa-moon')) {
                     icon.classList.replace('fa-moon', 'fa-sun');
                }
            }

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

            updateTime();
            setInterval(updateTime, 1000);

            showRandomFact();
            setInterval(showRandomFact, 10000);
        }
    </script>

</body>
</html>