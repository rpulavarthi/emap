<?php

class FieldTechController extends DooController {

function login()
{
if(!mysql_connect('localhost', 'envuser', 'ericsson1'))
	{
		echo "Cannot connect to server";
		exit;
	}
	if(!mysql_select_db('emap'))
	{
		echo "Cannot select database";
		exit;
	}
}

function createinput()
{
    $client = $this->params['client'];
    $owner = $this->params['owner'];
	$title = $_GET['title'];
	$notes = $_GET['notes'];
	$geomtype = $_GET['geomtype'];
	$points = $_GET['points'];
	$cascad = $_GET['cascad'];
	Doo::loadModel('DisasterFt');
    switch(strtoupper($geomtype))
	{
		case "POINT":
		$g = "POINT($points)";
		$geomvalue = "PointFromText('$g')";
		break;
		case "LINE":
		$g = "LINESTRING($points)";
		$geomvalue = "LineStringFromText('$g')";
		break;
		case "POLYGON":
		$g = "POLYGON(($points))";
		$geomvalue = "PolygonFromText('$g')";
		break;
	}
	$date = date('Y-m-d H:i:s');
	$this->login();
	$query = "Insert into disaster_ft(owner, active, modifydate,cascad,title, notes,client,shape) Values('$owner',1 ,'$date','$cascad','$title', '$notes', '$client', $geomvalue)";
	
	mysql_query($query) or die("Query Failed - $query");
	echo "Success";
}

function retrieveInput()
{
	$this->login();
	$client = $this->params['client'];
	$query = "SELECT AsText(shape) as shape,id,owner, active, modifydate, cascad, title, notes FROM disaster_ft where client = '$client' order by modifydate desc;";
	$result = mysql_query($query);
	$rows = array();
	if ($result) {
      while($row = mysql_fetch_assoc($result)) {
			$rows[] = $row;
		}
    }
    else {
      echo mysql_error();
	  exit;
    }
	echo json_encode($rows);
}

function retrieveInput_datagrid()
{
 $page = $_GET['page']; // get the requested page
$limit = $_GET['rows']; // get how many rows we want to have into the grid
$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
$sord = $_GET['sord']; // get the direction
if($sidx == "")
{
$sidx = "modifydate";
$sord = "desc";
}
	$this->login();
	$client = $this->params['client'];
	$result = mysql_query("SELECT COUNT(*) AS count FROM disaster_ft WHERE client = '$client'");
$row = mysql_fetch_array($result,MYSQL_ASSOC);
$count = $row['count'];

if( $count >0 ) {
	$total_pages = ceil($count/$limit);
} else {
	$total_pages = 0;
}
if ($page > $total_pages) $page=$total_pages;
$start = $limit*$page - $limit; 
	
	$query = "SELECT AsText(shape) as shape,id,owner, active, modifydate, cascad, title, notes FROM disaster_ft where client = '$client' ORDER BY  $sidx $sord LIMIT $start , $limit";
	$result = mysql_query($query);
	$response = new searchResponse;
	$response->page = $page;
	$response->total = $total_pages;
	$response->records = $count;
	$i=0;
	while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
		$response->rows[$i]['id']=$row['id'];
		$response->rows[$i]['cell']=array($row['id'],$row['title'],$row['owner'],$row['active'],$row['cascad'],$row['modifydate'],$row['notes']); 
		$i++;
	}        
	echo json_encode($response);
}

function modifyinput()
{
	$id = $_GET['id'];
	$title = $_GET['title'];
	$notes = $_GET['notes'];
	$active = $_GET['active'];
	$cascad = $_GET['cascad'];
	$geometry = $_GET['geometry'];
	$this->login();
	$query = "Update disaster_ft set title = '$title', notes = '$notes', active = $active, modifydate = NOW(), cascad = '$cascad', shape = GeometryFromText('$geometry') where id = $id";
	mysql_query($query) or die(mysql_error);
}

function deleteInput()
{
	$this->login();
	$id = $_GET["id"];
	$query = "Delete from disaster_ft where id = $id";
	mysql_query($query)  or die(mysql_error);;
}

}

?>