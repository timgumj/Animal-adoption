<?php
session_start();

if (isset($_GET["logout"])) {
  // Unset all session variables
  $_SESSION = [];

  // Destroy the session
  session_unset();
  session_destroy();

  // Redirect to login page
  header("Location: login.php");
  exit();
}
