<?php
require_once '../include/include.php';

$removeNonHighlight = false;

$where = '';
$values = array();

if (isset($_SESSION['map_search'])) {
	if (!empty($_SESSION['map_search'])) {
		$ids = explode(';', $_SESSION['map_search']);
		unset($_SESSION['map_search']);
		
		foreach ($ids as $value) {
			if (!empty($where))
				$where .= ' OR ';
			$where .= 'fault_id=?';
			$values[] = $value;
		}
		
	} else {
		unset($_SESSION['map_search']);
		echo json_encode(array());
		exit;
	}
}

$query = pdoInitSelect('lines', $values, array('*'), $where);
$lines = pdoGetMultiple($query);

foreach ($lines as $line) {
	// get the points of current line
	$query = pdoInitSelect('lines_points', array($line['fid']), array('lat', 'lon'), "fid=?");
	$points = pdoGetMultiple($query);
	
	// get the fault name belonging to current line
	$query = pdoInitSelect('faults', array($line['fault_id']), array('name'), "id=?");
	$fault = pdoGetSingle($query);
	if (!$fault) $fault = array('name' => 'Unknown');
	
	// check if line has to be highlighted
	if (isset($_GET['id'])) {
		if (is_array($_GET['id']) && in_array($line['fault_id'], $_GET['id'])) {
			$highlight = 2;
			$removeNonHighlight = true;
		} elseif ($_GET['id'] == $line['fault_id']) {
			$highlight = 1;
		} else {
			$highlight = 0;
		}
	} else {
		$highlight = 0;
	}
	
	// set properties of line
	$properties = array('fault_id' => $line['fault_id'], 
							'name' => $fault['name'], 
							'type' => $line['type'], 
					   'highlight' => $highlight);
	
	// save points in array
	$points_simple = NULL;
	foreach ($points as $point) 
		$points_simple[] = array((double)$point['lon'], (double)$point['lat']);
	
	// if a fault is highlighted, calculate the center of the current line
	if ($highlight)
		foreach ($points_simple as $value)
			$center[] = array($value[0], $value[1]);
	
	// compile all the information in one big variable
	$geometry = array('type' => 'MultiLineString', 'coordinates' => array($points_simple));
	$feature = array('type' => 'Feature', 'id' => $line['id'], 'properties' => $properties, 'geometry' => $geometry);
	$result[] = $feature;
}

// calculate the center of all highlighted lines, aka center of the fault
if (isset($center)) {
	$x_min = 1000;
	$x_max = -1000;
	$y_min = 1000;
	$y_max = -1000;
	
	foreach ($center as $value) {
		// calculate bound
		$x_min = min($x_min, $value[0]);
		$x_max = max($x_max, $value[0]);
		$y_min = min($y_min, $value[1]);
		$y_max = max($y_max, $value[1]);
	}
	//echo $x_min . $x_max . $y_min . $y_max;
	$x_delta = $x_max - $x_min;
	$y_delta = $y_max - $y_min;
	
	$center = array($x_min + $x_delta / 2, $y_min + $y_delta / 2);
	
	$factor = sqrt(max($x_max - $x_min, $y_max - $y_min)) / 3;
	$factor = min(1, max(0.1, $factor));
	
	$result[0]['properties']['center'] = $center;
	$result[0]['properties']['zoom'] = $factor;
}

// only show highlighted faults (search results)
if ($removeNonHighlight) {
	$result2 = array();
	
	foreach ($result as $value)
		if ($value['properties']['highlight'] == 2)
			$result2[] = $value;
	
	$result = $result2;
}

$json = array('type' => 'FeatureCollection', 'features' => $result);

echo json_encode($json);
?>