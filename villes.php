<?php
include 'db.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Add a new record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $columns = [];
    $values = [];

    foreach ($_POST as $key => $value) {
        if ($key != 'add') {
            $columns[] = "`$key`";
            $values[] = "'" . $conn->real_escape_string($value) . "'";
        }
    }

    if (!empty($columns)) {
        $sql = "INSERT INTO villes (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
        $conn->query($sql);
    }
}

// Delete a record
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM villes WHERE id = $id");
}

// Fetch all records
$result = $conn->query("SELECT * FROM villes");
$columns = $result->num_rows ? array_keys($result->fetch_assoc()) : [];

if ($result->num_rows) $result->data_seek(0);

// Set current year
$currentYear = date('Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Villes Management | Al Hoceima Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Management interface for villes in Al Hoceima Province">
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
            padding: 40px 0 100px;
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
        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: var(--card-color);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }
        .data-table th, 
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .data-table th {
            background: var(--gradient);
            color: white;
            font-weight: 600;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .data-table tr:hover {
            background-color: rgba(0,0,0,0.02);
        }
        .dark-mode .data-table tr:hover {
            background-color: rgba(255,255,255,0.05);
        }
        /* Form Styles */
        .form-container {
            background: var(--card-color);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--box-shadow);
            margin-top: 40px;
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
        .btn-sm {
            padding: 8px 15px;
            font-size: 0.8rem;
        }
        .btn-edit {
            background: linear-gradient(135deg, #00b09b, #96c93d);
        }
        .btn-delete {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        /* Responsive */
        @media (max-width: 768px) {
            .data-table {
                display: block;
                overflow-x: auto;
            }
            .action-buttons {
                flex-direction: column;
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
            <h1>Villes Management</h1>
            <p>Manage the villes data for Al Hoceima Province administration</p>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <th><?php echo ucfirst(htmlspecialchars($col)); ?></th>
                    <?php endforeach; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <td><?php echo htmlspecialchars($row[$col]); ?></td>
                        <?php endforeach; ?>
                        <td>
                            <div class="action-buttons">
                                <a href="villesedit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this record?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="form-container">
            <h2><i class="fas fa-plus-circle"></i> Add New Ville</h2>
            <form method="POST">
                <?php foreach ($columns as $col): ?>
                    <?php if ($col != 'id'): ?>
                        <div class="form-group">
                            <label for="<?php echo htmlspecialchars($col); ?>"><?php echo ucfirst($col); ?></label>
                            <input type="text" class="form-control" id="<?php echo htmlspecialchars($col); ?>" name="<?php echo htmlspecialchars($col); ?>" required>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <button type="submit" name="add" class="btn">
                    <i class="fas fa-save"></i> Add Record
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
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