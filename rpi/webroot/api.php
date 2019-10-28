<?php
session_start();
if (isset($_GET['redirect'])) {
  header('location: '.$_GET['redirect']);
  die('<h1>301</h1><a href="'.$_GET['redirect'].'">CLICK HERE</a>.');
}
if (!isset($_SESSION['loggedin'])) {
  die('please login');
}
if (!isset($_GET['key'])) {
  die('No key supplied');
}
if ($_GET['key'] !== $_SESSION['key']) {
  die('Invalid key');
}
//set header
header('Content-Type: application/json');
//check for com-data.json
if (file_exists('/run/com-data.json')) {
  //open com-data.json
  echo file_get_contents('/run/com-data.json');
}
else {
  die('Could not read sensor data');
}

?>
