<?php
require_once "connection.php";

$publisher_name = mysqli_real_escape_string($conn, $_GET['publisher_name']);

$sql = "SELECT * FROM `library` WHERE `publisher_name` = '$publisher_name'";
$result = mysqli_query($conn, $sql);

$layout = "";
if (mysqli_num_rows($result) == 0) {
  $layout .= "<p>No results found for publisher: $publisher_name</p>";
} else {
  $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

  foreach ($rows as $key => $value) {
    $layout .= "
    <div class='col-12 col-md-6 mb-4'>
      <div class='card h-100'>
        <div class='row g-0 h-100'>
          <div class='col-md-4'>
            <img src='{$value["image"]}' class='img-fluid rounded-start h-60' alt='...'>
          </div>
          <div class='col-md-8'>
            <div class='card-body'>
              <h5 class='card-title'>{$value["title"]}</h5>
              <p class='card-text'>ISBN Code: {$value["isbn_code"]}</p>
              <p class='card-text'>Description: {$value["short_description"]}</p>
              <p class='card-text'>Author Fn: {$value["author_first_name"]}</p>
              <p class='card-text'>Author Ln: {$value["author_last_name"]}</p>
              <p class='card-text<p class='card-text'><a href='publisher.php?publisher_name={$value["publisher_name"]}' class='publisher-link'>Publisher: {$value["publisher_name"]}</a></p>
              <p class='card-text'>Publisher addr: {$value["publisher_address"]}</p>
              <p class='card-text'>Publish date: {$value["publish_date"]}</p>
              
              <a href='details.php?id={$value["id"]}' class='btn btn-dark'>Show Details</a>
            </div>
          </div>
        </div>
      </div>
    </div>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Media by <?= htmlspecialchars($publisher_name) ?></title>
  <link rel="stylesheet" href="index.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Roboto Mono', monospace;
    }

    .navbar,
    .navbar-nav .nav-link {
      font-size: 24px;
    }

    .nav-link {
      font-size: 24px;

    }

    u {
      text-decoration: none;
      border-bottom: 0.5px solid white;
    }

    .navbar .navbar-toggler {
      border-color: rgba(255, 255, 255, 0.1);
    }

    .navbar .navbar-toggler-icon {
      background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28255, 255, 255, 1%29' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
    }

    .card {
      box-shadow: 0 0 10px rgb(0, 0, 0, .2);
    }

    .card-body,
    .card-footer {
      font-size: 16px;
    }

    .container {
      padding-top: 30px;
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
  <div class="container">
    <h1>Media published by <?= htmlspecialchars($publisher_name) ?></h1>
    <br>
    <br>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 g-4">
      <?= $layout ?>
    </div>
  </div>
  <br>
  <br>
  <footer>
    <div class="footerContainer">

    </div>
    <div class="footerBottom">
      <p>Copyright &copy; 2024; BigLibrary <span class="designer"></span></p>
    </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>