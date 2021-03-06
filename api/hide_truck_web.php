<?php
/**
 * @author Christopher Bitler
 */

include '../lib/Database.php';
include '../lib/Secrets.php';

session_start();

header("Access-Control-Allow-Origin: *.foodtrax.io");

if (!$_SESSION['id']) {
    die("Invalid parameters");
}

// Get the truckId and event data (lat,lon)
$id = $_POST['truckId'];

// Connect to the database
$databaseCredentials = (new Secrets())->readSecrets();
$database = new Database(
    $databaseCredentials['db_user'],
    $databaseCredentials['db_pass'],
    $databaseCredentials['db_host'],
    $databaseCredentials['db_database']
);

$database->connect();

// Verify owner
$checkTruckOwner = $database->query(
    'SELECT owner_id FROM `truck_information` WHERE `truck_id`= :truckid',
    [
        ':truckid' => $id
    ]
);

if (count($checkTruckOwner) === 0) {
    die(json_encode(['result' => false]));
}

$truckOwner = $checkTruckOwner[0]['owner_id'];

if ($truckOwner !== $_SESSION['id']) {
    die(json_encode(['result' => false]));
}

$truckId = $results[0]['truck_id'];

// Update offline state to 1
$insertResult = $database->update('UPDATE `truck_information` SET `offline`=1 WHERE `truck_id`=:truckId',
    [
        ':truckId' => (int)$id,
    ]
);

echo json_encode(['result' => $insertResult]);