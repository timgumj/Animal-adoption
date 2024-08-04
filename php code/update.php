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
$id = "";
$name = "";
$email = "";
$short_description = "";
$address = "";
$age = "";
$size = "";
$breed = "";
$vaccinated = "";
$image_path = "";

// Get the ID of the record to update
if (isset($_GET['id'])) {
  $id = $_GET['id'];

  // Fetch the current data for the record
  $sql = "SELECT * FROM animal WHERE id = $id";
  $result = mysqli_query($conn, $sql);

  if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $name = $row['name'];
    $email = $row['email'];
    $short_description = $row['short_description'];
    $address = $row['address'];
    $age = $row['age'];
    $size = $row['size'];
    $breed = $row['breed'];
    $vaccinated = $row['vaccinated'];
    $image_path = $row['image_path'];
  } else {
    $errors[] = "Record not found.";
  }

  // Fetch the current additional photos for the record
  $sql_photos = "SELECT * FROM photos WHERE animal_id = $id";
  $result_photos = mysqli_query($conn, $sql_photos);
  $current_photos = [];
  while ($row_photos = mysqli_fetch_assoc($result_photos)) {
    $current_photos[] = $row_photos['photo_path'];
  }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
  $id = $_POST['id'];
  $name = strtoupper(trim($_POST["name"]));
  $email = trim($_POST["email"]); // Keep original case
  $short_description = mysqli_real_escape_string($conn, trim($_POST["short_description"]));
  $address = trim($_POST["address"]); // Keep original case
  $age = trim($_POST["age"]); // Keep original case
  $size = trim($_POST["size"]); // Keep original case
  $breed = trim($_POST["breed"]); // Keep original case
  $vaccinated = trim($_POST["vaccinated"]); // Keep original case

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

    // Only update the image_path if a new image was uploaded
    $image_path_sql = $image_path ? ", image_path = '$image_path'" : "";

    $sql = "UPDATE animal SET 
              name = '$name', 
              email = '$email', 
              short_description = '$short_description', 
              address = '$address', 
              size = '$size', 
              age = '$age', 
              breed = '$breed', 
              vaccinated = '$vaccinated' 
              $image_path_sql
            WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
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
  <title>Update Record</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="update.css">
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
    <h1>Update Record</h1>
    <?php if (!empty($errors)) : ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $error) : ?>
          <p><?php echo $error; ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <form action="update.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?php echo $id; ?>">
      <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
      </div>
      <div class="mb-3">
        <label for="short_description" class="form-label">Short Description</label>
        <textarea class="form-control" id="short_description" name="short_description" rows="3" required><?php echo htmlspecialchars($short_description); ?></textarea>
      </div>
      <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" required>
      </div>
      <div class="mb-3">
        <label for="age" class="form-label">Age</label>
        <input type="text" class="form-control" id="age" name="age" value="<?php echo htmlspecialchars($age); ?>" required>
      </div>
      <div class="mb-3">
        <label for="size" class="form-label">Size</label>
        <input type="text" class="form-control" id="size" name="size" value="<?php echo htmlspecialchars($size); ?>" required>
      </div>
      <div class="mb-3">
        <label for="breed" class="form-label">Breed</label>
        <input type="text" class="form-control" id="breed" name="breed" value="<?php echo htmlspecialchars($breed); ?>" required>
      </div>
      <div class="mb-3">
        <label for="vaccinated" class="form-label">Vaccinated</label>
        <select class="form-control" id="vaccinated" name="vaccinated" required>
          <option value="Vaccinated" <?php echo $vaccinated == 'Vaccinated' ? 'selected' : ''; ?>>Vaccinated</option>
          <option value="Not Vaccinated" <?php echo $vaccinated == 'Not Vaccinated' ? 'selected' : ''; ?>>Not Vaccinated</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="image" class="form-label">Image</label>
        <input type="file" class="form-control" id="image" name="image">
        <?php if ($image_path) : ?>
          <p>Current image: <img src="<?php echo $image_path; ?>" alt="Current Image" style="max-width: 200px; max-height: 200px;"></p>
        <?php endif; ?>
      </div>
      <div class="mb-3">
        <label for="photos" class="form-label">Additional Photos</label>
        <input type="file" class="form-control" id="photos" name="photos[]" multiple>
        <?php if (!empty($current_photos)) : ?>
          <p>Current additional photos:</p>
          <div class="d-flex flex-wrap">
            <?php foreach ($current_photos as $photo) : ?>
              <div class="m-2">
                <img src="<?php echo $photo; ?>" alt="Current Additional Photo" style="max-width: 100px; max-height: 100px;">
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary">Update</button>
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