<?php
/*
 * polyFormSubmit.php *
 * Mark Patterson - Dec 20, 2012
 * (mark@oldensoft.com)
*/
session_start();
    if(isset($_POST['cancel']) && $_POST['cancel'] == 1) {
        return;
    }
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

    foreach($_POST as $k => $v) {
//      echo $k . '<br />' . $v . '<br /><br />';
        $$k = mysql_real_escape_string(trim($v));
    }

    if(isset($public) && $public == 'on') {
        $public = 1;
    } else {
        $public = 0;
    }
    $sql = 'INSERT INTO `emap`.user_polygons (user_id, name, bounds, geometry, color, public, create_date) VALUES (' . $_SESSION['user']['id'] . ', "' . $name . '", "' . $bounds . '", GeomFromText("' . $geometry . '"), "' . $color . '", ' . $public . ', CURDATE());';
    $result = mysql_query($sql);
    return;
?>
