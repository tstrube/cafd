<?php
require_once '../include/include.php';

$query = pdoInitSelect('quat_slip', array(), array('*'));
$quat_slips = pdoGetMultiple($query);

foreach ($quat_slips as $slip) {
	$geometry = array('type' => 'Point',
			   'coordinates' => array((double)$slip['lon'],
			   						  (double)$slip['lat']));

	$feature = array('type' => 'Feature',
					   'id' => $slip['id'],
			   'properties' => array( 'fault_id' => $slip['fault_id'],
				 					'fault_name' => $slip['fault_name'],
								   'q_slip_rate' => $slip['q_slip_rate'],
										'method' => $slip['method'],
									 'reference' => $slip['reference']),
				 'geometry' => $geometry);

	$result[] = $feature;
}

$json = array('type' => 'FeatureCollection',
		  'features' => $result);

echo json_encode($json);
?>
