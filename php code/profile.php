<?php
session_start();

if (!isset($_SESSION["user"]) && !isset($_SESSION["admin"])) {
  header("Location: login.php");
  exit();
}

require_once "connection.php"; // Adjusted to include the correct connection file


if (isset($_SESSION["admin"])) {
  $session = $_SESSION["admin"];
  $backTo = "dashboard.php";
} else {
  $session = $_SESSION["user"];
  $backTo = "home.php";
}

# Function to sanitize input
function cleanInput($input)
{
  return htmlspecialchars(strip_tags(trim($input)));
}

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $session);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (isset($_POST["edit"])) {
  $fname = cleanInput($_POST["first_name"]);
  $lname = cleanInput($_POST["last_name"]);
  $email = cleanInput($_POST["email"]);
  $date_of_birth = cleanInput($_POST["date_of_birth"]);


  if ($_FILES["picture"]["error"] == 4) {
    $sqlUpdate = "UPDATE users SET first_name = ?, last_name = ?, date_of_birth = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("ssssi", $fname, $lname, $date_of_birth, $email, $session);
  } else {
    if ($row["picture"] != 'avatar.jpg') {
      unlink("pictures/" . $row["picture"]);
    }
    $sqlUpdate = "UPDATE users SET first_name = ?, last_name = ?, date_of_birth = ?, email = ?, picture = ? WHERE id = ?";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("sssssi", $fname, $lname, $date_of_birth, $email, $picture[0], $session);
  }

  if ($stmt->execute()) {
    header("Location: " . $backTo);
    exit();
  }
  $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <style>
    body {
      font-family: 'Roboto Mono', monospace;
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

    .form-control,
    .btn {
      font-size: 16px;
    }

    .alert {
      margin-top: 20px;
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

    .publisher-link {
      color: black;
      text-decoration: underline;
    }
  </style>
  <div>
    <nav class="navbar navbar-expand-lg bg-dark">
      <div class="container-fluid">
        <a class="navbar-brand text-white" href="index.php">BIG 🕮 LIBRARY</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link text-white" aria-current="page" href="index.php"><u>Home</u></a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="create.php"><u>Create Product</u></a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </div>
  <br>
  <br>
  <div class="container">
    <h1>Edit profile!</h1>

    <form enctype="multipart/form-data" method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
      <input type="text" name="first_name" class="form-control mb-3" value="<?= htmlspecialchars($row["first_name"]) ?>">
      <input type="text" name="last_name" class="form-control mb-3" value="<?= htmlspecialchars($row["last_name"]) ?>">
      <input type="email" name="email" class="form-control mb-3" value="<?= htmlspecialchars($row["email"]) ?>">
      <input type="date" name="date_of_birth" class="form-control mb-3" value="<?= htmlspecialchars($row["date_of_birth"]) ?>">
      <input type="file" name="picture" class="form-control mb-3">
      <input type="submit" name="edit" value="Update profile" class="btn btn-warning">
    </form>
    <br>
    <br>
    <br>
  </div>
  <footer>
    <div class="footerContainer">
      <div class="footerBottom">
        <p>Copyright &copy; 2024; BigLibrary <span class="designer"></span></p>
      </div>
  </footer>
</body>

</html>