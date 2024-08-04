<?php
// file_upload.php

function handleFileUpload($fileInputName)
{
  // Define target directory and file name
  $target_dir = __DIR__ . "/pictures/"; // Updated to "pictures" folder
  $target_file = $target_dir . basename($_FILES[$fileInputName]["name"]);
  $uploadOk = 1;
  $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

  // Check if file is an actual image or a PDF
  if (isset($_POST["submit"])) {
    if ($fileType == "pdf") {
      // Check if it's a PDF
      $check = mime_content_type($_FILES[$fileInputName]["tmp_name"]);
      if ($check === "application/pdf") {
        $uploadOk = 1;
      } else {
        $uploadOk = 0;
      }
    } else {
      // Check if it's a valid image
      $check = getimagesize($_FILES[$fileInputName]["tmp_name"]);
      if ($check !== false) {
        $uploadOk = 1;
      } else {
        $uploadOk = 0;
      }
    }
  }

  // Check file size (example: limit to 5MB)
  if ($_FILES[$fileInputName]["size"] > 5000000) {
    $uploadOk = 0;
  }

  // Allow certain file formats
  $allowedFileTypes = ["jpg", "jpeg", "png", "gif", "webp", "pdf", "raw", "tif", "tiff", "bmp"];
  if (!in_array($fileType, $allowedFileTypes)) {
    $uploadOk = 0;
  }

  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
    return ["error" => "Sorry, your file was not uploaded."];
  } else {
    // If everything is ok, try to upload file
    if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $target_file)) {
      return ["success" => basename($_FILES[$fileInputName]["name"])];
    } else {
      return ["error" => "Sorry, there was an error uploading your file."];
    }
  }
}
