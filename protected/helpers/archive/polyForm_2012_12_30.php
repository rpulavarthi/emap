<?php
/*
 * polyForm.php *
 * Mark Patterson - Dec 20, 2012
 * (mark@oldensoft.com)
*/
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
                document.getElementById("color").value = parent.polygonColor;
                document.getElementById("bounds").value = parent.polygonBounds;
                document.getElementById("geometry").value = parent.polygonGeometry;
                document.getElementById("polyForm").submit();
                parent.closePolySave();
            }

            function displayvars() { //gratuitous FYI
                var str = "Name:\n" + parent.polygonId + "\r\n";
                str += "Color:\n" + parent.polygonColor + "\r\n";
                str += "Bounds\n" + parent.polygonBounds + "\r\n";
                str += "Geometry\n" + parent.polygonGeometry + "\r\n";
                //alert(str);
                document.getElementById("polyGeometry").innerHTML = "<p>Name:<br />" + parent.polygonId + "</p>";
                document.getElementById("polyGeometry").innerHTML = "<p>Color:<br />" + parent.polygonColor + "</p>";
                document.getElementById("polyGeometry").innerHTML += "<p>Bounds<br />" + parent.polygonBounds + "</p>";
                document.getElementById("polyGeometry").innerHTML += "<p>Geometry<br />" + parent.polygonGeometry + "</p>";
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

                <div class="input-append color" data-color="rgb(255, 146, 180)" data-color-format="rgb" id="cp3">
                    Color:&nbsp;<input type="text" class="span2" id="color" name="color" value="">
                    <span class="add-on"><i style="background-color: rgb(255, 146, 180)"></i></span>
                </div>
                <br />
            Public:&nbsp;<input type="checkbox" id="public" name="public" "checked="checked"/><br /><br />
        </form>
        <button id="submitMe" name="submitMe" onclick="checkForName()">Submit</button>&nbsp;&nbsp;&nbsp;&nbsp;
             <div id="polyGeometry"></div>
    </body>
</html>
