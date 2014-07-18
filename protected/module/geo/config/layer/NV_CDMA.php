<?php
class NV_CDMA {


	function wms(){}
	
	
	function kml(){}
	
	
	static function buildCellSectorDescription($attributes){
	
		$html = '';
		if(count($attributes) > 0){
			$html = "
			<div id=\"tabs\">
				<ul>
					<li><a href=\"#tabs-1\">Cell</a></li>
					<li><a href=\"#tabs-2\">Sector</a></li>
					<li><a href=\"#tabs-3\">Config</a></li>
				</ul>
				<div id=\"tabs-1\">
					<ul>
					 <li>Site ID: {$attributes[0]["sid"]}_{$attributes[0]["switch_id"]}_{$attributes[0]["cell_id"]}</li>
					  <li>Cluster: {$attributes[0]["cluster"]}</li>
					  <li>Market: {$attributes[0]["market99"]}</li>
					  <li>Region: {$attributes[0]["region"]}</li>
					  <li>Rad Center: {$attributes[0]["radcenter"]}</li>
					  <li>Elevation: {$attributes[0]["elevation"]}</li>
					  <li>On Air Date: {$attributes[0]["oad"]}</li>
					</ul>
				</div>
				<div id=\"tabs-2\">
				<table class=\"modalTable\">
				  <tr>
					<td>Sector</td>
					<td>Carriers</td>
					<td>Azimiuth</td>
					<td>M Tilt</td>
					<td>E Tilt</td>
					<td>HB</td>
					<td>PN</td>
					<td>Antenna</td>
				  </tr>
				  <tr>";
				
				foreach($attributes as $attr){
					$html .= "<tr>";
					$html .= "<td>{$attr["sector"]}</td>";
					$html .= "<td>{$attr["carrier_count"]}</td>";
					$html .= "<td>{$attr["azimuth"]}</td>";
					$html .= "<td>{$attr["m_tilt"]}</td>";
					$html .= "<td>{$attr["e_tilt"]}</td>";
					$html .= "<td>{$attr["hbw"]}</td>";
				
					$html .= "<td>{$attr["pn"]}</td>";
					$html .= "<td>{$attr["antenna_model"]}</td>";
					$html .= "</tr>";
				}
				
				$html .= "</table>
				</div>
				<div id=\"tabs-3\">
					<ul>
					  <li>Vendor: {$attributes[0]["vendor"]}</li>
					  <li>Equip: {$attributes[0]["bts_equip"]}</li>
					  <li>Switch Name: {$attributes[0]["switch_name"]}</li>
					  <li>Address: {$attributes[0]["address"]}</li>
					  <li>City: {$attributes[0]["city"]}</li>
					  <li>State: {$attributes[0]["state"]}</li>
					  <li>Zip: {$attributes[0]["zip"]}</li>
					</ul>
				</div>
			</div>";
		}
		return $html;
	}

}
?>