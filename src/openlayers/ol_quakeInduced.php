<?php
require_once '../include/include.php';

$query = pdoInitSelect('quake_induced', array(), array('*'));
$earthquakes = pdoGetMultiple($query);

foreach ($earthquakes as $quake) {
	$geometry = array('type' => 'Point',
			   'coordinates' => array((double)$quake['lon'],
			   						  (double)$quake['lat']));

	$feature = array('type' => 'Feature',
					   'id' => $quake['id'],
			   'properties' => array(	  'name' => $quake['name'],
									 'magnitude' => $quake['magnitude'],
										  'time' => $quake['time'],
								   'slide_count' => $quake['slide_count'],
									  'download' => $quake['download']),
				 'geometry' => $geometry);

	$result[] = $feature;
}

$json = array('type' => 'FeatureCollection',
		  'features' => $result);

echo json_encode($json);
?>
