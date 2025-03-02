<?php
require_once '../include/include.php';

if ($_GET['m'] == 1)
	$where = 'magnitude<=5';
elseif ($_GET['m'] == 2)
	$where = 'magnitude>5';
else
	$where = '';

if ($_GET['d'] == 1)
	$where2 = 'depth<=70';
elseif ($_GET['d'] == 2)
	$where2 = 'depth>70';
else
	$where2 = '';

if ($where != '' && $where2 != '')
	$where .= ' AND ';

$where .= $where2;

if ($where != '')
	$where .= ' AND ';

if ($_GET['c'] == 'aftershock' || $_GET['c'] == 'tipage')
	$where .= '(data_source="aftershock" OR data_source="tipage")';
else
	$where .= 'data_source="' . $_GET['c'] . '"';

$query = pdoInitSelect('earthquakes', array(), array('*'), $where);
$earthquakes = pdoGetMultiple($query);

$result[] = array();
foreach ($earthquakes as $quake) {
	$geometry = array('type' => 'Point',
			   'coordinates' => array((double)$quake['lon'],
			   						  (double)$quake['lat']));

	$feature = array('type' => 'Feature',
					   'id' => $quake['id'],
			   'properties' => array('data_source' => $quake['data_source'],
			   						   'magnitude' => (double)$quake['magnitude'],
			   						   'depth' => (double)$quake['depth']),
				 'geometry' => $geometry);

	$result[] = $feature;
}

$json = array('type' => 'FeatureCollection',
		  'features' => $result);

echo json_encode($json);
?>
