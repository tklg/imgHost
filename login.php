<?php
session_start();
if (isset($_SESSION['ghost-uid']) && $_SESSION['ghost-uid'] != 0) {
    header("Location: index");
    die();
}
require ('includes/config.php');
//error_reporting(0);//remove for debug
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?php echo $title ?> - Login</title>
    <?php require('includes/header.php'); ?>
    <style type="text/css">
    html, body {
        height: 100%;
    }
	#wrapper {
        width: 300px;
        height: 250px;
        position: absolute;
        top: 20%;
/*        top:0;bottom:0;*/
        left: 0;right: 0;margin: auto;
        font-family: 'quicksandlight', sans-serif;
        padding: 30px;
/*        background: rgba(255,255,255,.05);*/
        border: 1px solid #80069D;
        border-radius: 10px;
    }
    .loginpage-title {
    	font-size: 300%;
        margin: 0;
        margin-bottom: 10px;
        color: #999999;
        text-align: center;
    }
    .login-content-title,
    .login-content,
    .btn-submit {
        width: 100%;
        margin-top: 3px;
        color: #999999;
        -webkit-transition: all .2s ease-in-out;
          transition: all .2s ease-in-out;
    }
    .login-content {
/*        background: rgba(255,255,255,.05);*/
        background: transparent;
        border: 1px solid transparent;
        border-bottom: 1px solid rgba(255,255,255,.1);
        padding: 4px 0;
        text-indent: 5px;
    }
    .btn-submit {
        background: rgba(255,255,255,.1);
        padding: 10px 5px;
        margin-top: 10px;
        border: 1px solid transparent;
    }
    .btn-submit:hover {
/*        background: rgba(255,255,255,.2;*/
        background: #80069D;
        cursor: pointer;
    }
    input:active,
    input:focus,
    button:active,
    button:focus {
    outline: 0 none;
/*    background: rgba(255,255,255,.15);*/
/*    border: 1px solid #80069D;*/
        border-bottom: 1px solid #80069D;
	}
    .btn-submit:active {
        border: 1px solid #4A043D;
        box-shadow: inset 0 0 3px 3px #4A043D;
        background: #80069D;
    }
	.link {
		-webkit-transition: all .2s ease-in-out;
          transition: all .2s ease-in-out;
	}
    </style>

    <script type="text/javascript" src="js/jquery.min.js"></script>
  </head>
<body>

	<div class="alertbox"></div>

	<div id="wrapper">
	
		<p class="loginpage-title"><?php echo $name; ?></p>
		<p>
			<label class="login-content-title" for="uname"><i class="fa fa-user brightcolor"></i> Username</label>
			<input class="login-content" name="unamesub" type="text" id="uname" value="" placeholder="" required onFocus="setCookie('username', '', 0);"onBlur="check_availability()" />
		</p>
		<p>				
			<label class="login-content-title" for="upass"><i class="fa fa-unlock-alt brightcolor"></i> Password</label>
			<input class="login-content" name="upasssub" type="password" id="upass" placeholder="" required >
		</p>

		<button type="submit" name="login" class="btn btn-submit" onclick="check_validity()"><b>Login</b></button>
		<!--<div class="link sulink">Need an account? <a href="register">Sign Up!</a></div>-->
		<!--<div class="link sulinksub login-user-show">Not you? <a href="#" onclick="removeUser()">Change User</a></div>-->

	</div>

	<script type="text/javascript">
	var uexists = false;
	var using_cookie = false;
	function check_availability() {  

        var username = $('#uname').val();

        if(username.length > 0) {
  
	        $.post("<?php echo $foxfile_install_dir; ?>uauth.php", { 
	        	check_username: 'yes',
	        	username: username
	        	},  
	            function(result) {  
	                if (result == 0) {  
	                    //$('#uname').css('border-bottom','#99c68e solid 1px'); //light green
				        setCookie("username", username, 28);
	                    uexists = true;
	                } else {  
	                    //$('#uname').css('border-bottom','#e77471 solid 1px'); //light red
	                    uexists = false;
	                    console.error(result);
	                }  
	        });  
	    }
	}
	function check_validity() {
		var username = $('#uname').val(),
			password = $('#upass').val();
        if (using_cookie) uexists = true;
		if (username.length < 1 || password.length < 1) {
			d.warning("Please fill in both fields.");
		} else {
			if (uexists) {
				d.info("Checking validity...");
				$.post("<?php echo $foxfile_install_dir; ?>uauth.php", {
					login: 'yes',
					username: username,
					password: password
				},
				function (result) {
					if (result == 'valid') {
						$('#uname').css('border-bottom','#99c68e solid 1px');
						$('#upass').css('border-bottom','#99c68e solid 1px');
						//d.info("Valid!");
						d.success("Logging in...");
						setTimeout(function() {
                            var urla = window.location.href.toString().split('/');
                            var url = '';
                            for (i = 0; i < urla.length - 1; i++) {
                                url += urla[i] + '/';
                            }
							document.location = url;
						}, 1000);
					} else {
						$('#pass').css('border-bottom','#e77471 solid 1px')
						console.info(result);
						d.error(result);
					}
				});
			} else {
				d.error("Username not found.");
			}
		}
	}
	if ($('.footer').height() > 0) {
		$(".alertbox").css("bottom", 60);
	} else {
		$(".alertbox").css("bottom", 20);
	}
	$(document).keydown(function(e) {
		if (e.keyCode == 13) { //enter
            check_availability()
			$('.btn-submit').click();
		}
	});

	function getCookie(c_name) {
	    var c_value = document.cookie;
	    var c_start = c_value.indexOf(" " + c_name + "=");
	    if (c_start == -1) {
	        c_start = c_value.indexOf(c_name + "=");
	    }
	    if (c_start == -1) {
	        c_value = null;
	    } else {
	        c_start = c_value.indexOf("=", c_start) + 1;
	        var c_end = c_value.indexOf(";", c_start);
	        if (c_end == -1) {
	            c_end = c_value.length;
	        }
	        c_value = unescape(c_value.substring(c_start, c_end));
	    }
	    return c_value;
	}
	function setCookie(c_name, value, exdays) {
	    var exdate = new Date();
	    exdate.setDate(exdate.getDate() + exdays);
	    var c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
	    document.cookie = c_name + "=" + c_value;
	}
	function removeUser() {
		setCookie('username', '', 28);
		location.reload();
	}
	$(document).ready(function() {
		var u = getCookie("username");
	    if (u != null && u != "") {
	        using_cookie = true;
	        $('#uname').val(u);
	        $('.login-user-show').css("display", 'block');
	        $('.login-nouser').css("display", 'none');
	        $('#upass').focus();
	    } else {
            $('#uname').focus();
        }
	});
</script>
  	<script type="text/javascript" src="js/showlog.js"></script>

</body>
</html>
