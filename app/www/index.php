<?php
// URL Shortener prototype
// --------------------------------------------------------------------------

// Database Connection
// --------------------------------------------------------------------------
require_once('../config/db_info.php') ;
$db = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_user, $db_password);

// URLからショートコードに該当する部分を抜き出す
// --------------------------------------------------------------------------
if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
  $request_uri = $_SERVER['REQUEST_URI'];
  if (preg_match('/^\/([a-zA-Z0-9]+)$/', $request_uri, $matches)) {
    $short_code = $matches[1];
  } else {
    $error_message = "short code you specified is invalid.";
  }
} else {
  $error_message = "short code you specified is invalid.";
}

// exit program when short code is invalid.
if (isset($error_message)) {
  header('HTTP/1.0 400 Bad Request');
  echo $error_message;
  exit();
}

// Insert Access Log
// --------------------------------------------------------------------------
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$ip_address = $_SERVER['REMOTE_ADDR'];

$stmt = $db->prepare("INSERT INTO access_logs (short_code, user_agent, ip_address) VALUES (:short_code, :user_agent, :ip_address)");
$stmt->bindParam(':short_code', $short_code);
$stmt->bindParam(':user_agent', $user_agent);
$stmt->bindParam(':ip_address', $ip_address);
$stmt->execute();

// Search short code from urls table
// --------------------------------------------------------------------------
$stmt = $db->prepare("SELECT * FROM urls WHERE short_code = :short_code");
$stmt->bindParam(":short_code", $short_code);
$stmt->execute();
$result = $stmt->fetchAll();


// Redirect URL
// --------------------------------------------------------------------------
if (count($result) >0 ) {
  
  // EXIST:SHORT CODE
  $url = $result[0]['url'];
  header("Location: {$url}");
  exit;
  
} else {
  // MISS :SHORT CODE
  header('HTTP/1.0 400 Bad Request');
  echo "Short code you specified is missing";
  exit;
}
