<?php
session_start();
if (isset($_GET['teapot'])) {
	header("HTTP/1.1 418 I'm a teapot");
	header("Location: error?418");
}
/*if (!isset($_SESSION['ghost-uid'])) {
    header("Location: login");
    die();
}*/
$alvl = 0;
$uhd = 0;
if(isset($_SESSION['ghost-uid']) && $_SESSION['ghost-uid'] != 0) {
    $alvl = 5;
    $uhd = $_SESSION['ghost-uhd'];
}
$uri = $_SERVER['REQUEST_URI'];
if (strpos($uri, '/') !== false) {
    $uri = explode('/', $uri);
    $uri = $uri[sizeof($uri) - 1];
    $image = $uri;
} else {
    $image = substr($uri, 1);
}
require('includes/config.php');
require('includes/user.php');
$usertable = $database['TABLE_USERS'];
$filetable = $database['TABLE_FILES'];
$db = mysqli_connect($dbhost,$dbuname,$dbupass,$dbname);
//error_reporting(0);//remove for debug
error_reporting(E_ALL);

if ($image != '') {

    $result = mysqli_query($db, "SELECT * from $filetable where file_self = '$image'");  
    $row = mysqli_fetch_array($result);
    if ($row['is_shared'] == 1 || $row['owner'] === $uhd) {
        header("Content-type: ".$row['file_type']);
        readfile('images/'.$row['file_name']);
    } else {
        //header("HTTP/1.1 403 Forbidden");
        header('Location: error?403');
    }
    die();
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?php echo $title ?></title>
    <?php require('includes/header.php'); ?>	

    <script type="text/javascript" src="//code.jquery.com/jquery-2.1.4.min.js"></script>
  </head>
<body>

	<div class="alertbox"></div>
    
    <header>
        <h1 class="title"><?php echo $name ?></h1>
        <div class="button-group-h" id="mainmenu">
        <?php if ($alvl > 0) { ?>
            <button class="btn capitalize">Hello, <?php echo $uhd ?></button>
            <button class="btn" id="btn-upload">Upload</button>
            <button class="btn" onclick="window.location = 'uauth.php?logout'">Logout</button>
        <?php } else { ?>
            <button class="btn" onclick="window.location = 'login.php'">Login</button>
        <?php } ?>
        </div>
        <div class="hamburger">
            <button class="btn" onclick="toggleBurger();">
                <i class="fa"></i>
                <div class="hamburger-line" id="hl-1"></div>
                <div class="hamburger-line" id="hl-2"></div>
                <div class="hamburger-line" id="hl-3"></div>
            </button>
        </div>
    </header>
    <section class="grid-box">
        <section class="grid">
            <table>
            <tr class="tbl-header">
            <td class="tbl-header-content tbl-content">File Name</td>
            <td class="tbl-header-content tbl-content">File Size</td>
            <td class="tbl-header-content tbl-content">Created On</td>
            <td class="tbl-header-content tbl-content">Owner</td>
            <td class="tbl-header-content tbl-content">Publicity</td>
            <?php if ($alvl > 0) { ?>
            <td class="tbl-header-content tbl-content tbl-col-del">Del</td>
            <?php } ?>
            </tr>
            </table>
        </section>
        <button class="btn loadmore" onclick="loadMore()">Load More</button>
    </section>
    
    <?php if ($alvl > 0) { ?>
    <section class="dz">
    <form action="query.php?upload" class="dropzone" id="dropzone"></form>
    </section>
    <section class="dropzone-previews">

    </section>
    <?php } ?>
    
    <script type="text/template" id="thumbnails">
	<tr id="row-<%= model.get('hash') %>"><td class="tbl-content"><%= model.get('name') %></td>
    <td class="tbl-content"><%= model.get('size') %> <%= model.get('units') %></td>
    <td class="tbl-content"><%= model.get('lmdf') %></td>
    <td class="tbl-content"><%= model.get('owner') %></td>
    <td class="tbl-content" id="pub-<%= model.get('hash') %>"><%= model.get('publicity') %></td>
    <?php if ($alvl > 0) { ?>
<td class="tbl-content tbl-col-del"><a href="#" id="del-<%= model.get('hash') %>"><i class="fa fa-trash"></i></a></td>
    <?php } ?>
    </tr>
	</script>
    <script type="text/template" id="dz-template">
        <div class="dz-details">
            <span class="dz-filename" data-dz-name=""></span>
            <span class="dz-size" data-dz-size=""></span>
            <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
        </div>
    </script>
    
    <script src="js/underscore.min.js"></script>
    <script src="js/backbone.min.js"></script>
    <script type="text/javascript" src="js/jquery.nicescroll.js"></script>
    <!-- <script type="text/javascript" src="js/imagesloaded.min.js"></script> -->
    <script type="text/javascript" src="js/showlog.js"></script>
    <script type="text/javascript" src="js/dropzone.js"></script>
    <?php if ($alvl > 0) { ?>
    <?php } ?>
    <script type="text/javascript" src="js/imagehost.js"></script>
    <script type="text/javascript">
    var logged = false;
    $(document).ready(function() {
        <?php if($alvl>0) echo 'logged = true;'; ?>
        init();
    });
    </script>

</body>
</html>