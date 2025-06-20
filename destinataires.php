<?php
include 'db.php'; // Make sure db.php exists and has your database connection details

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Prepare and execute the delete statement
    $stmt = $conn->prepare("DELETE FROM destinataires WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        // Success
    } else {
        echo "<script>alert('Error deleting record: " . $stmt->error . "');</script>";
    }
    $stmt->close();
    header("Location: destinataires.php"); // Redirect back to the list
    exit();
}

// Handle add new
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $fields = [];
    $placeholders = [];
    $values = [];
    $types = ''; // String to hold parameter types (e.g., 'ssi')

    // Get column names and prepare statement dynamically
    $columns_result_for_add = $conn->query("SHOW COLUMNS FROM destinataires"); // Use a different variable name
    $allowed_fields = [];
    if ($columns_result_for_add) { // Check if query was successful
        while ($col = $columns_result_for_add->fetch_assoc()) {
            if ($col['Field'] !== 'id') { // Exclude the ID column
                $allowed_fields[] = $col['Field'];
            }
        }
        $columns_result_for_add->free(); // Free the result set
    }


    foreach ($allowed_fields as $field) {
        if (isset($_POST[$field])) {
            $fields[] = "`" . $field . "`";
            $placeholders[] = "?";
            $values[] = $_POST[$field];

            // Determine parameter type (basic for now)
            if (is_int($_POST[$field])) {
                $types .= 'i';
            } elseif (is_float($_POST[$field])) {
                 $types .= 'd';
            } elseif (is_numeric($_POST[$field]) && strpos($_POST[$field], '.') === false) {
                 $types .= 'i';
            } elseif (is_numeric($_POST[$field])) {
                 $types .= 'd';
            } else {
                $types .= 's'; // Default to string
            }
        }
    }

    if (!empty($fields)) {
        $sql = "INSERT INTO destinataires (" . implode(",", $fields) . ") VALUES (" . implode(",", $placeholders) . ")";
        $stmt = $conn->prepare($sql);

        if ($stmt) { // Check if prepare was successful
             // Bind parameters dynamically
             $bind_params = array();
             $bind_params[] = &$types; // First parameter is the types string
             for ($i = 0; $i < count($values); $i++) {
                 $bind_params[] = &$values[$i]; // Pass values by reference
             }

             // Use call_user_func_array to pass parameters to bind_param
             call_user_func_array(array($stmt, 'bind_param'), $bind_params);

             if ($stmt->execute()) {
                 // Success
             } else {
                  echo "<script>alert('Error adding record: " . $stmt->error . "');</script>";
             }
             $stmt->close();
        } else {
             echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
        }
    }

    header("Location: destinataires.php"); // Redirect back to the list
    exit();
}

// Fetch all destinataires
$result = $conn->query("SELECT * FROM destinataires");

// Get column names for the table header and add form
$columns_result_for_display = $conn->query("SHOW COLUMNS FROM destinataires"); // Use a different variable name
$column_names = [];
if ($columns_result_for_display) { // Check if query was successful
    while ($col = $columns_result_for_display->fetch_assoc()) {
        $column_names[] = $col['Field'];
    }
    $columns_result_for_display->free(); // Free the result set
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Destinataires | Al Hoceima Province</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Destinataires list for Al Hoceima Province administration">
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
        .card { /* Using card style for sections */
            background: var(--card-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            border: 1px solid rgba(0,0,0,0.05);
            margin-bottom: 40px; /* Space between sections */
        }
        .card h1, .card h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--primary-color);
            text-align: center; /* Center headings */
        }

        /* Table Styles */
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); /* Subtle table shadow */
        }
        .styled-table thead tr {
            background: var(--gradient);
            color: white;
            text-align: left;
        }
         .styled-table th,
         .styled-table td {
            padding: 12px 15px; /* Increased padding */
            border: 1px solid rgba(0,0,0,0.1); /* Lighter border */
        }
        .styled-table tbody tr {
             border-bottom: 1px solid rgba(0,0,0,0.08); /* Lighter row separator */
        }
        .styled-table tbody tr:nth-of-type(even) {
            background-color: var(--bg-color); /* Zebra striping */
        }
         .styled-table tbody tr:last-of-type {
            border-bottom: 2px solid var(--primary-color); /* Highlight last row */
        }
        .styled-table tbody tr:hover {
            background-color: rgba(37, 117, 252, 0.05); /* Hover effect */
        }
        .styled-table td {
            word-break: break-word; /* Prevent long text overflow */
        }
        .action-buttons {
            display: flex;
            gap: 8px; /* Space between action buttons */
            flex-wrap: wrap; /* Allow buttons to wrap */
        }

        /* Form Styles */
        .add-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Responsive grid */
            gap: 20px; /* Space between form fields */
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 0; /* Remove default margin-bottom as gap handles spacing */
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color);
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: var(--border-radius);
            font-size: 1rem;
            background-color: var(--bg-color);
            color: var(--text-color);
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
            box-shadow: 0 0 5px rgba(37, 117, 252, 0.3);
        }
        .button-group {
            grid-column: 1 / -1; /* Make button group span all columns */
            display: flex;
            justify-content: center;
            margin-top: 10px; /* Space above button */
        }
        .btn {
            display: inline-block;
            padding: 10px 25px; /* Adjusted padding for button */
            background: var(--gradient);
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            text-align: center;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .btn-danger {
            background: #f44336;
            background: linear-gradient(135deg, #f44336, #e57373);
        }

        /* Footer Styles */
        footer {
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            background: var(--card-color);
            color: var(--text-color);
            box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
            position: relative;
            bottom: 0;
            width: 100%;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card {
                padding: 20px;
            }
            .card h1, .card h2 {
                font-size: 1.5rem;
            }
            .styled-table th,
            .styled-table td {
                padding: 8px 10px;
                font-size: 0.9rem;
            }
            .btn {
                padding: 8px 15px;
                font-size: 0.8rem;
            }
             .add-form {
                grid-template-columns: 1fr; /* Stack form fields on small screens */
            }
        }
    </style>
</head>
<body>
<header>
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
            <h1>Destinataires List</h1>
            <?php if ($result && $result->num_rows > 0): ?>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <?php foreach ($column_names as $col_name): ?>
                                <th><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $col_name))); ?></th>
                            <?php endforeach; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result->data_seek(0); // Reset pointer after getting column names
                        while ($row = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?php echo htmlspecialchars($value); ?></td>
                                <?php endforeach; ?>
                                <td>
                                    <div class="action-buttons">
                                         <!-- Link to edit page (assuming it's destinatairesedit.php) -->
                                        <a class="btn" href="destinatairesedit.php?id=<?php echo htmlspecialchars($row['id']); ?>">Edit</a>
                                        <a class="btn btn-danger" href="?delete=<?php echo htmlspecialchars($row['id']); ?>" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php elseif ($conn->error): ?>
                 <p style="color: red;">Error fetching data: <?php echo htmlspecialchars($conn->error); ?></p>
            <?php else: ?>
                <p>No destinataires found.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Add New Destinataire</h2>
            <form method="POST" class="add-form">
                <?php
                // Re-fetch column names specifically for the add form
                $columns_result_for_add_form = $conn->query("SHOW COLUMNS FROM destinataires");
                if ($columns_result_for_add_form) {
                     while ($col = $columns_result_for_add_form->fetch_assoc()):
                         if ($col['Field'] == 'id') continue; // Skip the ID field
                         ?>
                         <div class="form-group">
                             <label for="add_<?php echo htmlspecialchars($col['Field']); ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $col['Field']))); ?></label>
                             <input type="text" name="<?php echo htmlspecialchars($col['Field']); ?>" id="add_<?php echo htmlspecialchars($col['Field']); ?>" required> <!-- Added 'required' -->
                         </div>
                         <?php
                     endwhile;
                     $columns_result_for_add_form->free(); // Free the result set
                }
                ?>
                 <div class="button-group">
                    <button type="submit" name="add" class="btn">Add Destinataire</button>
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
        const savedMode = localStorage.getItem('alhoceima_darkMode');

        if (savedMode === 'enabled') {
            document.body.classList.add('dark-mode');
            // Ensure the icon is correct on load
            const icon = modeToggle.querySelector('i');
            if (icon.classList.contains('fa-moon')) {
                 icon.classList.replace('fa-moon', 'fa-sun');
            }
        } else {
             // Ensure the icon is correct on load if not in dark mode
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
                localStorage.setItem('alhoceima_darkMode', 'enabled'); // Use specific key
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('alhoceima_darkMode', 'disabled'); // Use specific key
            }
        });
    }
</script>
</body>
</html>
<?php
// Close the connection at the very end
$conn->close();
?>