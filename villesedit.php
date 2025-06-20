<?php
include 'db.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $updates = [];

    foreach ($_POST as $key => $value) {
        if ($key != 'id') {
            $updates[] = "`$key` = '" . $conn->real_escape_string($value) . "'";
        }
    }

    $sql = "UPDATE villes SET " . implode(', ', $updates) . " WHERE id = $id";
    if ($conn->query($sql)) {
        header("Location: villes.php");
        exit();
    } else {
        die("Error updating record: " . $conn->error);
    }
}

// Show form
if (!isset($_GET['id'])) {
    die("Error: Missing ID.");
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM villes WHERE id = $id");
if (!$result || $result->num_rows == 0) {
    die("Error: Record not found.");
}
$row = $result->fetch_assoc();

// Set current year
$currentYear = date('Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Ville | Al Hoceima Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Edit ville record in Al Hoceima Province administration">
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
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
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .page-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
        /* Form Styles */
        .form-container {
            background: var(--card-color);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--box-shadow);
            max-width: 800px;
            margin: 0 auto;
        }
        .form-container h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: var(--border-radius);
            background: var(--bg-color);
            color: var(--text-color);
            font-family: 'Poppins', sans-serif;
            transition: border 0.3s, box-shadow 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.1);
        }
        /* Button Styles */
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: var(--gradient);
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        .btn-danger {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        /* Responsive */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            .btn {
                width: 100%;
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
        <div class="page-header">
            <h1>Edit Ville</h1>
            <p>Update the details for this ville record</p>
        </div>

        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <?php foreach ($row as $key => $value): ?>
                    <?php if ($key != 'id'): ?>
                        <div class="form-group">
                            <label for="<?php echo $key; ?>"><?php echo ucfirst($key); ?></label>
                            <input type="text" class="form-control" id="<?php echo $key; ?>" 
                                   name="<?php echo $key; ?>" 
                                   value="<?php echo htmlspecialchars($value); ?>" required>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <div class="action-buttons">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="villes.php" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    // Initialize dark mode
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>
</body>
</html>
<?php $conn->close(); ?>