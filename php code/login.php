<?php
session_start();
require_once "connection.php"; // Adjusted to include the correct connection file

# Redirect if user or admin is already logged in
if (isset($_SESSION["user"])) {
  header("Location: index.php");
  exit();
}

if (isset($_SESSION["admin"])) {
  header("Location: dashboard.php");
  exit();
}

# Initialize error variables
$error = false;
$email = $emailError = $passError = "";

# Function to sanitize input
function cleanInput($input)
{
  return htmlspecialchars(strip_tags(trim($input)));
}

if (isset($_POST["login-btn"])) {
  $email = cleanInput($_POST["email"]);
  $password = cleanInput($_POST["password"]);

  if (empty($email)) {
    $error = true;
    $emailError = "Email is required!";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = true;
    $emailError = "Not a valid email!";
  }

  if (empty($password)) {
    $error = true;
    $passError = "Password is required!";
  }

  if (!$error) { # $error == false
    $sql = "SELECT * FROM `users` WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && password_verify($password, $row['password'])) {
      # Correct credentials
      if ($row["status"] == "adm") {
        # Admin user
        $_SESSION["admin"] = $row["id"];
        header("Location: dashboard.php");
      } else {
        # Regular user
        $_SESSION["user"] = $row["id"];
        header("Location: index.php");
      }
      exit();
    } else {
      echo "Incorrect credentials!";
    }

    # Close statement
    $stmt->close();
  }
}

# Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Roboto Mono', monospace;
    }

    .register-container {
      max-width: 500px;
      margin: 50px auto;
      padding: 20px;
      box-shadow: 0 0 10px rgb(0, 0, 0, .2);
    }

    html,
    body {
      height: 100%;
      margin: 0;
      font-family: 'Roboto Mono', monospace;
    }

    body {
      display: flex;
      flex-direction: column;
    }

    .navbar,
    .navbar-nav .nav-link {
      font-size: 24px;
    }

    u {
      text-decoration: none;
      border-bottom: 0.5px solid white;
    }

    .nav-link {
      font-size: 24px;
    }

    .navbar .navbar-toggler {
      border-color: rgba(255, 255, 255, 0.1);
    }

    .navbar .navbar-toggler-icon {
      background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28255, 255, 255, 1%29' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
    }

    .container {
      flex: 1;
      padding-bottom: 20px;

    }

    .table {
      margin-top: 79px;
      margin-bottom: 79px;
      width: 100%;
    }

    .table th,
    .table td {
      white-space: normal;
      word-wrap: break-word;
    }

    .table-status {
      font-weight: bold;
    }

    .table-status-available {
      color: green;
    }

    .table-status-reserved {
      color: red;
    }

    .footerNav {
      margin: 30px 0;
    }

    .footerNav ul {
      display: flex;
      justify-content: center;
      list-style-type: none;
    }

    .footerNav ul li a {
      color: white;
      margin: 20px;
      text-decoration: none;
      font-size: 1.3em;
      opacity: 0.7;
      transition: 0.5s;

    }

    .footerNav ul li a:hover {
      opacity: 1;
    }

    .footerBottom {
      background-color: #000;
      padding: 20px;
      text-align: center;
    }

    .footerBottom p {
      color: white;
    }

    .designer {
      opacity: 0.7;
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 400;
      margin: 0px 5px;
    }

    @media (max-width: 700px) {
      .footerNav ul {
        flex-direction: column;
      }

      .footerNav ul li {
        width: 100%;
        text-align: center;
        margin: 10px;
      }
    }

    @media (max-width: 768px) {
      .table {
        display: block;
      }

      .table thead {
        display: none;
      }

      .table tr {
        display: block;
        margin-bottom: 10px;
      }

      .table td {
        display: block;
        text-align: right;
        font-size: 16px;
        border-top: 1px solid #dee2e6;
        position: relative;
        padding-left: 50%;
      }

      .table td::before {
        content: attr(data-label);
        position: absolute;
        left: 0;
        width: 50%;
        padding-left: 10px;
        font-weight: bold;
        text-align: left;
        white-space: nowrap;
      }
    }

    .publisher-link {
      color: black;
      text-decoration: underline;
    }
  </style>
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
              <a class="nav-link text-white" href="register.php"><u>Register</u></a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="login.php"><u>log in</u></a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </div>
  <br>
  <br>
  <br>
  <div class="container">
    <h1>Login Form</h1>
    <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" autocomplete="off">
      <input type="email" placeholder="something@gmail.com" class="form-control mt-3" name="email" value="<?= $email ?>">
      <p class="text-danger"><?= $emailError ?></p>
      <input type="password" placeholder="your password!" class="form-control mt-3" name="password">
      <p class="text-danger"><?= $passError ?></p>
      <input type="submit" value="Login" name="login-btn" class="btn btn-info mt-3">
    </form>
  </div>
  <footer>
    <div class="footerContainer">

      <div class="footerBottom">
        <p>Copyright &copy; 2024; BigLibrary <span class="designer"></span></p>
      </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>