<?php
include './config.php';
include './functions.php';

$clientDynamo = createSDK();

// Check if form was submitted to create a new game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_cloud_name'], $_POST['new_cloud_goal'])) {
    $gameName = $_POST['new_cloud_name'];
    $maxValue = $_POST['new_cloud_goal'];
    createNewGame($clientDynamo, 'cloud_guessing_game', $gameName, $maxValue);
}
header("Location:./index.php");
