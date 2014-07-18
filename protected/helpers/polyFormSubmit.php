<?php
/*
 * polyFormSubmit.php *
 * Mark Patterson - Dec 20, 2012
 * (mark@oldensoft.com)
*/
session_start();
require_once('db.php');

    if(isset($_POST['cancel']) && $_POST['cancel'] == 1) {
        return;
    }

    foreach($_POST as $k => $v) {
      //echo $k . '<br />' . $v . '<br /><br />';
        $$k = mysql_real_escape_string(trim($v));
    }
//return;
    if(isset($newGroup) && trim($newGroup) != '') {
        $sql = 'INSERT INTO `emap`.polygon_groups (user_id, name, create_date) VALUES (' . $_SESSION['user']['id'] . ', "' . $newGroup . '", CURDATE());';
        $result = mysql_query($sql);
        $user_group = @mysql_insert_id();
    } elseif(isset($user_group) && trim($user_group) == 'Select') {
        $user_group = 'NULL';
    }

    if(isset($public) && $public == 'on') {
        $public = 1;
    } else {
        $public = 0;
    }
    $sql = 'INSERT INTO `emap`.user_polygons (user_id, user_group, name, bounds, geometry, color, public, create_date) VALUES (' . $_SESSION['user']['id'] . ', ' . $user_group . ', "' . $name . '", "' . $bounds . '", GeomFromText("' . $geometry . '"), "' . $color . '", ' . $public . ', CURDATE());';
    $result = mysql_query($sql);
    return;
?>
