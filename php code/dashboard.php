<?php
session_start();
require_once "connection.php";

// Sanitize input data
$search_author = isset($_GET['author']) ? mysqli_real_escape_string($conn, $_GET['author']) : '';
$search_title = isset($_GET['title']) ? mysqli_real_escape_string($conn, $_GET['title']) : '';

// Base query
$sql = "SELECT * FROM `animal` WHERE 1=1";

// Append conditions based on search inputs
if (!empty($search_author)) {
  $sql .= " AND (email LIKE '%$search_author%' OR author_last_name LIKE '%$search_author%')";
}
if (!empty($search_title)) {
  $sql .= " AND title LIKE '%$search_title%'";
}

$result = mysqli_query($conn, $sql);

// Initialize $rows as an empty array
$rows = [];

// Fetch result set as an associative array
if ($result) {
  $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
  echo "<p>Error fetching data: " . mysqli_error($conn) . "</p>";
}

$layout = "";
if (empty($rows)) {
  $layout .= "<p>No results found</p>";
} else {
  foreach ($rows as $value) {
    if (is_array($value)) {
      $layout .= "
                <div class='col-12 col-md-6 mb-4'>
                    <div class='card h-100'>
                        <div class='row g-0 h-100'>
                            <div class='col-md-4'>
                                <img src='{$value["image_path"]}' class='img-fluid rounded-start h-70' alt='...'>
                            </div>
                            <div class='col-md-8'>
                                <div class='card-body'>
                                    <h5 class='card-title'>{$value["name"]}</h5>
                                    <p class='card-text'>Email: {$value["email"]}</p>
                                    <p class='card-text'>Description: {$value["short_description"]}</p>
                                    <p class='card-text'>Address: {$value["address"]}</p>
                                    <p class='card-text'>Age: {$value["age"]}</p>
                                    <div class='d-flex flex-wrap gap-2'>
                                        <a href='details.php?id={$value["id"]}' class='btn btn-dark'>Show Details</a>";

      // Show update button if user or admin is logged in
      if (isset($_SESSION["user"]) || isset($_SESSION["admin"])) {
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
}

$link = '';
if (isset($_SESSION["admin"])) {
  $sql_user = "SELECT * FROM users WHERE id = " . $_SESSION["admin"];
  $user_result = mysqli_query($conn, $sql_user);
  $row_user = mysqli_fetch_assoc($user_result);
  $first_name = $row_user['first_name'];

  $link = "
    <li class='nav-item d-flex align-items-center'>
        <a class='nav-link text-white' href='logout.php?logout'><u>Logout</u></a>
        <span class='text-danger ms-2'>Welcome $first_name</span> 
        <img src='https://avatar.iran.liara.run/public/boy?username=Ash' style='width: 40px; height: 40px;'>
    </li>";
} else {
  $link = "<li class='nav-item'>
                <a class='nav-link text-white' href='register.php'><u>Register</u></a>
             </li>
             <li class='nav-item'>
                <a class='nav-link text-white' href='login.php'><u>Sign-in</u></a>
             </li>";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Animal Index</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="dashboard.css">
</head>

<body>
  <div class="container-fluid bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark">
      <div class="container">
        <a class="navbar-brand" href="index.php">Pet Adopt</a>
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
            <?php endif; ?>
            <?= $link ?>
          </ul>
        </div>
      </div>
    </nav>
  </div>

  <br><br>

  <div class="container">
    <?php if (isset($_SESSION["user"]) || isset($_SESSION["admin"])) : ?>
      <a class="btn btn-dark" href="create.php">Create product</a>
    <?php endif; ?>
  </div>

  <br><br>

  <div class="container">
    <div class="row">
      <?= $layout ?>
    </div>
  </div>

  <br><br>

  <div class="container">
    <?php if (isset($_SESSION["user"]) || isset($_SESSION["admin"])) : ?>
      <a class="btn btn-dark" href="create.php">Create product</a>
    <?php endif; ?>
  </div>

  <br><br>

  <footer>
    <div class="footerContainer">
      <div class="footerBottom">
        <p>Copyright &copy; 2024; Pet Adopt <span class="designer"></span></p>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>