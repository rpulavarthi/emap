<?php
/*
 * polyGet.php *
 * Mark Patterson - Dec 20, 2012
 * (mark@oldensoft.com)
*/
session_start();
require_once('db.php');

    $polygons = array();
    $sql = 'Select';
    $sql .= ' `user_polygons`.id, `user_polygons`.name, `user_polygons`.bounds, AsText(`user_polygons`.geometry) as geometry, `user_polygons`.color,';
    $sql .= ' `user_polygons`.public, `user_polygons`.user_id, `user_polygons`.user_group, `user_polygons`.create_date, `polygon_groups`.name as group_name, `users`.email from user_polygons';
    $sql .= ' left join `users` on `user_polygons`.user_id = `users`.id';
    $sql .= ' left join `polygon_groups` on `user_polygons`.user_group = `polygon_groups`.id';
    $sql .= ' where `user_polygons`.public = 1 or `users`.id = ' . $_SESSION['user']['id'] . ' order by `user_polygons`.user_group;';

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
        <script src='../../global/js/jquery-1.7.2.min.js'></script>
        <script type="text/javascript">
        //script('<php echo $sql; ?>');
            function deletePolygon(key, name, group) {
                var str = "Are you certain you want to delete the '" + name + "' polygon?";
                if(confirm(str)) {
                    document.getElementById("deleteMe").value = key;
                    document.getElementById("deleteGroup").value = group;
                    document.getElementById("polyGetForm").submit();
                }
                return;
            }
            function loadGroup(groupId) {
                var polygonsIndex = 0;
                var polygons = new Array();
                $.each($("input[type='hidden']"), function (index, value) {
                    if($(value).attr("name").substring(0, 5) == 'group' && $(value).val() == groupId) {
                        var key = $(value).attr("name").substring(6, $(value).attr("name").length);
                        var polyString = document.getElementById("polyid_" + key).value;
                        polyString += ";" + document.getElementById("name_" + key).value;
                        polyString += ";" + document.getElementById("color_" + key).value;
                        polyString += ";" + document.getElementById("bounds_" + key).value;
                        polyString += ";" + document.getElementById("geometry_" + key).value;

                        polygons[polygonsIndex] = polyString;
                        polygonsIndex = polygonsIndex + 1;
                    }
                });
                parent.getPolyArray(polygons);
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
            echo '<input type="hidden" id="deleteGroup" name="deleteGroup" value="0">' . "\r\n";
            echo '<table border="0" width="100%">';
            echo '<tr>';
            echo '  <td><b>Group</b></td>';
            echo '  <td><b>Name</b></td>';
            echo '  <td><b>User</b></td>';
            echo '  <td><b>Created</b></td>';
            echo '  <td></td>';
            echo '</tr>';
            $first = true;
            $group = 'null';
            foreach($polygons as $key => $value) {
                $newGroup = trim($polygons[$key]['user_group']);
                if($newGroup != $group || $newGroup == '') {
                    $first = true;
                }
                $group = ((trim($polygons[$key]['user_group']) == '') ? 'null' : trim($polygons[$key]['user_group']));
                echo '<input type="hidden" id="polyid_' . $key . '" name="polyid_' . $key . '" value="' . $key . '">' . "\r\n";
                echo '<input type="hidden" id="group_' . $key . '" name="group_' . $key . '" value="' . $polygons[$key]['user_group'] . '">' . "\r\n";
                echo '<input type="hidden" id="name_' . $key . '" name="name_' . $key . '" value="' . $polygons[$key]['name'] . '">' . "\r\n";
                echo '<input type="hidden" id="color_' . $key . '" name="color_' . $key . '" value="' . $polygons[$key]['color'] . '">' . "\r\n";
                echo '<input type="hidden" id="bounds_' . $key . '" name="bounds_' . $key . '" value="' . $polygons[$key]['bounds'] . '">' . "\r\n";
                echo '<input type="hidden" id="geometry_' . $key . '" name="geometry_' . $key . '" value="' . $polygons[$key]['geometry'] . '">' . "\r\n";
                echo '<tr>' . "\r\n";
                if(is_numeric($group) && $newGroup == $group && !$first) {
                    echo '  <td>' . "\r\n";
                    echo '&nbsp';
                    echo '  </td>' . "\r\n";
                } else {
                    $first = false;
                    echo '  <td>' . "\r\n";
                    echo '<a href="javascript: loadGroup(' . $value['user_group'] . ');">' . $value['group_name'] . '</a>';
                    echo '  </td>' . "\r\n";
                }
                echo '  <td>' . "\r\n";
                echo '<a href="javascript: loadPolygon(' . $key . ');">' . $value['name'] . '</a>' . (is_numeric($value['user_group']) ? ' (' . trim($value['group_name']) . ' <i>group</i>)' : '');
                echo '  </td>' . "\r\n";
                echo '  <td>' . "\r\n";
                echo $value['email'];
                echo '  </td>' . "\r\n";
                echo '  <td>' . "\r\n";
                echo $value['create_date'];
                echo '  </td>' . "\r\n";
                echo '  <td>' . "\r\n";
                if($polygons[$key]['user_id'] == $_SESSION['user']['id']) {
                    echo '<a href="javascript: deletePolygon(' . $key . ', \'' . $value['name'] . '\', \'' . $value['user_group'] . '\');">Delete</a>';
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
