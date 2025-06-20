<?php
include 'db.php'; // Make sure db.php exists and has your database connection details

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $updates = [];

    foreach ($_POST as $key => $value) {
        if ($key != 'id') {
            $updates[] = "`" . $conn->real_escape_string($key) . "` = '" . $conn->real_escape_string($value) . "'";
        }
    }

    if (!empty($updates)) {
        $sql = "UPDATE organizations SET " . implode(', ', $updates) . " WHERE id = $id";
        if ($conn->query($sql)) {
            header("Location: organizations.php");
            exit();
        } else {
            die("Error updating record: " . $conn->error);
        }
    } else {
        // No updates submitted, just redirect
        header("Location: organizations.php");
        exit();
    }
}

// Show form
if (!isset($_GET['id'])) {
    die("Missing Organization ID.");
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM organizations WHERE id = $id");
if (!$result || $result->num_rows == 0) {
    die("Organization record not found.");
}
$row = $result->fetch_assoc();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Organization #<?php echo $id; ?> | Al Hoceima Province</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Edit organization details for Al Hoceima Province administration">
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
        }
        body.dark-mode {
            --bg-color: #121212;
            --text-color: #f0f0f0;
            --card-color: #1e1e1e;
            --primary-color: #4a90e2;
            --secondary-color: #8a2be2;
            --box-shadow: 0 4px 12px rgba(0,0,0,0.2);
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
        }
        .container {
            max-width: 800px; /* Adjusted container width for form */
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Header Styles */
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
            min-height: calc(100vh - var(--header-height));
        }
        .card { /* Using card style for the form container */
            background: var(--card-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .card h1 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--primary-color);
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px; /* Increased space */
        }
        label {
            display: block;
            font-weight: 600; /* Slightly bolder */
            margin-bottom: 8px; /* Increased space */
            color: var(--text-color);
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px; /* Increased padding */
            border: 1px solid #ccc;
            border-radius: var(--border-radius); /* Apply border-radius */
            font-size: 1rem;
            background-color: var(--bg-color); /* Match background */
            color: var(--text-color); /* Match text color */
            transition: border-color 0.3s, box-shadow 0.3s;
        }
         input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(37, 117, 252, 0.3); /* Subtle focus effect */
        }
        .button-group {
            display: flex;
            gap: 15px; /* Space between buttons */
            justify-content: center; /* Center buttons */
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px; /* Increased padding */
            background: var(--gradient);
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem; /* Increased font size */
            text-align: center;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .btn-cancel {
            background: #f44336; /* Red color for cancel */
            background: linear-gradient(135deg, #f44336, #e57373); /* Gradient for cancel */
        }

        /* Footer Styles */
        footer {
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            background: var(--card-color);
            color: var(--text-color);
            box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
            position: relative; /* Needed for potential fixed footer */
            bottom: 0;
            width: 100%;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card {
                padding: 20px;
            }
            .card h1 {
                font-size: 1.5rem;
            }
            .btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
             .button-group {
                flex-direction: column; /* Stack buttons on small screens */
                gap: 10px;
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
        <a href="indexadmin.html" class="btn-icon" title="Back to Home" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">
            <i class="fas fa-home"></i>
        </a>
    </div>
</div>
</header>

<main class="main-content">
    <div class="container">
        <div class="card">
            <h1>Edit Organization #<?php echo htmlspecialchars($id); ?></h1>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <?php foreach ($row as $key => $value): ?>
                    <?php if ($key != 'id'): ?>
                        <div class="form-group">
                            <label for="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $key))); ?></label>
                            <input type="text" name="<?php echo htmlspecialchars($key); ?>" id="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>" required>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <div class="button-group">
                    <button type="submit" class="btn">Save Changes</button>
                    <a href="organizations.php" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Al Hoceima Province Administration. All rights reserved.</p>
    </div>
</footer>

<script>
    // Initialize dark mode
    document.addEventListener('DOMContentLoaded', function() {
        initDarkMode();
    });

    function initDarkMode() {
        const modeToggle = document.getElementById('modeToggle');

        // Check for saved mode preference
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            modeToggle.querySelector('i').classList.replace('fa-moon', 'fa-sun');
        }

        // Toggle dark mode
        modeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const icon = modeToggle.querySelector('i');

            if (document.body.classList.contains('dark-mode')) {
                icon.classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('darkMode', 'disabled');
            }
        });
    }
</script>
</body>
</html>