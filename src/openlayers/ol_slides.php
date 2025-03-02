<?php
require_once '../include/include.php';

if (issetReturnGET('a') == 1)
	$where = 'display_big = 0';
elseif (issetReturnGET('a') == 2)
	$where = 'display_big = 1';
else
	$where = '';

if ($where != '')
	$where .= ' AND ';

$where .= 'data_source="' . issetReturnGET('c') . '" GROUP BY id, lon, lat, display_big';

$query = pdoInitSelect('landslides', array(), array('id', 'lon', 'lat', 'display_big', 'count(*) AS count'), $where);
$landslides = pdoGetMultiple($query);

foreach ($landslides as $slide) {
	$geometry = array('type' => 'Point',
			   'coordinates' => array((double)$slide['lon'],
			   						  (double)$slide['lat']));

	$feature = array('type' => 'Feature',
					   'id' => $slide['id'],
			   'properties' => array('display_big' => $slide['display_big'],
									 'count' 	   => $slide['count']),
				 'geometry' => $geometry);
	
	$result[] = $feature;
}


$json = array('type' => 'FeatureCollection',
		  'features' => $result);

echo json_encode($json);
?>
