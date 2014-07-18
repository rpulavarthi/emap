<?php
/*
 * db.php *
 * Mark Patterson - Dec 28, 2012
 * (mark@oldensoft.com)
*/
    if($_SERVER['SERVER_NAME'] == '127.0.0.1') {
        $link = mysql_connect("localhost", "root", "");
    } else {
        $link = mysql_connect("localhost", "dbuser", "ericsson123");
    }
    if(!$link) {
        die('Failed to connect to server.');
    }
    //Select database
    $db = mysql_select_db("emap");
    if(!$db) {
        die("Unable to select database.");
    }

?>
