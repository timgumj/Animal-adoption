<?php
session_start();
require_once "connection.php";

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit();
}

if (!isset($_GET['id'])) {
  die("Product ID is required");
}

$product_id = intval($_GET['id']);

// Fetch the product details
$sql = "SELECT * FROM library WHERE id = $product_id AND user_id = {$_SESSION['user']}";
$result = $conn->query($sql);

if ($result === false) {
  die("Error executing query: " . $conn->error);
}

$product = $result->fetch_assoc();

if (!$product) {
  die("Product not found");
}

// Fetch additional photos
$photos_sql = "SELECT id, photo_path FROM photos WHERE library_id = $product_id";
$photos_result = $conn->query($photos_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($product["title"]); ?></title>
  <link rel="stylesheet" href="index.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="details.css">
</head>

<body>
  <div>
    <nav class="navbar navbar-expand-lg bg-dark">
      <div class="container-fluid">
        <a class="navbar-brand text-white" href="index.php">Wien-noir</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link text-white" aria-current="page" href="index.php"><u>Home</u></a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="create.php"><u>Create listing</u></a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="myproduct.php"><u>My listing</u></a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </div>
  <br>
  <div class="container">
    <h1><?php echo htmlspecialchars($product["title"]); ?></h1>
    <img src="<?php echo htmlspecialchars($product["image_path"]); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product["title"]); ?>">
    <p><strong>Business owner:</strong> <?php echo htmlspecialchars($product["author_first_name"]) . ' ' . htmlspecialchars($product["author_last_name"]); ?></p>
    <p><strong>Registration No.:</strong> <?php echo htmlspecialchars($product["isbn_code"]); ?></p>
    <p><strong>Description:</strong> <?php echo htmlspecialchars($product["short_description"]); ?></p>
    <p><strong>Address:</strong> <?php echo htmlspecialchars($product["publisher_address"]); ?></p>
    <p><strong>Type of Business:</strong> <?php echo htmlspecialchars($product["type"]); ?></p>
    <p><strong>Status:</strong> <?php echo $product["status"] == 1 ? 'Available' : 'Reserved'; ?></p>

    <?php if ($photos_result->num_rows > 0) : ?>
      <div class="additional-photos">
        <h5>Additional Photos</h5>
        <div class="row">
          <?php while ($photo = $photos_result->fetch_assoc()) : ?>
            <div class="col-md-3">
              <img src="<?php echo htmlspecialchars($photo["photo_path"]); ?>" class="img-fluid" alt="Additional Photo">
            </div>
          <?php endwhile; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <br>
  <footer>
    <div class="footerContainer">
      <div class="footerBottom">
        <p>&copy; 2024 Wien-noir <span class="designer"></span></p>
      </div>
    </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>