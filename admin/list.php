<?php
session_start(); // Start session if needed for messages or user state
include '../db.php'; // Correct path to db.php

// Debug connection
if (!$conn) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search inputs from URL
$destinataire_id = $_GET['destinataire'] ?? ''; // Use ID now
$reference = $_GET['reference'] ?? '';
$objet = $_GET['objet'] ?? '';
$date_envoi_start = $_GET['date_envoi_start'] ?? '';
$date_envoi_end = $_GET['date_envoi_end'] ?? '';
$date_arrivee_start = $_GET['date_arrivee_start'] ?? '';
$date_arrivee_end = $_GET['date_arrivee_end'] ?? '';
$division_id = $_GET['division'] ?? ''; // Use ID now

function fetch_results($conn) {
    global $destinataire_id, $reference, $objet, $date_envoi_start, $date_envoi_end, $date_arrivee_start, $date_arrivee_end, $division_id;

    // Join with organizations and divisions tables to get names
    $sql = "SELECT s.*, o.division_fr AS destinataire_name, d.name AS division_name
            FROM submissions s
            LEFT JOIN organizations o ON s.destinataire_id = o.id
            LEFT JOIN divisions d ON s.division_id = d.id
            WHERE 1";

    // Use destinataire_id and division_id for filtering
    if ($destinataire_id) {
        $sql .= " AND s.destinataire_id = " . $conn->real_escape_string($destinataire_id);
    }
    if ($reference) {
        $sql .= " AND s.reference_number LIKE '%" . $conn->real_escape_string($reference) . "%'";
    }
    if ($objet) {
        $sql .= " AND s.objet LIKE '%" . $conn->real_escape_string($objet) . "%'";
    }
    if ($date_envoi_start && $date_envoi_end) {
        $sql .= " AND s.date_envoi BETWEEN '" . $conn->real_escape_string($date_envoi_start) . "' AND '" . $conn->real_escape_string($date_envoi_end) . "'";
    }
    if ($date_arrivee_start && $date_arrivee_end) {
        $sql .= " AND s.date_arrivee BETWEEN '" . $conn->real_escape_string($date_arrivee_start) . "' AND '" . $conn->real_escape_string($date_arrivee_end) . "'";
    }
     if ($division_id) {
        $sql .= " AND s.division_id = " . $conn->real_escape_string($division_id);
    }

    // Add ordering
    $sql .= " ORDER BY s.date_arrivee DESC, s.num_ordre DESC"; // Example ordering

    $result = $conn->query($sql);
    ob_start();

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $importantIndicator = $row["important"] ? "<i class='fas fa-star important-star'></i>" : ""; // Use Font Awesome icon
            echo "<tr>
                    <td>{$row["id"]}</td>
                    <td>{$row["reference_number"]}</td>
                    <td>" . htmlspecialchars($row["destinataire_name"]) . "</td> <!-- Use fetched name -->
                    <td>{$row["date_envoi"]}</td>
                    <td>{$row["num_ordre"]}</td>
                    <td>{$row["date_arrivee"]}</td>
                    <td>" . htmlspecialchars($row["objet"]) . "</td> <!-- Sanitize output -->
                    <td>" . htmlspecialchars($row["division_name"]) . "</td> <!-- Use fetched name -->
                    <td class='important-cell'>{$importantIndicator}</td>
                    <td class='actions-cell'>
                        <a href='edit.php?id={$row["id"]}' class='btn-action edit-btn'><i class='fas fa-edit'></i> Edit</a>
                        <a href='delete.php?id={$row["id"]}' class='btn-action delete-btn' onclick='return confirm(\"Are you sure you want to delete this submission?\");'><i class='fas fa-trash-alt'></i> Delete</a>
                    </td>
                </tr>";
        }
    } elseif ($result) {
        echo "<tr><td colspan='10' class='no-records'>No records found</td></tr>";
    } else {
         echo "<tr><td colspan='10' class='error-message'>Error fetching records: " . $conn->error . "</td></tr>";
    }

    return ob_get_clean();
}

// Fetch unique organizations for the filter dropdown (from organizations table)
$organizations_filter = [];
$org_filter_result = $conn->query("SELECT id, division_fr FROM organizations ORDER BY division_fr ASC");
if ($org_filter_result) {
    while ($row = $org_filter_result->fetch_assoc()) {
        $organizations_filter[] = $row;
    }
}

// Fetch unique reference_numbers for the filter dropdown
$references_filter = [];
$ref_filter_result = $conn->query("SELECT DISTINCT reference_number FROM submissions ORDER BY reference_number ASC");
if ($ref_filter_result) {
     while ($row = $ref_filter_result->fetch_assoc()) {
        $references_filter[] = $row;
    }
}

// Fetch unique divisions (IDs and Names) for the filter dropdown
$divisions_filter = [];
$div_filter_result = $conn->query("SELECT id, name FROM divisions ORDER BY name ASC");
if ($div_filter_result) {
    while ($row = $div_filter_result->fetch_assoc()) {
        $divisions_filter[] = $row;
    }
}

// If it's an AJAX call, return only the results
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    echo fetch_results($conn);
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>List of Submissions | Al Hoceima Employee Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="List of added submission information">
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
            --table-border: #ddd;
            --table-header-bg: #eef2f7;
            --table-row-hover: #f9f9f9;
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
            --table-border: #444;
            --table-header-bg: #2a2a2a;
            --table-row-hover: #282828;
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
        }
        .btn-icon:hover {
            transform: scale(1.1);
        }

        /* Main Content */
         .main-content {
            padding: 40px 0;
         }
        .list-card {
            background: var(--card-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        .list-card h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            color: var(--primary-color);
        }

        /* Search Form Styles */
        #searchForm {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); /* Responsive grid for filters */
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background-color: var(--bg-color); /* Light background for search filters */
            border-radius: var(--border-radius);
            border: 1px solid var(--table-border);
        }
         body.dark-mode #searchForm {
             background-color: #2a2a2a;
         }
        #searchForm label {
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
            font-size: 0.9rem;
            color: var(--text-color);
        }
         #searchForm input[type="text"],
         #searchForm input[type="date"],
         #searchForm select {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--input-border);
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            color: var(--text-color);
            background-color: var(--card-color);
            transition: border-color 0.3s, box-shadow 0.3s;
         }
         #searchForm input[type="text"]:focus,
         #searchForm input[type="date"]:focus,
         #searchForm select:focus {
             outline: none;
             border-color: var(--input-focus-border);
             box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.1);
         }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid var(--table-border);
            border-radius: var(--border-radius);
            overflow: hidden; /* Ensures border-radius is visible */
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--table-border);
             word-break: break-word; /* Prevent long text overflow */
        }
        th {
            background-color: var(--table-header-bg);
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95rem;
        }
        tbody tr:hover {
            background-color: var(--table-row-hover);
        }
        td {
            font-size: 0.9rem;
            color: var(--text-color);
        }

        /* Specific Column Styles */
        td:first-child { width: 50px; } /* ID */
        td:nth-child(5) { width: 80px; } /* Num D'Ordre */
        td:nth-child(4), td:nth-child(6) { width: 120px; } /* Dates */
        td:nth-child(9) { width: 60px; text-align: center; } /* Important */
        td:last-child { width: 180px; } /* Actions */

        .important-cell {
             text-align: center;
        }
        .important-star {
            color: var(--accent-color); /* Star color */
            font-size: 1.1rem;
        }
        .actions-cell {
            white-space: nowrap; /* Prevent buttons from wrapping */
             text-align: center;
        }

        /* Action Button Styles */
        .btn-action {
            display: inline-block;
            padding: 5px 10px;
            margin: 0 2px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: background-color 0.2s, opacity 0.2s;
        }
        .edit-btn {
            background-color: #5cb85c; /* Green */
            color: white;
        }
         .edit-btn i {
             margin-right: 5px;
         }
        .delete-btn {
            background-color: #d9534f; /* Red */
            color: white;
        }
         .delete-btn i {
             margin-right: 5px;
         }
        .btn-action:hover {
            opacity: 0.9;
        }

        /* No Records Message */
        .no-records, .error-message {
            text-align: center;
            font-style: italic;
            color: var(--text-color);
             opacity: 0.7;
        }
         .error-message {
             color: #d9534f; /* Red */
             font-weight: 600;
         }

        /* Add New Link */
        .add-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 25px;
            background: var(--gradient);
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        .add-link:hover {
             transform: translateY(-2px);
             box-shadow: 0 8px 20px rgba(0,0,0,0.15);
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
             th, td {
                 padding: 10px;
             }
             td {
                 font-size: 0.85rem;
             }
             .actions-cell {
                 text-align: left; /* Align actions left on smaller screens */
             }
             .btn-action {
                 display: block; /* Stack buttons */
                 margin: 5px 0;
                 text-align: center;
             }
             td:last-child {
                 width: auto; /* Allow actions column to shrink */
             }
        }
        @media (max-width: 768px) {
             .list-card {
                 padding: 20px;
             }
             .list-card h1 {
                 font-size: 1.8rem;
             }
             #searchForm {
                 grid-template-columns: 1fr; /* Stack filters vertically */
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
             }
             .cloud {
                 width: 40px;
                 top: 25px;
             }
             /* Hide less important columns on smaller screens */
             th:nth-child(1), td:nth-child(1), /* ID */
             th:nth-child(4), td:nth-child(4), /* Date d'envoi */
             th:nth-child(9), td:nth-child(9) /* Important */
             {
                 display: none;
             }
        }
         @media (max-width: 480px) {
              th, td {
                  padding: 8px;
              }
              td {
                  font-size: 0.8rem;
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

<main class="main-content">
    <div class="container">
        <div class="list-card">
            <h1>Submission Records</h1>

            <!-- Search Inputs -->
            <form id="searchForm">
                 <div class="form-group">
                    <label for="destinataire">Destinataire:</label>
                    <select name="destinataire" id="destinataire">
                        <option value="">-- All --</option>
                        <?php foreach ($organizations_filter as $org): ?>
                            <option value="<?= htmlspecialchars($org['id']) ?>" <?= (string)$org['id'] === $destinataire_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($org['division_fr']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                 </div>

                 <div class="form-group">
                    <label for="division">Division:</label>
                    <select name="division" id="division">
                        <option value="">-- All --</option>
                        <?php foreach ($divisions_filter as $div): ?>
                            <option value="<?= htmlspecialchars($div['id']) ?>" <?= (string)$div['id'] === $division_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($div['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                 </div>

                 <div class="form-group">
                    <label for="reference">Reference:</label>
                    <select name="reference" id="reference">
                        <option value="">-- All --</option>
                        <?php foreach ($references_filter as $row): ?>
                            <option value="<?= htmlspecialchars($row['reference_number']) ?>" <?= $row['reference_number'] === $reference ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['reference_number']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                 </div>

                 <div class="form-group">
                    <label for="objet">Objet (contains):</label>
                    <input type="text" id="objet" name="objet" value="<?= htmlspecialchars($objet) ?>">
                 </div>

                 <div class="form-group">
                    <label for="date_envoi_start">Sent Date (start):</label>
                    <input type="date" id="date_envoi_start" name="date_envoi_start" value="<?= htmlspecialchars($date_envoi_start) ?>">
                 </div>

                 <div class="form-group">
                    <label for="date_envoi_end">Sent Date (end):</label>
                    <input type="date" id="date_envoi_end" name="date_envoi_end" value="<?= htmlspecialchars($date_envoi_end) ?>">
                 </div>

                 <div class="form-group">
                    <label for="date_arrivee_start">Received Date (start):</label>
                    <input type="date" id="date_arrivee_start" name="date_arrivee_start" value="<?= htmlspecialchars($date_arrivee_start) ?>">
                 </div>

                 <div class="form-group">
                    <label for="date_arrivee_end">Received Date (end):</label>
                    <input type="date" id="date_arrivee_end" name="date_arrivee_end" value="<?= htmlspecialchars($date_arrivee_end) ?>">
                 </div>
            </form>

            <!-- Results Table -->
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ref Number</th>
                        <th>Destinataire</th>
                        <th>Sent Date</th>
                        <th>Order Number</th>
                        <th>Received Date</th>
                        <th>Objet</th>
                        <th>Division</th>
                        <th>Imp</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="results">
                    <?= fetch_results($conn); ?>
                </tbody>
            </table>

            <a href="add.php" class="add-link">Add New Submission</a>
        </div>
    </div>
</main>

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

        // Setup AJAX filtering
        setupAjaxFiltering();
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

    function setupAjaxFiltering() {
        const form = document.getElementById('searchForm');
        const results = document.getElementById('results');

        // Function to fetch and update results
        const fetchAndUpdateResults = () => {
             const params = new URLSearchParams(new FormData(form));
             // Add a header to indicate it's an AJAX request, more robust than a GET param
             const headers = new Headers();
             headers.append('X-Requested-With', 'XMLHttpRequest');

             fetch('<?= basename(__FILE__) ?>?' + params.toString(), {
                 headers: headers
             })
                 .then(response => {
                      if (!response.ok) {
                         throw new Error('Network response was not ok');
                      }
                      return response.text();
                 })
                 .then(data => {
                     results.innerHTML = data;
                 })
                 .catch(error => {
                      console.error('Error fetching data:', error);
                      results.innerHTML = '<tr><td colspan="10" class="error-message">Could not load results. Please try again.</td></tr>';
                 });
        };

        // Trigger update on input change
        form.addEventListener('input', fetchAndUpdateResults);

         // Initial fetch on page load (already done by PHP, but good for consistency if you change that)
         // fetchAndUpdateResults();
    }
</script>
</body>
</html>

<?php $conn->close(); ?>