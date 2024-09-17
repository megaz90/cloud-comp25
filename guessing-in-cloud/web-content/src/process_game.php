<?php
include './config.php';
include './functions.php';

$clientDynamo = createSDK();

// Check if form was submitted to create a new game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_cloud_name'], $_POST['new_cloud_goal'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $gameName = $_POST['new_cloud_name'];
        $maxValue = $_POST['new_cloud_goal'];
        createNewGame($clientDynamo, TABLE_NAME, $gameName, $maxValue);
    }
}
header("Location:./index.php");
