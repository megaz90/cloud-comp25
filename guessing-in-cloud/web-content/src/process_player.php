<?php
include './config.php';
include './functions.php';

$client = createSDK();

// Handle player information
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['player_name'], $_GET['game_id'])) {
    $playerName = $_POST['player_name'];
    // Check if player is already part of the game
    $playerStatus = $playerId ? getPlayerStatus($client, TABLE_NAME, $gameId, $playerId) : null;

    if ($playerName) {
        // Generate a unique player ID
        $playerId = uniqid();
        // Save player information in the session
        $_SESSION['player_name'] = $playerName;
        $_SESSION['player_id'] = $playerId;

        // Add the player to the game
        addPlayerToGame($client, TABLE_NAME, $gameId, $playerId, $playerName);
    }

    // Handle game guesses
    $guess = (int) ($_POST['guess'] ?? 0);
    $message = '';
    if ($guess) {
        $message = makeGuess($client, TABLE_NAME, $gameId, $playerId, $guess);
    }
}
