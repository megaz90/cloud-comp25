<?php
include './config.php';
include './functions.php';

$client = createSDK();

// Handle player information
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['player_name'], $_GET['game_id'])) {
    $playerName = $_POST['player_name'];
    $gameId = $_GET['game_id'];

    if (isset($_POST['player_id'])) {
        $playerId = $_POST['player_id'];
        // Check if player is already part of the game
        $_SESSION['player_record'][] = $newPlayerId . $gameId;
    }

    if ($playerName) {
        // Generate a unique player ID
        $newPlayerId = uniqid();
        // Save player information in the session
        $_SESSION['player_name'] = $playerName;
        $_SESSION['player_id'] = $newPlayerId;

        $_SESSION['player_record'][] = $newPlayerId . $gameId;

        // Add the player to the game
        addPlayerToGame($client, TABLE_NAME, $gameId, $newPlayerId, $playerName);
    }
    header("Location:./game.php?game_id=" . $_GET['game_id']);
}
