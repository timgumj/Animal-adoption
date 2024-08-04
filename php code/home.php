<?php
session_start();

// Redirect admin users to the dashboard
if (isset($_SESSION["admin"])) {
  header("Location: dashboard.php");
  exit();
}

require_once "connection.php";

// Get current user ID
$current_user_id = isset($_SESSION["user"]) ? $_SESSION["user"] : null;

// Check if the adopt button was clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adopt'])) {
  $animal_id = $_POST['animal_id'];
  $user_id = $_SESSION['user'];

  // Get user and animal details
  $user_query = "SELECT first_name, last_name FROM users WHERE id = $user_id";
  $user_result = mysqli_query($conn, $user_query);
  $user_data = mysqli_fetch_assoc($user_result);

  $animal_query = "SELECT name FROM animal WHERE id = $animal_id";
  $animal_result = mysqli_query($conn, $animal_query);
  $animal_data = mysqli_fetch_assoc($animal_result);

  $first_name = $user_data['first_name'];
  $last_name = $user_data['last_name'];
  $animal_name = $animal_data['name'];

  // Insert into pet_adoption table
  $insert_query = "INSERT INTO pet_adoption (users_id, animal_id, first_name, last_name, animal_name, adoption_date) VALUES ($user_id, $animal_id, '$first_name', '$last_name', '$animal_name', NOW())";
  if (mysqli_query($conn, $insert_query)) {
    // Update the animal status to 'Adopted'
    $update_status_query = "UPDATE animal SET status = 'Adopted' WHERE id = $animal_id";
    if (mysqli_query($conn, $update_status_query)) {
      $adopt_success = "Adoption successful!";
    } else {
      $adopt_error = "Error updating status: " . mysqli_error($conn);
    }
  } else {
    $adopt_error = "Error: " . mysqli_error($conn);
  }
}

// Retrieve search filters
$search_author = isset($_GET['author']) ? mysqli_real_escape_string($conn, $_GET['author']) : '';
$search_title = isset($_GET['title']) ? mysqli_real_escape_string($conn, $_GET['title']) : '';
$search_type = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';
$search_senior = isset($_GET['senior']) ? $_GET['senior'] : '';

// SQL query to fetch animals
$sql = "SELECT animal.*, users.id AS user_id FROM animal 
        JOIN users ON animal.user_id = users.id WHERE 1=1";

if (!empty($search_author)) {
  $sql .= " AND (author_first_name LIKE '%$search_author%' OR author_last_name LIKE '%$search_author%')";
}
if (!empty($search_title)) {
  $sql .= " AND title LIKE '%$search_title%'";
}
if (!empty($search_type)) {
  // Ensure the type value is properly sanitized and matches ENUM values
  $types = ['book', 'cd', 'dvd', 'event', 'business', 'ngo', 'restaurant', 'textile'];
  $search_type = strtolower($search_type);
  if (in_array($search_type, $types)) {
    $sql .= " AND type = '" . ucfirst($search_type) . "'";
  }
}
if ($search_senior === 'true') {
  $sql .= " AND age >= 8";
}

$result = mysqli_query($conn, $sql);

$layout = "";
$rows = []; // Initialize $rows as an empty array

if ($result) {
  if (mysqli_num_rows($result) > 0) {
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
  } else {
    $layout .= "<p>No results found</p>";
  }
} else {
  // Handle query error
  $layout .= "<p>Error fetching data: " . mysqli_error($conn) . "</p>";
}

foreach ($rows as $value) {
  if (is_array($value)) {
    $address = strtolower($value["address"]);
    $email = strtolower($value["email"]);
    $status = $value["status"];

    $layout .= "
            <div class='col-12 col-md-4 mb-4'>
                <div class='card h-100'>
                    <img src='{$value["image_path"]}' class='card-img-top' alt='{$value["name"]}'>
                    <div class='card-body'>
                        <h4 class='card-title'>{$value["name"]}</h4>
                        <p class='card-text'><strong>Address:</strong> $address</p>
                        <p class='card-text'><strong>Email:</strong> $email</p>
                        <p class='card-text'><strong>Age:</strong> {$value["age"]}</p>
                        <p class='card-text'><strong>Status:</strong> <span style='color: green; font-weight: bold;'>$status</span></p>
                        <div class='d-flex justify-content-between'>
                            <a href='details.php?id={$value["id"]}' class='btn btn-dark'>Show Details</a>";

    // Show Adopt button only if the animal is available
    if ($current_user_id && $status !== 'Adopted') {
      $layout .= "
                <form method='POST' action='index.php'>
                    <input type='hidden' name='animal_id' value='{$value["id"]}'>
                    <button type='submit' name='adopt' class='btn btn-success'>Take me home</button>
                </form>";
    } elseif ($status === 'Adopted') {
      $layout .= "<button class='btn btn-secondary' disabled>Already Adopted</button>";
    }

    // Show delete button only if admin is logged in
    if (isset($_SESSION["admin"])) {
      $layout .= "<a href='delete.php?id={$value["id"]}' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this item?\")'>Delete</a>";
    }

    $layout .= "
                        </div>
                    </div>
                </div>
            </div>";
  } else {
    error_log("Expected array but got " . gettype($value) . " for value: " . print_r($value, true));
  }
}

// Carousel items
$carousel_sql = "SELECT * FROM animal ORDER BY RAND() LIMIT 4";
$carousel_result = mysqli_query($conn, $carousel_sql);

$carouselItems = "";
if ($carousel_result) {
  if (mysqli_num_rows($carousel_result) > 0) {
    $first = true;
    while ($row = mysqli_fetch_assoc($carousel_result)) {
      $activeClass = $first ? 'active' : '';
      $carouselItems .= "
                <div class='carousel-item $activeClass'>
                    <div class='d-flex align-items-center'>
                        <div class='carousel-image text-center'>
                            <img src='{$row["image_path"]}' class='d-block mx-auto' alt='{$row["name"]}' style='max-height: 400px; max-width: 100%;'>
                        </div>
                        <div class='carousel-caption d-block' style='background-color: rgba(33, 37, 41, 0.85);'>
                            <h5 class='carousel-title'>{$row["name"]}</h5>
                            <a href='details.php?id='{$row["id"]}' class='btn btn-light'>Show Details</a>
                        </div>
                    </div>
                </div>";
      $first = false;
    }
  }
}

// User greeting and profile picture
$link = '';
if (isset($_SESSION["user"])) {
  $sql_user  = "SELECT * FROM users WHERE id = " . $_SESSION["user"];
  $user_result = mysqli_query($conn, $sql_user);
  $row_user = mysqli_fetch_assoc($user_result);
  $email = $row_user['email'];
  $picture = $row_user['picture']; // Assuming 'picture' stores the path to the uploaded image

  // Set default image if the user has not uploaded one
  $default_image = 'https://avatar.iran.liara.run/public/boy?username=Ash';
  $profile_image = !empty($picture) ? "uploads/{$picture}" : $default_image;

  $link = "
        <li class='nav-item d-flex align-items-center'>
            <a class='nav-link text-white' href='logout.php?logout'><u>Logout</u></a>
            <span class='text-warning ms-2'>Welcome $email</span> 
            <img src='$profile_image' style='width: 40px; height: 40px; border-radius: 50%;'>
        </li>";
} elseif (isset($_SESSION["admin"])) {
  $sql_admin  = "SELECT * FROM users WHERE id = " . $_SESSION["admin"];
  $admin_result = mysqli_query($conn, $sql_admin);
  $row_admin = mysqli_fetch_assoc($admin_result);
  $email = $row_admin['email'];
  $picture = $row_admin['picture']; // Assuming 'picture' stores the path to the uploaded image

  // Set default image if the user has not uploaded one
  $default_image = 'https://avatar.iran.liara.run/public/boy?username=Ash';
  $profile_image = !empty($picture) ? "uploads/{$picture}" : $default_image;

  $link = "
        <li class='nav-item d-flex align-items-center'>
            <a class='nav-link text-white' href='logout.php?logout'><u>Logout</u></a>
            <span class='text-warning ms-2'>Welcome Admin $email</span> 
            <img src='$profile_image' style='width: 40px; height: 40px; border-radius: 50%;'>
        </li>";
}

// Close database connection
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
  <link rel="stylesheet" href="index.css">
</head>

<body class="bg-light">
  <nav class="navbar navbar-expand-lg bg-dark navbar-dark">
    <div class="container-fluid container-fluid-custom">
      <a class="navbar-brand" href="index.php">Animal Index</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav ms-auto">
          <?php echo $link; ?>
          <li class="nav-item">
            <a class="nav-link" href="login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="index.php?senior=true">Senior</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid container-fluid-custom mt-4">
    <?php
    if (isset($adopt_success)) {
      echo "<div class='alert alert-success'>$adopt_success</div>";
    }
    if (isset($adopt_error)) {
      echo "<div class='alert alert-danger'>$adopt_error</div>";
    }
    ?>

    <div class="row">
      <div class="col-12 mb-4">
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">
            <?php echo $carouselItems; ?>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    </div>

    <div class="row">
      <?php echo $layout; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>