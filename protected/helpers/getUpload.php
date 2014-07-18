<?php
/*
 * getUpload.php *
 * Mark Patterson - Jan 14, 2013
 * (mark@oldensoft.com)
*/
if (!isset($_SESSION)) { session_start(); }

    ((isset($_GET['havePolygons']) && $_GET['havePolygons'] == 1) ? $polygons = trim($_SESSION['filePolygons']) : '');
    unset($_SESSION["filePolygons"]);
    $returnToMap = ((isset($_GET['havePolygons']) && $_GET['havePolygons'] == 1) ? $_GET['havePolygons'] : '');

    if($returnToMap == '1') {
?>
        <!DOCTYPE html>
            <html>
                <head>
                    <script type="text/javascript">
                        function returnData() {
                            var rawInfoData = '<?php echo $polygons; ?>';
                            parent.displayPolyArray(rawInfoData);
                        }
                    </script>
                </head>
                <body onload="returnData();">
                </body>
            </html>
<?php
    } else {
?>
        <!DOCTYPE html>
        <html>
            <head>
            </head>
            <body>
                <form action="getUploadSubmit.php" method="post" enctype="multipart/form-data">
                    <label for="file">Filename:</label>
                    <input type="file" name="file" id="file"><br>
                    <input type="submit" name="submit" value="Submit">
                </form>
            </body>
        </html>
<?php
    }
?>


