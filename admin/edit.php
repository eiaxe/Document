<?php
session_start();
include '../db.php'; // Make sure this path is correct

if (!$conn) {
    $_SESSION['error'] = "Database connection failed in edit.php: " . mysqli_connect_error();
    header("Location: list.php");
    exit();
}

$document = false; // Initialize $document outside the POST block
$destinataires_list = []; // Initialize arrays for dropdown options
$divisions_list = [];

// --- Fetch lists for dropdowns from the database ---
// Assuming you have tables named 'destinataires' and 'divisions'
// And each table has a column that you want to use for the option value and text
// Replace 'name' with the actual column name in your tables
$organizations_list = [];
$org_result = $conn->query("SELECT id, division_fr FROM organizations ORDER BY division_fr ASC");
if ($org_result) {
    while ($row = $org_result->fetch_assoc()) {
        $organizations_list[] = $row;
    }
    $org_result->free();
} else {
    $_SESSION['error'] = "Error fetching organizations list: " . $conn->error;
}

$divisions_result = $conn->query("SELECT name FROM divisions ORDER BY name");
if ($divisions_result) {
    while ($row = $divisions_result->fetch_assoc()) {
        $divisions_list[] = $row['name'];
    }
    $divisions_result->free();
} else {
    $_SESSION['error'] = (isset($_SESSION['error']) ? $_SESSION['error'] . "<br>" : "") . "Error fetching divisions list: " . $conn->error;
    // Continue, but the dropdown will be empty or show an error message
}


if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // --- Handle POST Request (Form Submission) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Basic validation - You can add more robust checks
        // Get ID from hidden field in POST for validation consistency
        $posted_id = isset($_POST['id']) ? $_POST['id'] : null;

        // Basic validation
        if (empty($posted_id) || $posted_id != $id || empty($_POST['reference_number']) || empty($_POST['destinataire']) || empty($_POST['date_envoi']) || empty($_POST['num_ordre']) || empty($_POST['date_arrivee']) || empty($_POST['objet']) || empty($_POST['division'])) {
             $_SESSION['error'] = "Invalid request or all fields are required.";
             // Redirect back to the edit page with the original GET ID
             header("Location: edit.php?id=" . urlencode($id));
             exit();
        }

        $reference_number = $_POST['reference_number'];
        $destinataire = $_POST['destinataire'];
        $date_envoi = $_POST['date_envoi'];
        $num_ordre = $_POST['num_ordre'];
        $date_arrivee = $_POST['date_arrivee'];
        $objet = $_POST['objet'];
        $division = $_POST['division']; // Get division from POST
        $important = isset($_POST['important']) ? 1 : 0;

        // Update the document
        $update_stmt = $conn->prepare("UPDATE submissions SET reference_number=?, destinataire=?, date_envoi=?, num_ordre=?, date_arrivee=?, objet=?, division=?, important=? WHERE id=?");

        if ($update_stmt === false) {
             $_SESSION['error'] = "Database update query preparation failed: " . $conn->error;
        } else {
            // Bind parameters (check types match your DB schema)
            // Assumes: i, s, s, i, s, s, s, i, i
            $bind_success = $update_stmt->bind_param("ississsii", $reference_number, $destinataire, $date_envoi, $num_ordre, $date_arrivee, $objet, $division, $important, $id);

            if ($bind_success === false) {
                $_SESSION['error'] = "Database bind_param failed: " . $update_stmt->error;
            } else {
                if ($update_stmt->execute()) {
                    $_SESSION['message'] = "Document updated successfully.";
                    header("Location: list.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Error updating document: " . $update_stmt->error;
                }
            }
            $update_stmt->close();
        }
    }

    // --- Fetch the existing document data (for initial form display or after POST error) ---
    // This block is only executed if it's a GET request or if the POST failed validation
    $stmt = $conn->prepare("SELECT id, reference_number, destinataire, date_envoi, num_ordre, date_arrivee, objet, division, important FROM submissions WHERE id = ?");

     if ($stmt === false) {
        $_SESSION['error'] = (isset($_SESSION['error']) ? $_SESSION['error'] . "<br>" : "") . "Database select query preparation failed: " . $conn->error;
     } else {
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->errno) {
             $_SESSION['error'] = (isset($_SESSION['error']) ? $_SESSION['error'] . "<br>" : "") . "Error fetching document data: " . $stmt->error;
        } else {
            $result = $stmt->get_result();
            $document = $result->fetch_assoc();

            if (!$document) {
                 $_SESSION['error'] = (isset($_SESSION['error']) ? $_SESSION['error'] . "<br>" : "") . 'No document found with that ID.';
                 header("Location: list.php");
                 exit();
            }
        }
        $stmt->close();
     }

} else {
    $_SESSION['error'] = (isset($_SESSION['error']) ? $_SESSION['error'] . "<br>" : "") . 'No document ID provided.';
    header("Location: list.php");
    exit();
}

// Close the database connection (optional but good practice)
// Only close if the script hasn't exited yet
if ($conn && $conn->ping()) {
    $conn->close();
}


// --- HTML Form Display ---
// Only display the form if a document was successfully fetched
if ($document):
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Submission | Al Hoceima Employee Portal</title>
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

        /* Main Content */
        .main-content {
            padding: 40px 0;
        }
        .add-card { /* Using .add-card for consistency with your CSS */
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
             cursor: pointer; /* Indicate it's clickable */
         }


        .button-group {
            display: flex;
            gap: 10px; /* Space between buttons */
            justify-content: flex-end; /* Align buttons to the right */
            margin-top: 30px;
        }

        .btn-submit,
        .btn-cancel { /* Style both submit and cancel buttons similarly */
            display: inline-block; /* Allow them to sit side-by-side */
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

        .btn-submit {
             background: var(--button-bg);
             color: white;
        }
        .btn-submit:hover {
            background: var(--button-hover-bg);
             transform: translateY(-2px);
             box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .btn-cancel {
            background-color: #6c757d; /* Bootstrap secondary color */
            color: white;
        }
         body.dark-mode .btn-cancel {
             background-color: #5a5f66;
         }

        .btn-cancel:hover {
            background-color: #5a6268;
             transform: translateY(-2px);
             box-shadow: 0 8px 20px rgba(0,0,0,0.1);
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
             .button-group {
                 flex-direction: column; /* Stack buttons on small screens */
                 gap: 10px;
             }
             .btn-submit, .btn-cancel {
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

    <div class="main-content container">
        <div class="add-card"> <!-- Use .add-card for the form container -->
            <h1>Edit Document</h1>

            <?php
            // Display session messages
            if (isset($_SESSION['error'])) {
                echo "<p class='message error-message'>" . $_SESSION['error'] . "</p>";
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['message'])) {
                echo "<p class='message success-message'>" . $_SESSION['message'] . "</p>";
                unset($_SESSION['message']);
            }
            ?>

            <form method="POST" action="">
                <!-- Hidden input to keep the ID -->
                <input type="hidden" name="id" value="<?= htmlspecialchars($document['id']) ?>">

                <div class="form-group">
                    <label for="reference_number">Reference Number:</label>
                    <input type="number" id="reference_number" name="reference_number" value="<?= htmlspecialchars($document['reference_number']) ?>" required>
                </div>

<div class="form-group">
    <label for="destinataire_id">Destinataire:</label>
    <select id="destinataire_id" name="destinataire_id" required>
        <option value="">-- Select Destinataire --</option>
        <?php
        // Loop through the fetched organizations list
        foreach ($organizations_list as $org) {
            $selected = $document['destinataire_id'] == $org['id'] ? "selected" : "";
            echo "<option value=\"" . htmlspecialchars($org['id']) . "\" $selected>" . 
                 htmlspecialchars($org['division_fr']) . "</option>";
        }
        ?>
    </select>
</div>

                <div class="form-group">
                    <label for="date_envoi">Date d'envoi:</label>
                    <input type="date" id="date_envoi" name="date_envoi" value="<?= htmlspecialchars($document['date_envoi']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="num_ordre">Num D'Ordre:</label>
                    <input type="number" id="num_ordre" name="num_ordre" value="<?= htmlspecialchars($document['num_ordre']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="date_arrivee">Date d'arrivee:</label>
                    <input type="date" id="date_arrivee" name="date_arrivee" value="<?= htmlspecialchars($document['date_arrivee']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="objet">Objet:</label>
                    <textarea id="objet" name="objet" required><?= htmlspecialchars($document['objet']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="division">Division:</label>
                    <select id="division" name="division" required>
                         <option value="">-- Select Division --</option>
                         <?php
                         // Loop through the fetched divisions list
                         foreach ($divisions_list as $div) {
                             $selected = $document['division'] == $div ? "selected" : "";
                             echo "<option value=\"" . htmlspecialchars($div) . "\" $selected>" . htmlspecialchars($div) . "</option>";
                         }
                         ?>
                    </select>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="important" name="important" value="1" <?= $document['important'] ? 'checked' : '' ?>>
                    <label for="important">Important</label>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-submit">Update Document</button>
                    <a href="list.php" class="btn-cancel">Cancel</a>
                </div>

            </form>
        </div> <!-- Close .add-card -->
    </div> <!-- Close .main-content -->


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

<?php endif; // End of the if ($document) check ?>