<?php
/*
 * polyGetSubmit.php *
 * Mark Patterson - Dec 20, 2012
 * (mark@oldensoft.com)
*/
session_start();

foreach($_SESSION as $k => $v) {
    foreach($v as $ke => $va) {
//        echo $k . ' - ' . $ke . ', ' . $va . '<br />';
    }
}
foreach($_POST as $k => $v) {
//    echo $k . ',  ' . $v . '<br /><br />';
}

    if(isset($_POST['cancel']) && $_POST['cancel'] == 1) {
        return;
    }

    $deleteId = mysql_real_escape_string(trim($_POST['deleteMe']));
    if($deleteId != '' && $deleteId != 0) {
        echo $deleteId;
        if($_SERVER['SERVER_NAME'] == '127.0.0.1') {
            $link = mysql_connect("localhost", "root", "");
        } else {
            $link = mysql_connect("localhost", "dbuser", "ericsson123");
        }
        if(!$link) {
            die('Failed to connect to server');
        }
        //Select database
        $db = mysql_select_db("emap");
        if(!$db) {
            die("Unable to select database");
        }
        $sql = 'delete from `emap`.user_polygons where id = ' . $deleteId . ';';
        $result = mysql_query($sql);
    }
    return header("location: polyGet.php");
?>
