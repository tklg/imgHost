<?php
session_start();
require ('../includes/config.php');
//error_reporting($show_errors);//remove for debug
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $title ?> Install</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="initial-scale=1, width=device-width, maximum-scale=1, minimum-scale=1, user-scalable=no">
	<!-- <link rel="stylesheet" href="../css/bootstrap.min.css"> -->
	<link rel="stylesheet" href="../css/font-awesome.min.css">
	<link rel="stylesheet" href="../css/fonts.css">
	<link rel="stylesheet" href="../css/imagehost.css">
    <style type="text/css">
    	#wrapper {
        width: 300px;
        height: auto;
        position: absolute;
        left: 0;right: 0;margin: auto;
        font-family: 'quicksandlight', sans-serif;
    }
    .install-content-title,
    .install-content,
    .btn-submit {
        width: 100%;
        margin-top: 3px;
        color: #bcc6cc;
        -webkit-transition: all .2s ease-in-out;
          transition: all .2s ease-in-out;
    }
    .install-content-title-desc {
    	font-size: 45%;
    	color: #aaa;
    	padding-top: -10px;
    }
    .install-content {
        background: transparent;
        border: none;
        border-bottom: 1px solid #444;
        padding: 4px 0;
        text-indent: 2px;
    }
    .btn-submit {
        background: rgba(255,255,255,.2);
        border: none;
        padding: 5px;
        margin-top: 10px;
    }
    .btn-submit:hover {
        background: rgba(255,255,255,.6);
        cursor: pointer;
    }
    input:active,
    input:focus,
    button:active,
    button:focus {
	    outline: 0 none;
	}
	.btn-moresettings {
		margin-top: -10px;
	}
	.moresettings {
		display: none;
	}
    </style>
    <script type="text/javascript" src="../js/jquery.min.js"></script>
    <script type="text/javascript" src="../js/underscore.min.js"></script>
    <script type="text/javascript" src="../js/backbone.min.js"></script>
</head>
<body>

  <div class="alertbox"></div>

  <?php
	function sanitize($s) {
	  global $db;
	  // return htmlentities(br2nl(addslashes(mysqli_real_escape_string($db, $s))), ENT_QUOTES);
	  return htmlentities(br2nl(mysqli_real_escape_string($db, $s)), ENT_QUOTES);
	}
	function desanitize($s) {
	  //return nlTobr(html_entity_decode($s));
	  return nlTobr($s);
	}
	function br2nl($s) {
	    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $s);
	}
	function nlTobr($s) {
	  return str_replace( "\n", '<br>', $s);
	}

    require('../includes/user.php');

    if (isset($_POST["install"])) {

    $string = '<?php 

    $dbuname = "' . $_POST['dbuname'] . '";

    $dbupass = "' . $_POST['dbupass'] . '";

    $dbhost = "' . $_POST['dbhost'] . '";

    $dbname = "' . $_POST['dbname'] . '";

    ';

    if (!isset($installed)) {

        $fp = fopen("../includes/user.php", "w");

        fwrite($fp, $string);

        fclose($fp);

        $db = mysqli_connect($dbhost,$dbuname,$dbupass,$dbname);
        if (mysqli_connect_errno()) {
        //echo "Failed to connect to MySQL: " . mysqli_connect_error();
        echo "<script type='text/javascript'>d.error('MySQL conn failed: " . mysqli_connect_error() . "')</script>";
        } else {
            //Create MySQL table for users
            $sql = 'CREATE TABLE USERS (
                PID INT NOT NULL AUTO_INCREMENT, 
                PRIMARY KEY(PID),
                name VARCHAR(50), 
                pass VARCHAR(512),
                email VARCHAR(128)
                )';
            $options = [
                'cost' => 11,
            ];
            
            $uname = sanitize($_POST['uname']);
            $upass = password_hash(sanitize($_POST['upass']), PASSWORD_BCRYPT, $options);
            $email = sanitize($_POST['email']);
            $date = date("F j, Y");

            // Execute query
            if (mysqli_query($db,$sql)) {
              echo "<script type='text/javascript'>d.success('Table \"Users\" Created successfully')</script>";
            } else {
              echo "<script type='text/javascript'>d.error('MySQL Query failed: " . mysqli_error($db) . "')</script>";
            }

            $sql = "INSERT INTO USERS (name, pass, email)
                    VALUES ('$uname', 
                    '$upass',
                    '$email')";

            if (mysqli_query($db,$sql)) {
              echo "<script type='text/javascript'>d.success('User \'". $uname ."\' created successfully')</script>";
            } else {
              echo "<script type='text/javascript'>d.error('MySQL Query failed: " . mysqli_error($db) . "')</script>";
            }

            $sql = 'CREATE TABLE FILES (
                PID INT NOT NULL AUTO_INCREMENT, 
                PRIMARY KEY(PID),
                owner VARCHAR(50),
                file_name VARCHAR(100),
                file_type VARCHAR(50),
                file_size DOUBLE(30, 2),
                last_modified VARCHAR(50),
                file_self VARCHAR(100),
                is_shared BOOLEAN
                )';

            if (mysqli_query($db,$sql)) {
              echo "<script type='text/javascript'>d.success('Table \"Files\" Created successfully')</script>";
            } else {
              echo "<script type='text/javascript'>d.error('MySQL Query failed: " . mysqli_error($db) . "')</script>";
            }

            $string = '<?php 
$dbuname = "' . $_POST['dbuname'] . '";
$dbupass = "' . $_POST['dbupass'] . '";
$dbhost = "' . $_POST['dbhost'] . '";
$dbname = "' . $_POST['dbname'] . '";
$installed = true;

';

            $fp = fopen("../includes/user.php", "w");

            fwrite($fp, $string);

            fclose($fp);

            header("Location: ../index.php");

        } 

    } else {
        echo "<script type='text/javascript'>d.error(" . $name . " is already installed.')</script>";
    }

    

}

?>
  <div id="wrapper">
  <?php if (!isset($installed)) { ?>

  <h1><?php echo $name?> Setup</h1>

  <form action="" method="post" name="install" id="install">
  <p>
      <label class="install-content-title" for="uname"><i class="fa fa-user"></i> Set a username.</label>
      <input class="install-content" name="uname" type="text" id="uname" value="" required> 
  </p>
  <p>
      <label class="install-content-title" for="upass"><i class="fa fa-lock"></i> Set a password.</label>
      <input class="install-content" name="upass" type="password" id="upass" required> 
  </p>
  <p>
      <label class="install-content-title" for="email"><i class="fa fa-envelope"></i> Set an email address.</label>
      <input class="install-content" name="email" type="email" id="email" required> 
  </p>
  <p>
      <label class="install-content-title" for="dbuname"><i class="fa fa-cog"></i> Enter the database username.</label>
      <input class="install-content" name="dbuname" type="text" id="dbuname" value="" required> 
  </p>
  <p>
      <label class="install-content-title" for="dbupass"><i class="fa fa-cog"></i> Enter the database password.</label>
      <input class="install-content" name="dbupass" type="password" id="dbupass"> 
  </p>
  <p>
      <label class="install-content-title" for="dbhost"><i class="fa fa-cog"></i> Enter the database host url.</label>
      <input class="install-content" name="dbhost" type="text" id="dbhost" value="" required> 
  </p>
  <p>
      <label class="install-content-title" for="dbname"><i class="fa fa-cog"></i> Enter the database name.</label>
      <input class="install-content" name="dbname" type="text" id="dbname" required> 
  </p>
  <p>
  	<label class="install-content-title" for="dbgrouppass"><i class="fa fa-circle-o"></i> Set a group password.</label>
    <input class="install-content" name="dbgrouppass" type="text" id="dbgrouppass" placeholder="">
  </p>
  <p>
      <button class="btn btn-submit" type="submit" name="install" value="Install"><b>Install</b></button>
  </p>
  </form>

  <?php } else { ?>
  <h1><?php echo $name ?> is already installed.</h1>
  <h3><button class="btn btn-submit" onclick="document.location = '../';">Return to Index</button></h3>
  <?php } ?>
  </div>
</body>
</html>
