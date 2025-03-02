<?php

// HTML-Header
function head($page) {
	echo '
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>' . $GLOBALS['cfg_title'] . (!empty($page) ? ' - ' . $page : '') . '</title>
	<link rel="icon" href="favicon/favicon.ico" type="image/x-icon">
	<link rel="manifest" href="favicon/manifest.json">
	<meta name="theme-color" content="#ffffff">
	<link rel="stylesheet" href="css/template.css" />
	<link rel="stylesheet" href="css/media.css" />
	<link rel="stylesheet" href="css/jquery.multiselect.css" />
	<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" type="text/javascript"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js" type="text/javascript"></script>
	<script src="include/jquery.multiselect.js" type="text/javascript"></script>
	<script src="include/spin.min.js" type="text/javascript"></script>
	<script src="https://www.google.com/recaptcha/api.js"></script>
';

require_once 'analytics.php';

echo '
</head>
<body>
';
	include "head.php";
}

// HTML-Footer
function foot() {
	$query = pdoInitSelect('faults', array(), array('MAX(last_update)'));
	$result = pdoGetSingle($query);

	echo '<center><div style="margin: 10px; font-size: 10px;">Last updated on: ' .
		date('d.m.Y', strtotime($result['MAX(last_update)'])) .
		' &middot; <a href="trackinginfo.php" class="link_noblue">Tracking Information</a></div></center></body></html>';

}

function arrayToCommaString($input) {
	$out = "";
	if (is_array($input)) {
		$first = true;
		foreach ($input as $i) {
			if ($first)
				$first = false;
			else
				$out .= ", ";

			$out .= $i;
		}
	} else
		$out = $input;
	return $out;
}

function issetReturn($var) {
	return isset($var) ? $var : '';
}

function issetReturnGET($var) {
	return isset($_GET[$var]) ? $_GET[$var] : '';
}

function issetReturnArrayGET($var) {
	return isset($_GET[$var]) ? $_GET[$var] : [''];
}

function issetReturnPOST($var) {
	return isset($_POST[$var]) ? $_POST[$var] : '';
}

/////////////////
///// P D O /////
/////////////////

function pdoInitSelect($table, $values, $columns, $where = NULL, $order = NULL, $limit = NULL) {
    $sql = "SELECT " . implode(",", $columns) . " FROM `" . $table . "`";
    if (!empty($where)) $sql .= " WHERE " . $where;
    if (!empty($order)) $sql .= " ORDER BY " . $order;
    if (!empty($limit)) $sql .= " LIMIT " . $limit;
    $query = $GLOBALS['pdo']->prepare($sql);
	$query->execute($values);
	return $query;
}

function pdoGetNumber($query) {
    return $query->rowCount();
}

function pdoGetSingle($query) {
    return $query->fetch(PDO::FETCH_ASSOC);
}

function pdoGetMultiple($query) {
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function pdoInsert($table, $insert_array) {
    $keys = implode(", ", array_keys($insert_array));
    $values = ":" . implode(", :", array_keys($insert_array));
    $sql = "INSERT INTO $table ($keys) VALUES ($values)";
    $query = $GLOBALS['pdo']->prepare($sql);    
    if ($query->execute($insert_array)) {
        return $GLOBALS['pdo']->lastInsertId();
    } else {
        echo "Error: " . implode(", ", $query->errorInfo());
        return false;
    }
}

function pdoUpdate($table, $values, $columns, $where) {
    $sql = "UPDATE `" . $table . "` SET " . implode(", ", $columns) . " WHERE " . $where;
    $query = $GLOBALS['pdo']->prepare($sql);
    if ($query->execute($values)) {
        return true;
    } else {
        echo "Error: " . implode(", ", $query->errorInfo());
        return false;
    }
}

function pdoDelete($table, $values, $where) {
    $sql = "DELETE FROM `" . $table . "` WHERE " . $where;
    $query = $GLOBALS['pdo']->prepare($sql);
    if ($query->execute($values)) {
        return true;
    } else {
        echo "Error: " . implode(", ", $query->errorInfo());
        return false;
    }
}

?>
