<?php
session_start();

require_once "connection.php";

// Get current user ID
$current_user_id = isset($_SESSION["user"]) ? $_SESSION["user"] : null;

// Retrieve type filter
$search_type = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';

// SQL query to fetch products by type
$sql = "SELECT library.*, users.id AS user_id FROM library 
        JOIN users ON library.user_id = users.id WHERE 1=1";

if (!empty($search_type)) {
  $sql .= " AND type = '$search_type'";
}

$result = mysqli_query($conn, $sql);

$layout = "";
$rows = []; // Initialize $rows as an empty array

if ($result) {
  if (mysqli_num_rows($result) > 0) {
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
  } else {
    $layout .= "<p>No results found for type: $search_type</p>";
  }
} else {
  // Handle query error
  $layout .= "<p>Error fetching data: " . mysqli_error($conn) . "</p>";
}

foreach ($rows as $value) {
  if (is_array($value)) {
    $layout .= "
                <div class='col-12 col-md-6 mb-4'>
                    <div class='card h-100'>
                        <div class='row g-0 h-100'>
                            <div class='col-md-4'>
                                <img src='{$value["image_path"]}' class='img-fluid rounded-start h-60' alt='...'>
                            </div>
                            <div class='col-md-8'>
                                <div class='card-body'>
                                    <h5 class='card-title'>{$value["title"]}</h5>
                                    <p class='card-text'>Type of business: {$value["type"]}</p>
                                    
                                    <p class='card-text'>Description: {$value["short_description"]}</p>
                                    
                                    <p class='card-text'><a href='publisher.php?publisher_name={$value["publisher_name"]}' class='publisher-link'>Business name: {$value["publisher_name"]}</a></p>
                                    <p class='card-text'>Address: {$value["publisher_address"]}</p>
                                    <p class='card-text'>Email: {$value["author_first_name"]}</p>
                                    <p class='card-text'>Telephone: {$value["author_last_name"]}</p>
                                    
                                    <p class='card-text'>Start date: {$value["start_date"]}</p>
                                    <p class='card-text'>End date: {$value["end_date"]}</p>
                                    
                                    <div class='d-flex flex-wrap gap-2'>
                                        <a href='details.php?id={$value["id"]}' class='btn btn-dark'>Show Details</a>";

    // Show update button only if the product belongs to the logged-in user
    if ($current_user_id == $value["user_id"]) {
      $layout .= "<a href='update.php?id={$value["id"]}' class='btn btn-warning'>Update</a>";
    }

    // Show delete button only if admin is logged in
    if (isset($_SESSION["admin"])) {
      $layout .= "<a href='delete.php?id={$value["id"]}' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this item?\")'>Delete</a>";
    }

    $layout .= "
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>";
  } else {
    error_log("Expected array but got " . gettype($value) . " for value: " . print_r($value, true));
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products in Category: <?= htmlspecialchars(ucfirst($search_type)) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="index.css">
</head>

<body>
  <div class="container-fluid bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark">
      <div class="container">
        <a class="navbar-brand" href="index.php">Wien-noir</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0 mx-auto">
            <li class="nav-item">
              <a class="nav-link text-white" aria-current="page" href="index.php"><u>Home</u></a>
            </li>
            <?php if (isset($_SESSION["user"]) || isset($_SESSION["admin"])) : ?>
              <li class="nav-item">
                <a class="nav-link text-white" href="create.php"><u>Create</u></a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-white" href="myproduct.php"><u>My listing</u></a>
              </li>
            <?php endif; ?>
            <?= isset($_SESSION["user"]) ? "<li class='nav-item'><a class='nav-link text-white' href='logout.php?logout'><u>Logout</u></a></li>" : "<li class='nav-item'><a class='nav-link text-white' href='register.php'><u>Register</u></a></li><li class='nav-item'><a class='nav-link text-white' href='login.php'><u>Sign-in</u></a></li>" ?>
          </ul>
        </div>
      </div>
    </nav>
  </div>

  <div class="container my-4">
    <h2>Products in Category: <?= htmlspecialchars(ucfirst($search_type)) ?></h2>
    <div class="row">
      <?= $layout ?>
    </div>
  </div>

  <footer>
    <div class="footerContainer">
      <div class="footerBottom">
        <p>Copyright &copy; 2024; BigLibrary <span class="designer"></span></p>
      </div>
    </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>