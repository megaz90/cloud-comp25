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
        try {
            $playerStatus = $playerId ? getPlayerStatus($client, TABLE_NAME, $gameId, $playerId) : null;
            $_SESSION['player_id'] = $playerId;
        } catch (Exception $ex) {
            header("Location:./game.php?game_id=" . $_GET['game_id']);
        }
    }

    if ($playerName) {
        // Generate a unique player ID
        $newPlayerId = uniqid();
        // Save player information in the session
        $_SESSION['player_name'] = $playerName;
        $_SESSION['player_id'] = $newPlayerId;

        // Add the player to the game
        addPlayerToGame($client, TABLE_NAME, $gameId, $newPlayerId, $playerName);
    }
    header("Location:./game.php?game_id=" . $_GET['game_id']);
}
