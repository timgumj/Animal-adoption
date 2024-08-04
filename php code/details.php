<?php
session_start();
require_once "connection.php";

// Check if 'id' is set in the URL and is a valid integer
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $id = intval($_GET['id']);

  // Prepare the SQL statement to fetch the specific product
  $sql = "SELECT * FROM animal WHERE id = $id";
  $result = $conn->query($sql);

  if ($result === false) {
    die("Error executing query: " . $conn->error);
  }

  // Fetch product details
  $product = $result->fetch_assoc();
} else {
  die("Invalid or missing ID parameter.");
}

// Fetch additional photos
$photos_sql = "SELECT id, photo_path FROM photos WHERE animal_id = $id";
$photos_result = $conn->query($photos_sql);

// Check if the current user is an admin
$isAdmin = isset($_SESSION["admin"]) && $_SESSION["admin"];

// Handle photo deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_photo']) && $isAdmin) {
  $photo_id = intval($_POST['photo_id']);
  $photo_path = $_POST['photo_path'];

  // Delete the photo file from the server
  if (file_exists($photo_path)) {
    unlink($photo_path);
  }

  // Delete the photo record from the database
  $sql = "DELETE FROM photos WHERE id = $photo_id";
  if (mysqli_query($conn, $sql)) {
    header("Location: details.php?id=$id");
    exit();
  } else {
    echo "Error deleting photo: " . mysqli_error($conn);
  }
}

// Handle product deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_product']) && $isAdmin) {
  // Delete associated photos
  $photos_sql = "SELECT photo_path FROM photos WHERE animal_id = $id";
  $photos_result = $conn->query($photos_sql);
  while ($photo_row = $photos_result->fetch_assoc()) {
    if (file_exists($photo_row["photo_path"])) {
      unlink($photo_row["photo_path"]);
    }
  }
  $sql = "DELETE FROM photos WHERE animal_id = $id";
  mysqli_query($conn, $sql);

  // Delete the product
  $sql = "DELETE FROM animal WHERE id = $id";
  if (mysqli_query($conn, $sql)) {
    header("Location: index.php"); // Redirect to home or another page after deletion
    exit();
  } else {
    echo "Error deleting product: " . mysqli_error($conn);
  }
}

// Fetch related products
$vaccinated = $product['vaccinated'];
$sql_related = "SELECT * FROM animal WHERE vaccinated = '$vaccinated' AND id != '$id' LIMIT 6";
$result_related = mysqli_query($conn, $sql_related);

$related_products = [];
if ($result_related && mysqli_num_rows($result_related) > 0) {
  while ($row = mysqli_fetch_assoc($result_related)) {
    $related_products[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail View</title>
  <link rel="stylesheet" href="index.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="details.css">
  <style>
    .product-details {
      display: flex;
      align-items: flex-start;
    }

    .product-photo img {
      max-width: 100%;
      height: auto;
    }

    .product-photo {
      flex: 1;
      margin-right: 20px;
    }

    .product-info {
      flex: 2;
    }

    .card-text {
      text-transform: none;
    }

    .thumbnail img {
      cursor: pointer;
      width: 100%;
      height: auto;
    }

    .photo-container {
      position: relative;
    }

    .delete-button {
      position: absolute;
      top: 10px;
      right: 10px;
    }
  </style>
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
            <?php if ($isAdmin) : ?>
              <li class="nav-item">
                <a class="nav-link text-white" href="create.php"><u>Create listing</u></a>
              </li>

            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
  </div>
  <br>
  <div class="container">
    <?php
    if ($result->num_rows > 0) {
      echo '<div class="product-details">';
      echo '<div class="product-photo"><img src="' . $product["image_path"] . '" alt="' . $product["name"] . '"></div>';
      echo '<div class="product-info">';
      echo '<h5>' . $product["name"] . '</h5>';
      echo '<p class="card-text"><strong>Email:</strong> ' . $product["email"] . '</p>';
      echo '<p class="card-text"><strong>Address:</strong> ' . $product["address"] . '</p>';
      echo '<p class="card-text"><strong>Size:</strong> ' . $product["size"] . '</p>';
      echo '<p class="card-text"><strong>Description:</strong> ' . $product["short_description"] . '</p>';
      echo '<p class="card-text"><strong>Age:</strong> ' . $product["age"] . '</p>';
      echo '<p class="card-text"><strong>Breed:</strong> ' . $product["breed"] . '</p>';
      echo '<p class="card-text"><strong>Vaccinated:</strong> ' . $product["vaccinated"] . '</p>';
      $statusText = $product["status"];
      echo '<p class="card-text"><strong>Status:</strong> ' . $statusText . '</p>';
      echo '</div>';
      echo '</div>';

      // Display additional photos using Bootstrap grid
      if ($photos_result->num_rows > 0) {
        echo '<div class="container text-center mt-4">';
        echo '<div class="row row-cols-1 row-cols-md-4 g-4">';
        while ($photo_row = $photos_result->fetch_assoc()) {
          echo '<div class="col photo-container">';
          echo '<div class="p-3 thumbnail" data-bs-toggle="modal" data-bs-target="#lightboxModal" data-index="' . $photo_row["id"] . '"><img src="' . $photo_row["photo_path"] . '" class="img-fluid" alt="Additional Photo"></div>';
          if ($isAdmin) {
            echo '<form action="details.php?id=' . $id . '" method="post" class="d-inline">';
            echo '<input type="hidden" name="photo_id" value="' . $photo_row["id"] . '">';
            echo '<input type="hidden" name="photo_path" value="' . $photo_row["photo_path"] . '">';
            echo '<button type="submit" name="delete_photo" class="btn btn-danger btn-sm delete-button">Delete</button>';
          }
          echo '</form>';
          echo '</div>';
        }
        echo '</div>';
        echo '</div>';
      }
    } else {
      echo "No product details found.";
    }
    ?>

    <div class="mt-4">
      <?php if ($isAdmin) : ?>
        <a href="update.php?id=<?= $id ?>" class="btn btn-primary">Edit</a>
        <form action="details.php?id=<?= $id ?>" method="post" class="d-inline">
          <button type="submit" name="delete_product" class="btn btn-danger">Delete listing</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <br>
  <footer>
    <div class="footerContainer">
      <div class="footerBottom">
        <p>Copyright &copy; 2024; Pet Adopt <span class="designer"></span></p>
      </div>
    </div>
  </footer>

  <!-- Lightbox Modal -->
  <div class="modal fade" id="lightboxModal" tabindex="-1" aria-labelledby="lightboxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="lightboxModalLabel">Photo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <img src="" class="img-fluid" id="lightboxImage" alt="Full Resolution">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="prevPhoto">Previous</button>
          <button type="button" class="btn btn-secondary" id="nextPhoto">Next</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var thumbnails = document.querySelectorAll('.thumbnail');
      var lightboxImage = document.getElementById('lightboxImage');
      var currentIndex = 0;
      var photoPaths = [];

      thumbnails.forEach(function(thumbnail, index) {
        photoPaths.push(thumbnail.querySelector('img').src);
        thumbnail.addEventListener('click', function() {
          currentIndex = index;
          lightboxImage.src = photoPaths[currentIndex];
        });
      });

      document.getElementById('prevPhoto').addEventListener('click', function() {
        if (currentIndex > 0) {
          currentIndex--;
          lightboxImage.src = photoPaths[currentIndex];
        }
      });

      document.getElementById('nextPhoto').addEventListener('click', function() {
        if (currentIndex < photoPaths.length - 1) {
          currentIndex++;
          lightboxImage.src = photoPaths[currentIndex];
        }
      });
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>