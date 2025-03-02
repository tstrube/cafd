<?php
require_once 'include/include.php';

// Update gps
echo '<br /><strong>Updating gps table</strong><br />';
$query = pdoInitSelect('gps', array(), array('id, lon, lat'), 'country = ""'); 
$mysql_result = pdoGetMultiple($query);

foreach ($mysql_result as $mysql_value) {
	// build url
	$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $mysql_value['lat'] . ',' . $mysql_value['lon'] . '&key=' . $ggap;
	
	// curl gets json
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_ENCODING, "UTF-8");
	$result = curl_exec($curl);
	curl_close($curl);
	
	// Decode json to array
	$json = json_decode($result);
	
	$country = "";
	$province = "";
	$district = "";
	
	foreach ($json->results as $result) {
	    if (in_array('administrative_area_level_2', $result->types) && !$province && !$country) {
	    	foreach ($result->address_components as $value) {
	    		if (in_array('administrative_area_level_2', $value->types)) {
					$district = $value->long_name;
					$district = str_replace(' district', '', $district);
					$district = str_replace(' District', '', $district);
	    		} elseif (in_array('administrative_area_level_1', $value->types)) {
					$province = $value->long_name;
					$province = str_replace(' province', '', $province);
					$province = str_replace(' Province', '', $province);
				} elseif (in_array('country', $value->types))
	       			$country = $value->long_name;
			}
	        echo 'ID: ' . $mysql_value['id'] . ', country: ' . $country . ', province: ' . $province . ', district: ' . $district . 
	        ', lon/lat: ' . $mysql_value['lat'] . ',' . $mysql_value['lon'] . '</span><br />';
			continue;
		}
	}
	
	if (!$country)
		foreach ($json->results as $result) {
			if (!$country) {
		    	foreach ($result->address_components as $value) {
		    		if (in_array('administrative_area_level_2', $value->types)) {
						$district = $value->long_name;
						$district = str_replace(' district', '', $district);
						$district = str_replace(' District', '', $district);
		    		} elseif (in_array('administrative_area_level_1', $value->types)) {
						$province = $value->long_name;
						$province = str_replace(' province', '', $province);
						$province = str_replace(' Province', '', $province);
					} elseif (in_array('country', $value->types))
		       			$country = $value->long_name;
				}
		        echo '<span style="color: orange;">ID: ' . $mysql_value['id'] . ', country: ' . $country . ', province: ' . $province . ', district: ' . $district . 
		        ', lon/lat: ' . $mysql_value['lat'] . ',' . $mysql_value['lon'] . '</span><br />';
		        continue;
			}
		}

	if ($country) {
		$values = array('country' => $country, 'province' => $province, 'district' => $district, 'id' => $mysql_value['id']);
		$columns = array('country', 'province', 'district');
		$where = 'id=:id';
		pdoUpdate('gps', $values, $where, $columns);
	}
		
}

// Update gps_provinces
echo '<br /><strong>Updating gps_province table</strong><br />';
$query = pdoInitSelect('gps', array(), array('country, province'), 'province NOT IN (SELECT province FROM gps_provinces)'); 
$mysql_result = pdoGetMultiple($query);

foreach ($mysql_result as $mysql_value) {
	if ($mysql_value['province'] != "" && $mysql_value['country'] != "") {	
		// build url
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?components=administrative_area:' . urlencode($mysql_value['province']) . '|country:' . urlencode($mysql_value['country']) . '&key=' . $ggap;
		
		// curl gets json
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_ENCODING, "UTF-8");
		$result = curl_exec($curl);
		
		// Decode json to array
		$json = json_decode($result);
		
		if ($json->status == "OK") {
			$lon = $json->results[0]->geometry->location->lng;
			$lat = $json->results[0]->geometry->location->lat;
			echo 'country: ' . $mysql_value['country'] . ', province: ' . $mysql_value['province'] . ', lon/lat: ' . $lon . ',' . $lat . '<br />';
			pdoInsert('gps_provinces', array('country' => $mysql_value['country'], 'province' => $mysql_value['province'], 'lat' => $lat, 'lon' => $lon));
		} else 
			echo '<span style="color: orange">No results for country: ' . $mysql_value['country'] . ', province: ' . $mysql_value['province'] . '</span><br />';
	}
}

echo '<br /><strong>Update gps count</strong><br />';

$sql = 'SELECT count(*) count, province FROM gps GROUP BY province';
$query = pdoInitSelect('gps', array(), array('count(*) count', 'province'), '1 GROUP BY province'); 
$mysql_result = pdoGetMultiple($query);

foreach ($mysql_result as $mysql_value){
	$values = array('count' => $mysql_value['count'], 'province' => $mysql_value['province']);
	$columns = array('count');
	$where = 'province=":province"';
	pdoUpdate('gps_provinces', $values, $where, $columns);
}

echo '<br />Done.';

?>
