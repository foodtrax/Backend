<?php
include '../lib/Database.php';
include '../lib/Secrets.php';

session_start();

$id = $_POST['truckid'];
$name = $_POST['truckname'];
$description = $_POST['truckdesc'];

// Verify we have expected parameters
if (!$id || !$name || !$description || !$_SESSION['id']) {
    die("Invalid parameters");
}

// Connect to database
$databaseCredentials = (new Secrets())->readSecrets();
$database = new Database(
    $databaseCredentials['db_user'],
    $databaseCredentials['db_pass'],
    $databaseCredentials['db_host'],
    $databaseCredentials['db_database']
);

$database->connect();

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

$updateTruckInformation = $database->update(
    'UPDATE `truck_information` SET `name`=:name,`description`=:desc WHERE `truck_id`= :truckid',
    [
        ':name' => $name,
        ':desc' => $description,
        ':truckid' => $id
    ]
);

die(json_encode(['result' => $updateTruckInformation]));