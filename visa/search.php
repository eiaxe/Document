<?php
include '../db.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    exit;
}

$stmt = $conn->prepare("SELECT * FROM submissions WHERE reference_number LIKE ? OR objet LIKE ? OR division LIKE ? ORDER BY id DESC LIMIT 20");
$search = "%$q%";
$stmt->bind_param("sss", $search, $search, $search);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>No results found.</p>";
    exit;
}

while ($row = $result->fetch_assoc()) {
    echo "<div class='result-item'>";
    echo "<strong>Ref #:</strong> " . htmlspecialchars($row['reference_number']) . "<br>";
    echo "<strong>Objet:</strong> " . htmlspecialchars($row['objet']) . "<br>";
    echo "<strong>Division:</strong> " . htmlspecialchars($row['division']) . "<br>";
    echo "<hr>";
    echo "</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Live Search</title>
  <style>
    #results { margin-top: 10px; }
    .result-item { border-bottom: 1px solid #ccc; padding: 5px; }
  </style>
</head>
<body>
  <h2>Search Documents</h2>
  <input type="text" id="searchInput" placeholder="Type reference, subject, or division...">
  <div id="results"></div>

  <script>
    const input = document.getElementById('searchInput');
    const results = document.getElementById('results');

    input.addEventListener('input', () => {
      const query = input.value.trim();
      if (query.length < 2) {
        results.innerHTML = ''; // don't search for very short input
        return;
      }

      fetch(`search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.text())
        .then(data => {
          results.innerHTML = data;
        });
    });
  </script>
</body>
</html>
