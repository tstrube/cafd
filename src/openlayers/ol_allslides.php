<?php
require_once '../include/include.php';

$query = pdoInitSelect('all_slides', array(), array('*'));
$all_slides = pdoGetMultiple($query);

foreach ($all_slides as $allslides) {
	$geometry = array('type' => 'Point',
			   'coordinates' => array((double)$allslides['lon'],
			   						  (double)$allslides['lat']));

	$feature = array('type' => 'Feature',
					   'id' => $allslides['id'],
			   'properties' => array(	  'name' => $allslides['name'],
										  'time' => $allslides['time'],
								     'reference' => $allslides['reference'],
									  'download' => $allslides['download']),
				 'geometry' => $geometry);

	$result[] = $feature;
}

$json = array('type' => 'FeatureCollection',
		  'features' => $result);

echo json_encode($json);
?>
