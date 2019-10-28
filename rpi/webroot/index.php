<?php
session_start();

//check login data
if (isset($_POST['username']) && isset($_POST['password'])) {
  //check login and stuff

  $username = $_POST['username'];
  $password_raw = $_POST['password'];

  //hash password with username as salt;
  $password = hash('sha512', $password_raw.' - '.$username, False);

  //database login info
  $db_servername = "localhost";
  $db_username = "root";
  $db_password = "";
  $db_name = "telemetry";

  // Create connection
  $conn = new mysqli($db_servername, $db_username, $db_password, $db_name);
  // Check connection
  if (!$conn) {
     die("Connection failed: " . mysqli_connect_error());
  }
  //connect and select with prepared statements
  $stmt = $conn->prepare('SELECT password, id FROM users WHERE `username` = ?');
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows == 1) {
    $stmt->bind_result($database_password, $db_id);
    $stmt->fetch();

    //if the passwords match, load session avriables and redirect user
    if ($password === $database_password) {
      $_SESSION['loggedin'] = True;
      $_SESSION['username'] = $_POST['username'];
      $_SESSION['key'] = bin2hex(openssl_random_pseudo_bytes(10));
      $_SESSION['id'] = $db_id;
      header('location: /api.php?redirect=/');
    }
    //if they don't match give error message
    else {
      $msg = 'Incorrect username and/or password';
    }
  }
  else {
    $msg = 'Incorrect username and/or password';
  }
}
elseif (isset($_GET['logout'])) {
  // remove all session variables
  session_unset();
  // destroy the session
  session_destroy();
  header('location: /api.php?redirect=/');
}

if (!isset($_SESSION['loggedin'])) {
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="/icon.png">
    <link rel="stylesheet" href="/css/login.css">
  </head>
  <body id="body">
    <form method="post" action="/">
      <p style="color: white; text-align: center; font-size:40px;">Calandlyceum Kart Telemetry Monitor</p>
      <p style="color: white; text-align: center; height: 20px;font-size:20px;"><?php if(isset($msg)){echo htmlentities($msg);} ?></p>
      <p><input type="text" name="username" placeholder="Username"></p>
      <p><input type="password" name="password" placeholder="Password"></p>
      <p><input type="submit" value="Login"></p>
    </form>
    <footer>
      <p><a href="https://www.joerigeuzinge.nl" target="_blank">&#169; Joeri Geuzinge</a></p>
    </footer>
  </body>
</html>
<?php
  ;die();}
 ?>
 <!DOCTYPE html>
 <html lang="en" dir="ltr">
   <head>
     <meta charset="utf-8">
     <link rel="shortcut icon" href="/icon.png">
     <title>Analytics Panel</title>
     <link rel="stylesheet" href="/css/panel.css">
     <script src="/js/libraries/plotly.min.js" charset="utf-8"></script>
     <script src="/js/panel.js" charset="utf-8"></script>
     <meta http-equiv="refresh" content="240">
   </head>
   <body id="body">
     <header style="top: 0;padding:15px;">
       <div style="font-size: 18px;">
         Welcome <?php echo htmlentities($_SESSION['username']); ?>
         <a href="/?logout=true" class="logout">Logout</a>
         <a href="#" onclick='clear_graph(["sens_temp_1", "speed", "tire_temp_1", "battery_1", "battery_2", "something"], [2, 1, 4, 5, 5, 1])' style="float: right;margin-right:16px;">Clear</a>
       </div>
     </header>
     <div id="main">
       <input type="hidden" id="key" value="<?php echo htmlentities($_SESSION['key']); ?>">
     </div>
     <div id="message_modal">
       <div>
         <span id="close_modal">&times;</span>
         <div id="modal_content">
         </div>
         <button type="button" name="button" onclick="warn = false;document.getElementById('message_modal').style.display = 'none';">Disable warnings</button>
       </div>
     </div>
     <footer style="bottom: 0;text-align:center;">
       <p><a href="https://www.joerigeuzinge.nl" target="_blank" style="color:white;">&copy;&nbsp;Joeri&nbsp;Geuzinge</a></p>
     </footer>
     <script type="text/javascript">
     var key = document.getElementById('key').value;
     var graphlist = ['sens_temp_1','speed', 'tire_temp_1', 'battery_1', 'battery_2', 'something'];
     var graphs = ['sens_temp_1','speed', /*'tire_temp_1', 'battery_1', 'battery_2',*/ 'something'];
     var labels = ['Temperature', 'Speed', 'idfk'];

     for (var i = 0; i < graphlist.length; i++) {
       create_container(graphlist[i]);
     }

     create_graph('tire_temp_1', ['tire 1', 'tire 2', 'tire 3', 'tire 4'], 4);
     create_graph('battery_1', ['smth'], 5);
     create_graph('battery_2', ['smth'], 5);


     for (var i = 0; i < graphs.length; i++) {
       create_graph(graphs[i], labels[i]);
     }

     setInterval(function() {
       //request_data(key, graphs);
       request_data(key, ["sens_temp_1", "speed", "tire_temp_1", "battery_1", "battery_2", "something"]);
     }, 1000);



     //message modal js
     // Get the modal
     var modal = document.getElementById("message_modal");

     // Get the <span> element that closes the modal
     var span = document.getElementById("close_modal");

     // When the user clicks on <span> (x), close the modal
     span.onclick = function() {
       modal.style.display = "none";
     }

     // When the user clicks anywhere outside of the modal, close it
     window.onclick = function(event) {
       if (event.target == modal) {
         modal.style.display = "none";
       }
     }
     </script>
   </body>
 </html>
