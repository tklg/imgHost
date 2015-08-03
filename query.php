<?php
session_start();
require('includes/config.php');
require('includes/user.php');
if(!isset($_SESSION['ghost-uid'])) $_SESSION['ghost-uid'] = 0;
if(!isset($_SESSION['ghost-uhd'])) $_SESSION['ghost-uhd'] = 0;
$uid = $_SESSION['ghost-uid'];
$uhd = $_SESSION['ghost-uhd'];
$usertable = $database['TABLE_USERS'];
$filetable = $database['TABLE_FILES'];
$db = mysqli_connect($dbhost,$dbuname,$dbupass,$dbname);
date_default_timezone_set('America/New_York');
if ($uhd != null && $uhd != '') $loggedin = true;
else $loggedin = false;
error_reporting(0);

function sanitize($s) {
	return htmlentities(br2nl($s), ENT_QUOTES);
}
function desanitize($s) {
	//return nlTobr(html_entity_decode($s));
	return nlTobr($s);
}
function br2nl($s) {
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $s);
}

if (isset($_GET['upload'])) {

    if ($loggedin) {  
        if (!empty($_FILES)) {
			$tFile = $_FILES['file']['tmp_name'];
			$fExt = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
			//$fName = sanitize($_FILES['file']['name']); //this file will be replaced if a matching file is uploaded
			/*$fName = str_replace("'", "", $fName);
			$fName = str_replace('"', "", $fName);*/
            require('includes/Hashids/HashGenerator.php');
            require('includes/Hashids/Hashids.php');
            $hashids = new Hashids\Hashids('this is such a great salt', 8);
            $fName = strtotime(date("Y-m-d H:i:s"));
            $fName += gettimeofday()['usec'];
            $fName = $hashids->encode((int) $fName);
            $fHash = $fName;
            $fName .= '.'.$fExt;
			$targetPath = 'images/' . $fName; //this file will be replaced if a matching file is uploaded
            
			$fType = sanitize($_FILES['file']['type']);
			$fSize = sanitize($_FILES['file']['size']);
            //$mimeType = image_type_to_mime_type('IMAGETYPE_'.$fType);
            $mimeType = $fType;
			$date = [
				'last_modified' => date("F j, Y, g:i a"),
				'today' => date("Y-m-d H:i:s")
			];
			$lmdf = $date['last_modified'];

			if (!file_exists($targetPath)) {
				if (move_uploaded_file($tFile, $targetPath)) {
                    
                    /*$finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $fileType = finfo_file($finfo, $targetPath);
                    finfo_close($finfo);*/
                    //$mimeType = image_type_to_mime_type(exif_imagetype($targetPath));
                    $sql = "INSERT INTO $filetable (owner, file_name, file_size, file_type, file_self, last_modified, is_shared) VALUES
                        ('$uhd', '$fName', '$fSize', '$mimeType', '$fHash', '$lmdf', 1)";

                    if (mysqli_query($db, $sql)) {
                        echo 1;
                    } else {
                        echo mysql_errno();
                    }
                    
				} else {
					echo 'Upload failed';
				}
			} else {
				echo "File already exists";
			}
		} else {
			echo "Cannot upload nothing.";
		}
    }
}
if (isset($_GET['files'])) {
    if (isset($_GET['reset'])) {
        if ($_GET['reset'] == 'true') {
            $_SESSION['lastloaded'] = 0;
        }
    }
    $lastLoaded = $_SESSION['lastloaded'];
    $path = 'images/';
    $resultDir = mysqli_query($db, "SELECT * from $filetable ORDER BY PID DESC LIMIT $lastLoaded, 40"); //also order by file_type or file_name or last_modified
    //$row = mysqli_fetch_array($resultDir);    
    if (mysqli_num_rows($resultDir) > 0) {
        $r = array();
        while ($row = mysqli_fetch_assoc($resultDir)) {
            if ($row['is_shared'] == 1 || $row['owner'] === $uhd) {
                $r[] = $row;
            } else {
                $row['file_name'] = '••••••••';
                $row['file_self'] = '••••••••';
                $r[] = $row;
            }
        }
        if (sizeof($r) > 0) {
            echo json_encode($r);
        } else {
            echo '[{"PID": "--","owner":"--","file_name":"File Access Denied","file_size":"0","file_type":"--","file_self":"--","is_shared":"1"}]';
        }
    } else {  
        echo '[{"PID": "--","owner":"--","file_name":"No More Images","file_size":"0","file_type":"--","file_self":"--","last_modified":"--","is_shared":"1"}]';  
    }
    $_SESSION['lastloaded'] += 40;
}
if(isset($_POST['toggle_shared'])) {
    $hash = sanitize($_POST['toggle_shared']);
    $res = mysqli_query($db, "SELECT is_shared from $filetable WHERE owner='$uhd' AND file_self='$hash'");
    $row = mysqli_fetch_array($res);
    if($row['is_shared'] == 1) {
        $shared = 0;
    } else {
        $shared = 1;
    }
    if(!mysqli_query($db, "UPDATE $filetable set is_shared = '$shared' WHERE owner='$uhd' AND file_self='$hash'")) {
        echo "Failed to update publicity";
    }
    //echo $hash;
}
if(isset($_POST['remove_image'])) {
    $hash = sanitize($_POST['remove_image']);
    $res = mysqli_query($db, "SELECT file_name from $filetable WHERE owner='$uhd' AND file_self='$hash'");
    $row = mysqli_fetch_array($res);
    if(unlink('images/'.$row['file_name'])) {
        if(!mysqli_query($db, "DELETE FROM $filetable WHERE owner='$uhd' AND file_self='$hash'")) {
            echo "Failed to remove file from DB";
        }
    } else {
        echo "Failed to delete file - Permission Denied";
    }
}
