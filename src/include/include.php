<?php
session_start();

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

require_once 'subs.php';
require_once 'subs_ofa.php';

?>