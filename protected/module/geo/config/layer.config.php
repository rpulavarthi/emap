<?php
	//
	// Sprint:V2R2_CDMA
	//
	$layer_config['Sprint']['V2R2_CDMA']['styles'] = array('cluster' => array('cluster_size'), 'cell' => array('cell_type'), 'pie'=> array('pie_carrier_count'));
	//required fields => key , lat, long, geom in 900913
	$layer_config['Sprint']['V2R2_CDMA']['cellSQL'] = "SELECT cascad as key, max(switch_id) as switch_id, max(cell_id) as cell_id, max(latitude) as lat, max(longitude) as lon,  ST_Transform(max(geom),900913) as geom
															FROM prepallmktdatav2r2 
															WHERE activemouflag = '1' 
															AND sc_type = 'TRAFFIC CARRIER' 
															GROUP BY cascad";
															
	$layer_config['Sprint']['V2R2_CDMA']['WMS']['pieRadius'] = 20;															
	$layer_config['Sprint']['V2R2_CDMA']['sectorSQL'] = 'SELECT cascad                               AS KEY, 
															   sector, 
															   St_y(St_transform(MAX(geom), $srid)) AS latitude, 
															   St_x(St_transform(MAX(geom), $srid)) AS longitude, 
															   COUNT(*)                             AS carrier_count, 
															   MAX(hbw)                             AS hbw, 
															   MAX(azimuth)                         AS azimuth ,
															   MAX(region)                           AS region, 
															   MAX(market99)                         AS market99, 
															   MAX(cluster)                          AS cluster, 
															   MAX(radcenter)                        AS radcenter, 
															   MAX(elevation)                        AS elevation, 
															   MAX(oad)                              AS oad, 
															   MAX(vendor)                           AS vendor, 
															   MAX(sitetype)                         AS sitetype, 
															   MAX(bts_equip)                        AS bts_equip, 
															   MAX(address1)                         AS address, 
															   MAX(city)                             AS city, 
															   MAX(latitude)                         AS latitude_wgs84, 
															   MAX(longitude)                        AS longitude_wgs84, 
															   MAX(pn)                               AS pn, 
															   MAX(e_tilt)                           AS e_tilt, 
															   MAX(m_tilt)                           AS m_tilt, 
															   MAX(antenna_model)                    AS antenna_model,
															   MAX(sc_type)							 AS sc_type,
																MAX(sid)								 AS sid,
																MAX(switch_id)							 AS switch_id,
																MAX(cell_id)							 AS cell_id,
															   MAX(switch_name)							 AS switch_name,
															   MAX(state)							 AS state,
															   MAX(zip)							 AS zip
															   
														FROM   prepallmktdatav2r2 
														WHERE  cascad IN ( $sql ) 
															   AND activemouflag =  \'1\' 
															   AND sc_type =  \'TRAFFIC CARRIER\' 
														GROUP  BY key, 
																sector ORDER BY key, sector';
	//
	// Sprint:V2R2_EVDO
	//
	$layer_config['Sprint']['V2R2_EVDO']['styles'] =  array('cluster' => array('cluster_size'), 'cell' => array('cell_type'), 'pie'=> array('pie_carrier_count'));
	$layer_config['Sprint']['V2R2_EVDO']['cellSQL'] = "	SELECT cascad as key, max(switch_id) as switch_id, max(cell_id) as cell_id, max(latitude) as lat, max(longitude) as lon,  ST_Transform(max(geom),900913) as geom
															FROM prepallmktdatav2r2 
															WHERE activemouflag = '1' 
															AND sc_type IN ('TRAFFIC EV-DO CARRIER' , 'Traffic EV-DO Carrier')
															GROUP BY cascad";
	$layer_config['Sprint']['V2R2_EVDO']['WMS']['pieRadius'] = 20;														
	$layer_config['Sprint']['V2R2_EVDO']['sectorSQL'] = 'SELECT cascad as key,
																sector, 
															   St_y(St_transform(MAX(geom), $srid)) AS latitude, 
															   St_x(St_transform(MAX(geom), $srid)) AS longitude, 
															   COUNT(*)                             AS carrier_count, 
															   MAX(hbw)                             AS hbw, 
															   MAX(azimuth)                         AS azimuth ,
															   MAX(region)                           AS region, 
															   MAX(market99)                         AS market99, 
															   MAX(cluster)                          AS cluster, 
															   MAX(radcenter)                        AS radcenter, 
															   MAX(elevation)                        AS elevation, 
															   MAX(oad)                              AS oad, 
															   MAX(vendor)                           AS vendor, 
															   MAX(sitetype)                         AS sitetype, 
															   MAX(bts_equip)                        AS bts_equip, 
															   MAX(address1)                         AS address, 
															   MAX(city)                             AS city, 
															   MAX(latitude)                         AS latitude_wgs84, 
															   MAX(longitude)                        AS longitude_wgs84, 
															   MAX(pn)                               AS pn, 
															   MAX(e_tilt)                           AS e_tilt, 
															   MAX(m_tilt)                           AS m_tilt,  
															   MAX(antenna_model)                    AS antenna_model,
															 	MAX(sc_type)							 AS sc_type,
																MAX(sid)								 AS sid,
																MAX(switch_id)							 AS switch_id,
																MAX(cell_id)							 AS cell_id,
															   MAX(switch_name)							 AS switch_name,
															   	MAX(state)							 AS state,
															   MAX(zip)							 AS zip
														FROM prepallmktdatav2r2 WHERE cascad IN ($sql)
														AND activemouflag = \'1\' 
														AND sc_type IN (\'TRAFFIC EV-DO CARRIER\' , \'Traffic EV-DO Carrier\')
														GROUP BY cascad, sector';
	//
	// Sprint:NV_CDMA
	//									
	$layer_config['Sprint']['NV_CDMA']['styles'] = array('cluster' => array('cluster_size'), 'cell' => array('cell_type'), 'pie'=> array('pie_carrier_count'));
	//required fields => key , lat, long, geom in 900913
	$layer_config['Sprint']['NV_CDMA']['cellSQL'] = "SELECT cascad as key, max(switch_id) as switch_id, max(cell_id) as cell_id, max(latitude) as lat, max(longitude) as lon,  ST_Transform(max(geom),900913) as geom
															FROM nvconfig 
															WHERE activemouflag = '1' 
															AND sc_type = 'CDMA' 
															GROUP BY cascad";
															
	$layer_config['Sprint']['NV_CDMA']['WMS']['pieRadius'] = 20;															
	$layer_config['Sprint']['NV_CDMA']['sectorSQL'] = 'SELECT cascad                               AS KEY, 
															   sector, 
															   St_y(St_transform(MAX(geom), $srid)) AS latitude, 
															   St_x(St_transform(MAX(geom), $srid)) AS longitude, 
															   COUNT(*)                             AS carrier_count, 
															   MAX(hbw)                             AS hbw, 
															   MAX(azimuth)                         AS azimuth ,
															   MAX(region)                           AS region, 
															   MAX(market99)                         AS market99, 
															   MAX(cluster)                          AS cluster, 
															   MAX(radcenter)                        AS radcenter, 
															   MAX(elevation)                        AS elevation, 
															   MAX(oad)                              AS oad, 
															   MAX(vendor)                           AS vendor, 
															   MAX(sitetype)                         AS sitetype, 
															   MAX(bts_equip)                        AS bts_equip, 
															   MAX(address1)                         AS address, 
															   MAX(city)                             AS city, 
															   MAX(latitude)                         AS latitude_wgs84, 
															   MAX(longitude)                        AS longitude_wgs84, 
															   MAX(pn)                               AS pn, 
															   MAX(e_tilt)                           AS e_tilt, 
															   MAX(m_tilt)                           AS m_tilt, 
															   MAX(antenna_model)                    AS antenna_model,
															  	MAX(sc_type)							 AS sc_type,
																MAX(sid)								 AS sid,
																MAX(switch_id)							 AS switch_id,
																MAX(cell_id)							 AS cell_id,
															   MAX(switch_name)							 AS switch_name,
															   	MAX(state)							 AS state,
															   MAX(zip)							 AS zip
														FROM   nvconfig 
														WHERE  cascad IN ( $sql ) 
															   AND activemouflag =  \'1\' 
															   AND sc_type =  \'CDMA\' 
														GROUP  BY key, 
																sector ORDER BY key, sector';													
	//
	// Sprint:NV_EVDO
	//									
	$layer_config['Sprint']['NV_EVDO']['styles'] = array('cluster' => array('cluster_size'), 'cell' => array('cell_type'), 'pie'=> array('pie_carrier_count'));
	//required fields => key , lat, long, geom in 900913
	$layer_config['Sprint']['NV_EVDO']['cellSQL'] = "SELECT cascad as key, max(switch_id) as switch_id, max(cell_id) as cell_id, max(latitude) as lat, max(longitude) as lon,  ST_Transform(max(geom),900913) as geom
															FROM nvconfig 
															WHERE activemouflag = '1' 
															AND sc_type = 'EVDO' 
															GROUP BY cascad";
															
	$layer_config['Sprint']['NV_EVDO']['WMS']['pieRadius'] = 20;															
	$layer_config['Sprint']['NV_EVDO']['sectorSQL'] = 'SELECT cascad                               AS KEY, 
															   sector, 
															   St_y(St_transform(MAX(geom), $srid)) AS latitude, 
															   St_x(St_transform(MAX(geom), $srid)) AS longitude, 
															   COUNT(*)                             AS carrier_count, 
															   MAX(hbw)                             AS hbw, 
															   MAX(azimuth)                         AS azimuth ,
															   MAX(region)                           AS region, 
															   MAX(market99)                         AS market99, 
															   MAX(cluster)                          AS cluster, 
															   MAX(radcenter)                        AS radcenter, 
															   MAX(elevation)                        AS elevation, 
															   MAX(oad)                              AS oad, 
															   MAX(vendor)                           AS vendor, 
															   MAX(sitetype)                         AS sitetype, 
															   MAX(bts_equip)                        AS bts_equip, 
															   MAX(address1)                         AS address, 
															   MAX(city)                             AS city, 
															   MAX(latitude)                         AS latitude_wgs84, 
															   MAX(longitude)                        AS longitude_wgs84, 
															   MAX(pn)                               AS pn, 
															   MAX(e_tilt)                           AS e_tilt, 
															   MAX(m_tilt)                           AS m_tilt, 
															   MAX(antenna_model)                    AS antenna_model,
																MAX(sc_type)							 AS sc_type,
																MAX(sid)								 AS sid,
																MAX(switch_id)							 AS switch_id,
																MAX(cell_id)							 AS cell_id,
															   MAX(switch_name)							 AS switch_name	,
															   MAX(state)							 AS state,
															   MAX(zip)							 AS zip															   
														FROM   nvconfig 
														WHERE  cascad IN ( $sql ) 
															   AND activemouflag =  \'1\' 
															   AND sc_type =  \'EVDO\' 
														GROUP  BY key, 
																sector ORDER BY key, sector';													
														
					
?>