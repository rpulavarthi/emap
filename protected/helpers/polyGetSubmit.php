<?php
/*
 * polyGetSubmit.php *
 * Mark Patterson - Dec 20, 2012
 * (mark@oldensoft.com)
*/
session_start();
require_once('db.php');

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
    $deleteGroupId = mysql_real_escape_string(trim($_POST['deleteGroup']));
    if($deleteId != '' && $deleteId != 0) {

        // check if it's the last of it's group
        $sql = 'select count(*) from `emap`.user_polygons where `user_group` = ' . $deleteGroupId . ';';
        $result = @mysql_query($sql);
        while($row = mysql_fetch_array($result)) {
            $count = $row['count(*)'];
        }
        if($count == 1) {
            $sql = 'delete from `emap`.polygon_groups where `id` = ' . $deleteGroupId . ';';
            $result = @mysql_query($sql);
        }

        $sql = 'delete from `emap`.user_polygons where `id` = ' . $deleteId . ';';
        $result = @mysql_query($sql);
    }
    return header("location: polyGet.php");
?>
