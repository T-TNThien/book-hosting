<?php
  session_start();
  require_once 'db.php';

  if (isset($_GET['redirect'])) {
    $_SESSION['redirect_after_login'] = $_GET['redirect'];
  }
  
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION["user_id"] = $user['id'];
      $_SESSION["username"] = $user['username'];
      $_SESSION["is"] = true;
      $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
      unset($_SESSION['redirect_after_login']);
      header("Location: " . $redirect);
      exit();
    } else {
      $_SESSION["is"] = false;
    }
  }
?>
<!DOCTYPE html>
<html lang="en" class="w-100 h-100">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      rel="stylesheet"
      href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"
      integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU"
      crossorigin="anonymous"
    />
    <link
      rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
    />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="../css/style.css" />
    <title>Login</title>
  </head>
  <body class="w-100 h-100 bg-3">
    <div class="d-flex align-items-center w-100 h-100">
      <div class="container bg-white shadow rounded">
        <div class="row justify-content-center">
          <div class="col-md-7 col-lg-5">
            <div class="login-wrap p-4 p-md-5">
              <div
                class="icon d-flex align-items-center justify-content-center"
              >
                <span class="fa fa-user-o"></span>
              </div>
              <h3 class="text-center mb-4">Sign In</h3>
              <form action="login.php" method="post" class="login-form">
                <div class="form-group">
                  <input
                    id="username"
                    name="username"
                    type="text"
                    class="form-control rounded-left"
                    placeholder="Username"
                    required=""
                    maxlength="30"
                  />
                </div>
                <div class="form-group d-flex">
                  <input
                    id="password"
                    name="password"
                    type="password"
                    class="form-control rounded-left"
                    placeholder="Password"
                    required=""
                    maxlength="64"
                    minlength="6"
                  />
                </div>
                <a href="register.php" class="form-group btn btn-link text-secondary">Create an account</a>
                <div class="alert alert-danger" id="wrongInput" style="display: <?php echo isset($_SESSION["is"]) && !$_SESSION["is"] ? "block" : "none" ?>;">Username or password is incorrect.</div>
                <div class="form-group">
                  <button
                    type="submit"
                    class="form-control btn btn-primary rounded submit px-3 bg-2"
                  >
                    Login
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
