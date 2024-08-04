<?php
session_start();
require_once "connection.php";

function cleanInput($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $first_name = cleanInput(mysqli_real_escape_string($conn, $_POST['first_name']));
  $last_name = cleanInput(mysqli_real_escape_string($conn, $_POST['last_name']));
  $password = cleanInput(mysqli_real_escape_string($conn, $_POST['password']));
  $date_of_birth = cleanInput(mysqli_real_escape_string($conn, $_POST['date_of_birth']));
  $email = cleanInput(mysqli_real_escape_string($conn, $_POST['email']));
  $picture = ''; // Will hold the file name of the uploaded picture
  $status = 'active'; // Default status

  // Handle file upload
  if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['picture']['tmp_name'];
    $fileName = $_FILES['picture']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif');
    if (in_array($fileExtension, $allowedfileExtensions)) {
      // Ensure the upload directory exists
      $uploadFileDir = './uploads/';
      if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0777, true);
      }

      // Use a unique filename to prevent overwriting
      $newFileName = uniqid() . '.' . $fileExtension;
      $dest_path = $uploadFileDir . $newFileName;

      if (move_uploaded_file($fileTmpPath, $dest_path)) {
        $picture = $newFileName; // Store the unique file name in the database
      } else {
        $error = "There was an error moving the uploaded file.";
      }
    } else {
      $error = "Upload failed. Allowed file types: " . implode(', ', $allowedfileExtensions);
    }
  }

  // Hash the password before storing it
  $passwordHash = password_hash($password, PASSWORD_DEFAULT);

  // Check if email already exists
  $sql_check = "SELECT * FROM users WHERE email = '$email'";
  $result_check = mysqli_query($conn, $sql_check);

  if (mysqli_num_rows($result_check) > 0) {
    $error = "Email already exists.";
  } else {
    // Insert the new user into the database
    $sql = "INSERT INTO users (first_name, last_name, password, date_of_birth, email, picture, status) 
                VALUES ('$first_name', '$last_name', '$passwordHash', '$date_of_birth', '$email', '$picture', '$status')";

    if (mysqli_query($conn, $sql)) {
      $success = "Registration successful. You can now <a href='login.php'>login</a>.";
    } else {
      $error = "Error: " . mysqli_error($conn);
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="register.css">
</head>

<body>

  <div>
    <nav class="navbar navbar-expand-lg bg-dark">
      <div class="container-fluid">
        <a class="navbar-brand text-white" href="index.php">Pet Adopt</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link text-white" aria-current="page" href="index.php"><u>Home</u></a>
            </li>

            <li class="nav-item">
              <a class="nav-link text-white" href="register.php"><u>Register</u></a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="login.php"><u>Sign-in</u></a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </div>
  <br>

  <div class="container register-container">
    <h2 class="text-center">Register</h2>
    <?php if ($error) : ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success) : ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <form method="post" action="register.php" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="first_name" class="form-label">First Name</label>
        <input type="text" class="form-control" id="first_name" name="first_name" required>
      </div>
      <div class="mb-3">
        <label for="last_name" class="form-label">Last Name</label>
        <input type="text" class="form-control" id="last_name" name="last_name" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <div class="mb-3">
        <label for="date_of_birth" class="form-label">Date of Birth</label>
        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="picture" class="form-label">Picture</label>
        <input type="file" class="form-control" id="picture" name="picture">
      </div>
      <button type="submit" class="btn btn-dark">Register</button>
    </form>
  </div>

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