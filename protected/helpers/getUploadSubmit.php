<?php
/*
 * getUploadSubmit.php *
 * Mark Patterson - Jan 14, 2013
 * (mark@oldensoft.com)
*/
if (!isset($_SESSION)) { session_start(); }

    $file = $_FILES["file"]["tmp_name"];
    $perlFile = realpath(dirname(__FILE__)) . '\mapinfo2latlon.pl';

    if($_SERVER['SERVER_NAME'] == '127.0.0.1') {
        $perlFile = realpath(dirname(__FILE__)) . '\mapinfo2latlon.pl';
        $result = exec("C:\Perl\bin\perl.exe $perlFile $file");
    } else {
        $perlFile = realpath(dirname(__FILE__)) . '/mapinfo2latlon.pl';
        $result = exec("/usr/bin/perl $perlFile $file");
    }
    $_SESSION['filePolygons'] = removeCarriageReturns($result);
    return header("location: getUpload.php?havePolygons=1");

function removeCarriageReturns($string) {
    $clean = '';
    for($i = 0; $i < strlen($string); $i++) {
        if(ord($string[$i]) >= 32 && ord($string[$i]) <= 125) {
            $clean .= $string[$i];
        }
    }
    return $clean;
}
?>
