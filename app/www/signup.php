<?php

require_once("../config/db_info.php");

// Signup form session start
// --------------------------------------------------------------------
session_start();

// check if user is already logged in
if (isset($_SESSION['user_id'])) {
  header("Location: manage.php"); //
  exit();
}

// check if form is submitted
if (isset($_POST['submit'])) {
  
  // validate inputs
  $name     = trim($_POST['name']);
  $email    = trim($_POST['email']);
  $password = trim($_POST['password']);
  $confirm_password = trim($_POST['confirm_password']);
  
  $errors = [];
  
  // validation process ------------------------------------------
  // 1. name
  if (empty($name)) {
    $errors[] = 'Name is required';
  }
  
  // 2. email
  if (empty($email)) {
    $errors[] = 'Email is required.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format.';
  }
  
  // 3. password
  if (empty($password)) {
    $errors[] = 'Password is required';
  } elseif (strlen($password) < 8 ) {
    $errors[] = 'Password must be at least 8 characters long.';
  }
  
  // 3.1. password confirmnation
  if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match.';
  }
  
  
  // if there are no errors, create the user
  // --------------------------------------------------------------------
  if (empty($errors)) {
    
    try {
      // connect the database
      $db = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_user, $db_password);
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      echo 'Connection failed: ' . $e->getMessage();
    }
    
    // hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // generate a unique token for email verification
    $token = bin2hex(random_bytes(32));
    
    // insert the user into the database
    $sql = "INSERT INTO users (name, email, password, token) VALUES "
      . "(:name, :email, :password, :token)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':name',     $name);
    $stmt->bindParam(':email',    $email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':token',    $token);
    
    if( $stmt->execute() ) {
      // write a email
      $to = $email ;
      $subject = 'Verify your email';
      $message = "Click the link below to verify your email:\n\n"
        . "http://goto.site/verify_email.php?token=$token";
      $headers = 'From: no-reply@goto.site' . "\r\n" .
        . 'Reply-To: no-reply@goto.site' . "\r\n" .
        . 'X-Mailer: PHP/' . phpversion();
      
      mail($to, $subject, $message, $headers);
      
      // redirect to the login page
      header('Location: login.php');
      exit();
      
    } else {
      //
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
</head>
<body>
    <h1>Sign Up</h1>

    <?php if (!empty($errors)) { ?>
        <div>
            <?php foreach ($errors as $error) { ?>
                <p><?php echo $error; ?></p>
            <?php } ?>
        </div>
    <?php } ?>

    <form method="post">
        <div>
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>">
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
        </div>

        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password">
        </div>

        <div>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>

        <div>
            <button type="submit" name="submit">Sign Up</button>
        </div>
    </form>
</body>
</html>
