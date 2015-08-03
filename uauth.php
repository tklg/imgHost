<?php 
session_start();

require('includes/config.php');
require('includes/user.php');
$usertable = $database['TABLE_USERS'];
$db = mysqli_connect($dbhost,$dbuname,$dbupass,$dbname);

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

if(isset($_POST['check_username'])) {

	$username = sanitize($_POST['username']);  
	 
	$result = mysqli_query($db, "SELECT name from $usertable where name = '". $username . "'");  
	
	if(mysqli_num_rows($result)>0){  
	    echo 0;  
	} else {  
	    echo 1;  
	}
}
if(isset($_POST['login'])) {
	$username = sanitize($_POST['username']);
	$user = mysqli_query($db, "SELECT * from $usertable where name = '". $username . "'");
	$row = mysqli_fetch_array($user);
	$password = sanitize($_POST['password']);
	$passToMatch = $row['pass'];

	if (password_verify($password, $passToMatch)) {
		$_SESSION['ghost-uid'] = $row['PID'];
		$_SESSION['ghost-username'] = $row['name'];
		$_SESSION['ghost-uhd'] = $row['name'];
        $_SESSION['lastloaded'] = 0;
		echo 'valid';
	} else {
		echo 'Invalid username or password';
	}
}
/*if(isset($_POST['register'])) {
	$gp = false;
	//if (validate_username($_POST['username'])) {
		if (!$gp || $v) {
	        $username = sanitize($_POST['username']);  
			$result = mysqli_query($db, "SELECT name from $usertable where name = '$username'");  
			
			if(mysqli_num_rows($result) > 0){  
			    echo "Username is not available";
			} else {  
				$uname = sanitize($_POST['username']);
				$upass = password_hash(sanitize($_POST['password']), PASSWORD_BCRYPT);
				$email = sanitize($_POST['email']);
				$date = date("F j, Y");
				$sql = "INSERT INTO $usertable (name, pass, email)
		                VALUES ('$uname', 
		                '$upass',
		                '$email')";
				if (mysqli_query($db,$sql)) {

		        } else {
		            echo 'MySQL Query failed: ' . mysqli_error($db);
		        }
		        echo 'valid';
		    }
		} else {
			echo 'Invalid group password,<br>or something is broken.';
		}
}*/
if(isset($_GET['logout'])) {
    //$_SESSION['ghost-uid'] = null;
    foreach($_SESSION as $sess) {
    	$sess = null;
    }
	session_destroy();
	header('Location: /imghost');
}