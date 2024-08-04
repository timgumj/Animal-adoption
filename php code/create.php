<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION["admin"])) {
  // Redirect non-admin users to the index page
  header("Location: index.php");
  exit();
}

require_once "connection.php";

$errors = [];
$name = "";
$email = "";
$short_description = "";
$address = "";
$age = "";
$size = "";
$breed = "";
$vaccinated = "";
$image_path = "";
$status = "Available"; // Set default status to "Available"

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = strtoupper(trim($_POST["name"]));
  $email = trim($_POST["email"]); // Keep original case
  $short_description = mysqli_real_escape_string($conn, trim($_POST["short_description"]));
  $address = trim($_POST["address"]); // Keep original case
  $age = trim($_POST["age"]); // Keep original case
  $size = trim($_POST["size"]); // Keep original case
  $breed = trim($_POST["breed"]); // Keep original case
  $vaccinated = trim($_POST["vaccinated"]); // Keep original case
  $status = trim($_POST["status"]); // Keep original case

  // Handle image upload
  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $target_dir = "pictures/";
    $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $uniqueFileName = uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $uniqueFileName;

    // Check file size (70MB limit)
    if ($_FILES["image"]["size"] > 70000000) {
      $errors[] = "Sorry, your file is too large.";
    }

    // Set the character set to utf8mb4
    if (!$conn->set_charset("utf8mb4")) {
      echo "Error loading character set utf8mb4: " . $conn->error;
    }

    // Allow certain file formats
    $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'raw', 'tif', 'tiff', 'bmp'];
    if (!in_array($imageFileType, $allowedImageTypes)) {
      $errors[] = "Sorry, only JPG, JPEG, PNG, GIF, WEBP, RAW, TIF, TIFF, and BMP files are allowed.";
    }

    // Check if $errors is empty and move file
    if (empty($errors)) {
      if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = $target_file;
      } else {
        $errors[] = "Sorry, there was an error uploading your file.";
      }
    }
  }

  if (empty($errors)) {
    // Check if user or admin is logged in and use their respective IDs
    if (isset($_SESSION["user"])) {
      $user_id = $_SESSION["user"];
    } elseif (isset($_SESSION["admin"])) {
      $user_id = $_SESSION["admin"];
    }

    $sql = "INSERT INTO animal (name, email, short_description, address, size, age, breed, vaccinated, status, image_path, user_id) VALUES ('$name', '$email', '$short_description', '$address', '$size', '$age', '$breed', '$vaccinated', '$status', '$image_path', '$user_id')";

    if (mysqli_query($conn, $sql)) {
      $id = mysqli_insert_id($conn); // Get the ID of the newly inserted product

      // Handle additional photos upload
      if (isset($_FILES['photos']) && $_FILES['photos']['error'][0] == 0) {
        $photo_count = count($_FILES['photos']['name']);
        for ($i = 0; $i < $photo_count; $i++) {
          if ($_FILES['photos']['error'][$i] == 0) {
            $photo_file_type = strtolower(pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION));
            $uniquePhotoName = uniqid() . '_' . time() . '_' . $i . '.' . $photo_file_type;
            $photo_target_file = $target_dir . $uniquePhotoName;

            if ($_FILES['photos']['size'][$i] > 70000000) {
              $errors[] = "Sorry, your photo file is too large.";
              continue;
            }

            $allowedPhotoTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'raw', 'tif', 'tiff', 'bmp'];
            if (!in_array($photo_file_type, $allowedPhotoTypes)) {
              $errors[] = "Sorry, only JPG, JPEG, PNG, GIF, WEBP, RAW, TIF, TIFF, and BMP photo files are allowed.";
              continue;
            }

            if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $photo_target_file)) {
              $photo_path = $photo_target_file;
              $sql_photo = "INSERT INTO photos (animal_id, photo_path) VALUES ('$id', '$photo_path')";
              if (!mysqli_query($conn, $sql_photo)) {
                $errors[] = "Error: " . mysqli_error($conn);
              }
            } else {
              $errors[] = "Sorry, there was an error uploading your photo file.";
            }
          }
        }
      }

      if (empty($errors)) {
        header("Location: details.php?id=$id");
        exit();
      }
    } else {
      $errors[] = "Error: " . mysqli_error($conn);
    }
  }
}

// User greeting
$link = '';
if (isset($_SESSION["user"])) {
  $sql_user = "SELECT * FROM users WHERE id = " . $_SESSION["user"];
  $user_result = mysqli_query($conn, $sql_user);
  $row_user = mysqli_fetch_assoc($user_result);
  $first_name = $row_user['first_name'];

  $link = "
        <li class='nav-item d-flex align-items-center'>
            <a class='nav-link text-white' href='logout.php?logout'><u>Logout</u></a>
            <span class='text-warning ms-2'>Welcome $first_name</span> 
            <img src='https://avatar.iran.liara.run/public/boy?username=Ash' style='width: 40px; height: 40px;'>
        </li>";
} elseif (isset($_SESSION["admin"])) {
  $sql_admin = "SELECT * FROM users WHERE id = " . $_SESSION["admin"];
  $admin_result = mysqli_query($conn, $sql_admin);
  $row_admin = mysqli_fetch_assoc($admin_result);
  $first_name = $row_admin['first_name'];

  $link = "
        <li class='nav-item d-flex align-items-center'>
            <a class='nav-link text-white' href='logout.php?logout'><u>Logout</u></a>
            <span class='text-warning ms-2'>Welcome $first_name</span> 
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
  <style>
    .carousel-inner img {
      width: 100%;
      height: 400px;
      /* Adjust as needed */
      object-fit: cover;
    }

    .carousel-caption {
      background-color: rgba(33, 37, 41, 0.85);
      padding: 10px;
      border-radius: 5px;
    }

    .carousel-control-prev,
    .carousel-control-next {
      width: 5%;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
      background-color: black;
      padding: 10px;
      border-radius: 50%;
    }

    .search-container,
    .cards-container {
      padding-left: 0;
      padding-right: 0;
    }

    .container-fluid-custom {
      max-width: 100%;
      padding-left: 15px;
      padding-right: 15px;
    }
  </style>
</head>

<body>
  <div class="container-fluid bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark">
      <div class="container">
        <a class="navbar-brand" href="index.php">Pet Adopt</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <?php if (isset($_SESSION["admin"])) : ?>
              <li class="nav-item">
                <a class="nav-link text-white" href="dashboard.php"><u>Home</u></a>
              </li>
            <?php endif; ?>
            <?php echo $link; ?>
          </ul>
        </div>
      </div>
    </nav>
  </div>

  <div class="container mt-4">
    <h1>Create New Record</h1>
    <?php if (!empty($errors)) : ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $error) : ?>
          <p><?php echo $error; ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <form action="create.php" method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="short_description" class="form-label">Short Description</label>
        <textarea class="form-control" id="short_description" name="short_description" rows="3" required></textarea>
      </div>
      <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <input type="text" class="form-control" id="address" name="address" required>
      </div>
      <div class="mb-3">
        <label for="age" class="form-label">Age</label>
        <input type="text" class="form-control" id="age" name="age" required>
      </div>
      <div class="mb-3">
        <label for="size" class="form-label">Size</label>
        <input type="text" class="form-control" id="size" name="size" required>
      </div>
      <div class="mb-3">
        <label for="vaccinated" class="form-label">Vaccinated</label>
        <select class="form-control" id="vaccinated" name="vaccinated" required>
          <option value="Vaccinated">Vaccinated</option>
          <option value="Not Vaccinated">Not Vaccinated</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="breed" class="form-label">Breed</label>
        <input type="text" class="form-control" id="breed" name="breed" required>
      </div>
      <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select class="form-control" id="status" name="status" required>
          <option value="Available" selected>Available</option>
          <option value="Adopted">Adopted</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="image" class="form-label">Image</label>
        <input type="file" class="form-control" id="image" name="image" required>
      </div>
      <div class="mb-3">
        <label for="photos" class="form-label">Additional Photos</label>
        <input type="file" class="form-control" id="photos" name="photos[]" multiple>
      </div>
      <button type="submit" class="btn btn-primary">Create</button>
    </form>
  </div>
  <br>
  <br>
  <footer>
    <div class="footerContainer">
      <div class="footerBottom">
        <p>Copyright &copy; 2024; Pet Adopt <span class="designer"></span></p>
      </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>