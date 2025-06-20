<?php
session_start(); // Start session for messages
include '../db.php'; // Correct path to db.php

// Initialize variables
$error_message = '';
$success_message = '';
$organizations = [];
$divisions = [];

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

// Fetch Divisions for dropdown (using name as in your second query)
$sql_div = "SELECT id, name FROM divisions ORDER BY name ASC";
$result_div = $conn->query($sql_div);
if ($result_div && $result_div->num_rows > 0) {
    while ($row = $result_div->fetch_assoc()) {
        $divisions[] = $row;
    }
} else {
    $error_message = "Could not fetch divisions from the database.";
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data with sanitization
    $reference_number = trim($_POST['reference_number'] ?? '');
    $destinataire_id = intval($_POST['destinataire_id'] ?? 0);
    $date_envoi = $_POST['date_envoi'] ?? null;
    $num_ordre = !empty($_POST['num_ordre']) ? intval($_POST['num_ordre']) : null;
    $date_arrivee = $_POST['date_arrivee'] ?? null;
    $objet = trim($_POST['objet'] ?? '');
    $division_id = intval($_POST['division_id'] ?? 0);
    $important = isset($_POST['important']) ? 1 : 0;

    // Validate required fields
    $missing_fields = [];
    if (empty($reference_number)) $missing_fields[] = "Reference Number";
    if ($destinataire_id <= 0) $missing_fields[] = "Destinataire";
    if (empty($objet)) $missing_fields[] = "Objet";
    if ($division_id <= 0) $missing_fields[] = "Division";
    if ($num_ordre === null) $missing_fields[] = "Num D'Ordre";

    if (!empty($missing_fields)) {
        $error_message = "Required fields missing: " . implode(", ", $missing_fields);
    } 
    // Validate date formats if provided
    elseif (!empty($date_envoi) && !DateTime::createFromFormat('Y-m-d', $date_envoi)) {
        $error_message = "Invalid Date d'envoi format (use YYYY-MM-DD)";
    }
    elseif (!empty($date_arrivee) && !DateTime::createFromFormat('Y-m-d', $date_arrivee)) {
        $error_message = "Invalid Date d'arrivee format (use YYYY-MM-DD)";
    }
    // Validate field lengths
    elseif (strlen($reference_number) > 50) {
        $error_message = "Reference Number is too long (max 50 characters)";
    }
    elseif (strlen($objet) > 255) {
        $error_message = "Objet is too long (max 255 characters)";
    } else {
        // Prepare an INSERT statement
        $sql = "INSERT INTO submissions (
            reference_number,
            destinataire_id,
            date_envoi,
            num_ordre,
            date_arrivee,
            objet,
            division_id,
            important
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            $error_message = "Database query preparation failed: " . $conn->error;
        } else {
            // Bind parameters with correct types
            $stmt->bind_param(
                "sisssisi", // Corrected types
                $reference_number,    // s (string)
                $destinataire_id,    // i (integer)
                $date_envoi,         // s (string or NULL)
                $num_ordre,          // i (integer)
                $date_arrivee,       // s (string or NULL)
                $objet,              // s (string)
                $division_id,        // i (integer)
                $important           // i (integer)
            );

            // Execute the statement
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Submission added successfully!";
                // Redirect to prevent form resubmission
                header("Location: list.php");
                exit();
            } else {
                // Check for unique constraint violation
                if ($conn->errno == 1062) {
                    $error_message = "Error: The Num D'Ordre already exists. Please use a different number.";
                } else {
                    $error_message = "Database error: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Submission | Al Hoceima Employee Portal</title>
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Table Styles */
.data-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 0.9em;
    background: var(--card-color);
    box-shadow: var(--box-shadow);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.data-table thead tr {
    background: var(--gradient);
    color: white;
    text-align: left;
}

.data-table th,
.data-table td {
    padding: 12px 15px;
    border-bottom: 1px solid var(--input-border);
}

.data-table tbody tr:last-of-type td {
    border-bottom: none;
}

.data-table tbody tr:hover {
    background-color: rgba(106, 17, 203, 0.1);
}

/* Action buttons */
.action-btn {
    padding: 5px 10px;
    border-radius: 4px;
    text-decoration: none;
    color: white;
    margin-right: 5px;
    font-size: 0.8em;
    transition: all 0.3s;
}

.edit-btn {
    background-color: #4CAF50;
}

.delete-btn {
    background-color: #f44336;
}

.view-btn {
    background-color: #2196F3;
}

.action-btn:hover {
    opacity: 0.8;
    transform: translateY(-1px);
}
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
        .add-card {
            background: var(--card-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        .add-card h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            color: var(--primary-color);
        }
        .form-group {
            margin-bottom: 20px;
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
         .form-group textarea {
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
         .form-group textarea:focus {
             outline: none;
             border-color: var(--input-focus-border);
             box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.1);
         }
         .form-group textarea {
             min-height: 100px;
             resize: vertical;
         }

         .checkbox-group {
             display: flex;
             align-items: center;
             margin-bottom: 20px;
         }
         .checkbox-group input[type="checkbox"] {
             margin-right: 10px;
             width: 20px;
             height: 20px;
             accent-color: var(--primary-color); /* Style the checkbox */
         }
         .checkbox-group label {
             margin-bottom: 0;
             font-weight: 400;
         }


        .btn-submit {
            display: block;
            width: 100%;
            padding: 12px;
            background: var(--button-bg);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
             text-align: center;
             text-decoration: none; /* If using as a link */
        }
        .btn-submit:hover {
            background: var(--button-hover-bg);
             transform: translateY(-2px);
             box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            font-size: 0.95rem;
             font-weight: 500;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
         body.dark-mode .success-message {
             background-color: #1f3a2e;
             color: #a3e6b3;
             border-color: #284d38;
         }
         body.dark-mode .error-message {
             background-color: #4a252c;
             color: #f5a9b8;
             border-color: #6a3a4a;
         }

         .back-link {
             display: inline-block;
             margin-top: 20px;
             color: var(--primary-color);
             text-decoration: none;
             font-weight: 500;
             transition: color 0.2s;
         }
         .back-link:hover {
             color: var(--secondary-color);
             text-decoration: underline;
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
        @media (max-width: 768px) {
             .add-card {
                 padding: 20px;
             }
             .add-card h1 {
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
             }
             .cloud {
                 width: 40px;
                 top: 25px;
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
        <div class="add-card">
            <h1>Add New Submission</h1>

            <?php
            // Display messages
            if (isset($_SESSION['success_message'])) {
                echo "<div class='message success-message'>" . $_SESSION['success_message'] . "</div>";
                unset($_SESSION['success_message']); // Clear the message after displaying
            }
            if (isset($error_message)) {
                echo "<div class='message error-message'>" . $error_message . "</div>";
            }
            ?>

            <form action="add.php" method="post">
                <div class="form-group">
                    <label for="reference_number">Reference Number:</label>
                    <input type="text" id="reference_number" name="reference_number" required>
                </div>

<div class="form-group">
    <label for="destinataire_id">Destinataire:</label>
    <select id="destinataire" name="destinataire_id" required>
        <option value="">Select Destinataire</option>
        <?php foreach ($organizations as $org): ?>
            <option value="<?= $org['id'] ?>"><?= htmlspecialchars($org['division_fr']) ?></option>
        <?php endforeach; ?>
    </select>
                </div>

                <div class="form-group">
                    <label for="date_envoi">Date d'envoi:</label>
                    <input type="date" id="date_envoi" name="date_envoi">
                </div>

                <div class="form-group">
                    <label for="num_ordre">Num D'Ordre:</label>
                    <input type="number" id="num_ordre" name="num_ordre" required>
                </div>

                <div class="form-group">
                    <label for="date_arrivee">Date d'arrivee:</label>
                    <input type="date" id="date_arrivee" name="date_arrivee">
                </div>

                <div class="form-group">
                    <label for="objet">Objet:</label>
                    <textarea id="objet" name="objet" required></textarea>
                </div>

                 <div class="form-group">
                    <label for="division_id">Division:</label>
                     <select id="division" name="division_id" required>
                         <option value="">Select Division</option>
                         <?php foreach ($divisions as $div): ?>
                             <option value="<?= $div['id'] ?>"><?= htmlspecialchars($div['name']) ?></option>
                         <?php endforeach; ?>
                     </select>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="important" name="important">
                    <label for="important">Important</label>
                </div>

                <button type="submit" class="btn-submit">Add Submission</button>
            </form>

            <a href="list.php" class="back-link">Back to List</a>
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