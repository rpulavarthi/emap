<?php
/*
 * polyGet.php *
 * Mark Patterson - Dec 20, 2012
 * (mark@oldensoft.com)
*/
session_start();
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
    $polygons = array();
    $sql = 'Select';
    $sql .= ' `user_polygons`.id, `user_polygons`.name, `user_polygons`.bounds, AsText(`user_polygons`.geometry) as geometry, `user_polygons`.color,';
    $sql .= ' `user_polygons`.public, `user_polygons`.user_id, `user_polygons`.create_date, `users`.email from user_polygons';
    $sql .= ' left join `users` on `user_polygons`.user_id = `users`.id';
    $sql .= ' where `user_polygons`.public = 1 or `users`.id = ' . $_SESSION['user']['id'] . ';';

    $result = mysql_query($sql);
    while($row = mysql_fetch_array($result)) {
        foreach($row as $k => $v) {
            if(!(is_numeric($k))) {
                $polygons[$row['id']][$k] = $v;
            }
        }
    }

?>

<!DOCTYPE html>
<html>
    <head>
        <script type="text/javascript">
        script('<php echo $sql; ?>');
            function deletePolygon(key, name) {
                var str = "Are you certain you want to delete the '" + name + "' polygon?";
                if(confirm(str)) {
                    document.getElementById("deleteMe").value = key;
                    document.getElementById("polyGetForm").submit();
                }
                return;
            }
            function loadPolygon(key) {
                parent.polygonId = document.getElementById("polyid_" + key).value;
                parent.polygonName = document.getElementById("name_" + key).value;
                parent.polygonColor = document.getElementById("color_" + key).value;
                parent.polygonBounds = document.getElementById("bounds_" + key).value;
                parent.polygonGeometry = document.getElementById("geometry_" + key).value;
                parent.getPoly(parent.polygonId);
            }

        </script>
    </head>
    <body>
        <form id="polyGetForm" name="polyGetForm" action="polyGetSubmit.php" method="post">
<?php
            echo '<input type="hidden" id="deleteMe" name="deleteMe" value="0">' . "\r\n";
            echo '<table border="0" width="100%">';
            echo '<tr>';
            echo '  <td><b>Id</b></td>';
            echo '  <td><b>Name</b></td>';
            echo '  <td><b>User</b></td>';
            echo '  <td><b>Created</b></td>';
            echo '  <td></td>';
            echo '</tr>';
            foreach($polygons as $key => $value) {
                echo '<input type="hidden" id="polyid_' . $key . '" name="polyid_' . $key . '" value="' . $key . '">' . "\r\n";
                echo '<input type="hidden" id="name_' . $key . '" name="name_' . $key . '" value="' . $polygons[$key]['name'] . '">' . "\r\n";
                echo '<input type="hidden" id="color_' . $key . '" name="color_' . $key . '" value="' . $polygons[$key]['color'] . '">' . "\r\n";
                echo '<input type="hidden" id="bounds_' . $key . '" name="bounds_' . $key . '" value="' . $polygons[$key]['bounds'] . '">' . "\r\n";
                echo '<input type="hidden" id="geometry_' . $key . '" name="geometry_' . $key . '" value="' . $polygons[$key]['geometry'] . '">' . "\r\n";
                 echo '<tr>' . "\r\n";
                echo '  <td>' . "\r\n";
                echo $key;
                echo '  </td>' . "\r\n";
                echo '  <td>' . "\r\n";
                echo '<a href="javascript: loadPolygon(' . $key . ');">' . $value['name'] . '</a>';
                echo '  </td>' . "\r\n";
                echo '  <td>' . "\r\n";
                echo $value['email'];
                echo '  </td>' . "\r\n";
                echo '  <td>' . "\r\n";
                echo $value['create_date'];
                echo '  </td>' . "\r\n";
                echo '  <td>' . "\r\n";
                if($polygons[$key]['user_id'] == $_SESSION['user']['id']) {
                    echo '<a href="javascript: deletePolygon(' . $key . ', \'' . $value['name'] . '\');">Delete</a>';
                } else {
                    echo '&nbsp;';
                }
                echo '  </td>' . "\r\n";
                echo '</tr>' . "\r\n";
            }
            echo '</table>';
?>
        </form>
    </body>
</html>
