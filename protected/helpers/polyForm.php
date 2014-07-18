<?php
/*
 * polyForm.php *
 * Mark Patterson - Dec 20, 2012
 * (mark@oldensoft.com)
*/
session_start();
require_once('db.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <script src='../../global/js/jquery-1.7.2.min.js'></script>
        <script src='../../global/js/bootstrap.min.js'></script>
        <script src="../../global/js/bootstrap-colorpicker.js"></script>
        <link rel="stylesheet" href="../../global/css/colorpicker.css">
        <link rel="stylesheet" href="../../global/css/bootstrap.min.css">
        <script type="text/javascript">
            $(function(){
                window.prettyPrint && prettyPrint()
                $('#cp3').colorpicker({
                format: 'hex'
                });
                $('#cp3').colorpicker().on('changeColor', function(ev){
                    bodyStyle.backgroundColor = ev.color.toHex();
                });
            });
            function changeGroup() {
                if("Select" != document.getElementById('user_group').options[document.getElementById("user_group").selectedIndex].value) {
                    document.getElementById('newGroup').disabled = true;
                } else {
                    document.getElementById('newGroup').disabled = false;
                }
                //alert('h1 ' + document.getElementById('user_group').options[document.getElementById("user_group").selectedIndex].value);
            }
            function addGroup() {
                if(document.getElementById('newGroup').value != '') {
                    document.getElementById('user_group').disabled = true;
                } else {
                    document.getElementById('user_group').disabled = false;
                }
            }
            function checkForName() {
                if($.trim($("#name").val()) == "") {
                    return alert("You must provide a Name.");
                } else {
                    return submitForm();
                }
            }
            function submitForm() {
                parent.polygonColor = $("#color").val();
                document.getElementById("polyid").value = parent.polygonId;
                document.getElementById("color").value = $("#color").val();
                document.getElementById("bounds").value = parent.polygonBounds;
                document.getElementById("geometry").value = parent.polygonGeometry;
                document.getElementById("polyForm").submit();
                parent.closePolySave();
            }
        </script>
    </head>
    <body>
        <form id="polyForm" action="polyFormSubmit.php" method="post">
            <input type="hidden" id="cancel" name="cancel" value="0">
            <input type="hidden" id="polyid" name="polyid" value="">
            <!-- <input type="hidden" id="color" name="color" value=""> -->
            <input type="hidden" id="bounds" name="bounds" value="">
            <input type="hidden" id="geometry" name="geometry" value="">
            <br />
            Name:&nbsp;<input type="text" id="name" name="name" /><br /><br />
            Group:&nbsp;<?php selectGroup(); ?><br /><br />
            New Group:&nbsp;<input type="text" id="newGroup" name="newGroup" onChange="javascript: addGroup();" /><br /><br />
            <div class="input-append color" data-color="rgb(255, 146, 180)" data-color-format="rgb" id="cp3">
                Color:&nbsp;<input type="text" class="span2" id="color" name="color" value="">
                <span class="add-on"><i style="background-color: rgb(255, 146, 180)"></i></span>
            </div>
            <br />
            Public:&nbsp;<input type="checkbox" id="public" name="public" "checked="checked"/><br /><br />
        </form>
        <button id="submitMe" name="submitMe" onclick="checkForName()">Submit</button>&nbsp;&nbsp;&nbsp;&nbsp;
    </body>
</html>
<?php
    function selectGroup() {
        $groups = array();
        $sql = 'select `polygon_groups`.id, `polygon_groups`.name from polygon_groups left join `user_polygons` on `polygon_groups`.id = `user_polygons`.user_group;';
        $result = mysql_query($sql);
        while($row = mysql_fetch_array($result)) {
            foreach($row as $k => $v) {
                if(!(is_numeric($k))) {
                    $groups[$row['id']][$k] = $v;
                }
            }
        }
        echo '<select name="user_group" id="user_group" onChange="javascript: changeGroup();" style="width: 250px;">';
        echo '  <option value="Select"';
        echo '>Select Group</option>';
        foreach($groups as $v) {
            echo '  <option value="' . $v['id'] . '">' . $v['name'] . '</option>';
        }
        echo '</select>';
    }
?>