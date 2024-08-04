<?php
session_start();

require_once "connection.php";

// Check if the user is an admin
if (!isset($_SESSION["admin"])) {
  header("Location: index.php");
  exit();
}

// Check if product ID is provided in the URL
if (!isset($_GET['id'])) {
  header("Location: myproduct.php");
  exit();
}

$product_id = $_GET['id'];

// Admin can delete any product
$sql = "SELECT image_path FROM animal WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);

// Execute the query and fetch the result
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $image_path = $row['image_path'];

  // Delete the main image file
  if (!empty($image_path) && file_exists($image_path)) {
    unlink($image_path);
  }

  // Delete additional photos
  $sql_photos = "SELECT photo_path FROM photos WHERE animal_id = ?";
  $stmt_photos = $conn->prepare($sql_photos);
  $stmt_photos->bind_param("i", $product_id);
  $stmt_photos->execute();
  $result_photos = $stmt_photos->get_result();

  while ($photo_row = $result_photos->fetch_assoc()) {
    $photo_path = $photo_row['photo_path'];
    if (!empty($photo_path) && file_exists($photo_path)) {
      unlink($photo_path);
    }
  }

  // Delete from the photos table first
  $sql_delete_photos = "DELETE FROM photos WHERE animal_id = ?";
  $stmt_delete_photos = $conn->prepare($sql_delete_photos);
  $stmt_delete_photos->bind_param("i", $product_id);
  $stmt_delete_photos->execute();

  // Delete from the animal table
  $sql_delete_product = "DELETE FROM animal WHERE id = ?";
  $stmt_delete_product = $conn->prepare($sql_delete_product);
  $stmt_delete_product->bind_param("i", $product_id);

  // Execute the delete query
  if ($stmt_delete_product->execute()) {
    // Redirect to index.php after successful deletion
    header("Location: index.php");
    exit();
  } else {
    echo "Error: " . $stmt_delete_product->error;
  }

  $stmt_delete_product->close();
  $stmt_delete_photos->close();
} else {
  echo "No such product found or you do not have permission to delete it.";
}

$stmt->close();
$conn->close();
