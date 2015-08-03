<?php
session_start();
require ('includes/config.php');
//error_reporting(0);//remove for debug
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?php echo $title ?></title>
    <?php //require('includes/header.php'); ?>
    <style type="text/css">
    html, body {
    	height: 100%;
    	width: 100%;
    	padding: 0;
    	margin: 0;
    	color: #ccc;
		font-family: sans-serif;
		-webkit-font-smoothing: antialiased;
		background: #1a1a1a;
		overflow: hidden;
    }
    a {
		text-decoration: none;
		color: #80069D
		-webkit-transition: all .1s ease-in-out;
	            transition: all .1s ease-in-out;
	}
	a:hover {
		color: #ccc;
	}
	::selection {
		background: #4A043D;
	}
    #wrapper {
    	height: 50%;
    	width: 50%;
    	position: absolute;
    	top: 0; bottom: 0; left: 0; right: 0;
    	margin: auto;
    }
    .errornumber {
    	font-size: 100pt;
    	padding: 0;
    	margin: 0;
    	/*font-family: 'quicksandlight', sans-serif;*/
    }
    .errornumber::first-letter {
    	color: #80069D;
    }
    .errordesc {
    	margin: 0;
        padding-left: 5px;
    }
        input:active,
input:focus,
button:active,
button:focus {
    outline: 0 none;
    border-bottom: 1px solid #80069D;
}
.btn:hover {
    background: #80069D;
}
.btn {
    cursor: pointer;
    border-radius: 6px;
    -webkit-transition: all .2s ease-in-out;
          transition: all .2s ease-in-out;
    padding: 11px 20px;  
    margin: 0 4px;
/*    background: rgba(255,255,255,.1);*/
    background: transparent;
    border: 1px solid #80069D;
    color: #ccc;
}
    </style>

    <script type="text/javascript" src="js/jquery.min.js"></script>
  </head>
<body>

	<?php
	$er = 'Error';
	$de = 'Something is wrong and we don\'t know what.';
	if(isset($_GET['404'])) {$er = '404'; $de = 'The requested page does not exist.';}
	if(isset($_GET['500'])) {$er = '500'; $de = 'Something is broken!';}
	if(isset($_GET['403'])) {$er = '403'; $de = 'Access is forbidden.';}
	if(isset($_GET['418'])) {$er = '418'; $de = 'I\'m a teapot.<br><br>The resulting entity body is short and stout.<br>Tip me over and pour me out.';}
	if(isset($_GET['502'])) {$er = '502'; $de = 'Bad gateway.';}
	?>
	<div id="wrapper">
	<p class="errornumber">
	<?php
	echo $er;
	?>
	</p>
	<p class="errordesc">
	<?php
	echo $de;
	?>
	</p>
	<p class="errorsol">
        <a onclick="window.history.back()"><button class="btn">Go Back</button></a>
	</p>
	</div>
  	<script type="text/javascript" src="js/showlog.js"></script>
    <!-- <script type="text/javascript" src="js/foxfile.js"></script> -->
    <script type="text/javascript">
    <?php
    if(isset($_GET['404'])) echo 'var code = 404;';
	if(isset($_GET['500'])) echo 'var code = 500;';
	if(isset($_GET['403'])) echo 'var code = 403;';
	?>
    document.title = 'Error ' + code;
    </script>
</body>
</html>
