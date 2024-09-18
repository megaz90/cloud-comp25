<?php
require __DIR__ . '/../vendor/autoload.php';

use Aws\Exception\AwsException;

// Function to insert new game entry into DynamoDB
function createNewGame($dynamoDb, $tableName, $gameName, $maxValue)
{
    try {
        $targetValue = rand(1, $maxValue);
        $gameId = uniqid();
        $result = $dynamoDb->putItem([
            'TableName' => $tableName,
            'Item' => [
                'game_id' => ['S' => $gameId],    // Unique game ID
                'player_id' => ['S' => 'game'],   // Placeholder to store game metadata
                'name' => ['S' => $gameName],     // Game name
                'max_value' => ['N' => (string)$maxValue], // Maximum goal value
                'target_value' => ['N' => (string)$targetValue], // The randomly generated target number
            ],
        ]);
        return $gameId;
    } catch (AwsException $ex) {
        throw new Exception($ex->getMessage());
    }
}

// Function to list games from DynamoDB
function listGames($dynamoDb, $tableName)
{
    try {
        $result = $dynamoDb->scan([
            'TableName' => $tableName,
        ]);

        if (count($result['Items']) > 0) {
            // Loop through the results to find the game and player data
            $data = [];
            foreach ($result['Items'] as $item) {
                if ($item['player_id']['S'] === 'game') {
                    $data['game'][] = $item; // This is the game metadata
                } else {
                    $data['player'][] = $item; // This is the player data
                }
            }
            return $data;
        } else {
            return [];
        }
    } catch (AwsException $ex) {
        throw new Exception($ex->getMessage());
    }
}

function joinGame($dynamoDb, $tableName, $gameId, $playerName)
{
    try {
        $playerId = uniqid();

        // Initialize the player with 0 attempts and no guesses
        $result = $dynamoDb->putItem([
            'TableName' => $tableName,
            'Item' => [
                'game_id' => ['S' => $gameId],
                'player_id' => ['S' => $playerId],
                'player_name' => ['S' => $playerName],
                'attempts' => ['N' => '0'],
                'last_guess' => ['N' => '0'], // Initially no guess
            ],
        ]);

        return $playerId; // Return the player's unique ID
    } catch (AwsException $ex) {
        throw new Exception($ex->getMessage());
    }
}

function makeGuess($dynamoDb, $tableName, $gameId, $playerId, $guess)
{
    try {
        if (checkIfGameIsWon($dynamoDb, $tableName, $gameId)) {
            return "Nice Try! Someone else already won!";
        }
        $gameResult = $dynamoDb->getItem([
            'TableName' => $tableName,
            'Key' => [
                'game_id' => ['S' => $gameId],
                'player_id' => ['S' => 'game'],
            ],
        ]);

        $playerResult = $dynamoDb->getItem([
            'TableName' => $tableName,
            'Key' => [
                'game_id' => ['S' => $gameId],
                'player_id' => ['S' => $playerId],
            ],
        ]);

        if (!isset($gameResult['Item']) || !isset($playerResult['Item'])) {
            throw new Exception('Game or player does not exist.');
        }

        // Extract the game and player information
        $targetValue = (int)$gameResult['Item']['target_value']['N'];
        $attempts = (int)$playerResult['Item']['attempts']['N'];

        // Increment the attempts
        $attempts++;

        // Compare the guess to the target value
        if ($guess == $targetValue) {
            $message = "Congratulations! You've guessed the correct number {$targetValue} in {$attempts} attempts!";
        } elseif ($guess < $targetValue) {
            $message = "Too low! Try again.";
        } else {
            $message = "Too high! Try again.";
        }

        // Update the player's attempts and last guess in DynamoDB
        $dynamoDb->updateItem([
            'TableName' => $tableName,
            'Key' => [
                'game_id' => ['S' => $gameId],
                'player_id' => ['S' => $playerId],
            ],
            'UpdateExpression' => 'SET attempts = :attempts, last_guess = :guess',
            'ExpressionAttributeValues' => [
                ':attempts' => ['N' => (string)$attempts],
                ':guess' => ['N' => (string)$guess],
            ],
        ]);

        return $message; // Return the message to the player

    } catch (AwsException $ex) {
        throw new Exception($ex->getMessage());
    }
}

function getPlayerStatus($dynamoDb, $tableName, $gameId, $playerId)
{
    try {
        $result = $dynamoDb->getItem([
            'TableName' => $tableName,
            'Key' => [
                'game_id' => ['S' => $gameId],
                'player_id' => ['S' => $playerId],
            ],
        ]);

        return $result['Item'] ?? null;
    } catch (AwsException $ex) {
        throw new Exception($ex->getMessage());
    }
}

// Function to check if the game is won by any player
function checkIfGameIsWon($dynamoDb, $tableName, $gameId)
{
    try {
        $gameMetaResult = $dynamoDb->getItem([
            'TableName' => $tableName,
            'Key' => [
                'game_id' => ['S' => $gameId],
                'player_id' => ['S' => 'game'], // Metadata for the game
            ],
        ]);

        if (!isset($gameMetaResult['Item'])) {
            throw new Exception("Game metadata not found for game_id: $gameId");
        }

        $targetValue = $gameMetaResult['Item']['target_value']['N'];

        $result = $dynamoDb->query([
            'TableName' => $tableName,
            'KeyConditionExpression' => 'game_id = :gameId',
            'ExpressionAttributeValues' => [
                ':gameId' => ['S' => $gameId],
            ],
        ]);

        foreach ($result['Items'] as $playerRecord) {
            if ($playerRecord['player_id']['S'] === 'game') {
                continue;
            }
            // Check if the player's last guess matches the target value
            if (isset($playerRecord['last_guess']['N']) && $playerRecord['last_guess']['N'] == $targetValue) {
                return true;
            }
        }
        return false;
    } catch (AwsException $ex) {
        throw new Exception($ex->getMessage());
    }
}

// Function to list all players in a game
function listPlayersInGame($dynamoDb, $tableName, $gameId)
{
    try {
        $result = $dynamoDb->scan([
            'TableName' => $tableName,
            'FilterExpression' => 'game_id = :gameId AND player_id <> :game',
            'ExpressionAttributeValues' => [
                ':gameId' => ['S' => $gameId],
                ':game' => ['S' => 'game'] // Exclude the game metadata
            ],
        ]);

        return $result['Items'];
    } catch (AwsException $ex) {
        throw new Exception($ex->getMessage());
    }
}


// Function to retrieve a specific game by its game_id from DynamoDB
function getGameById($dynamoDb, $tableName, $gameId)
{
    try {
        // Fetch the game metadata (which has player_id = 'game')
        $result = $dynamoDb->query([
            'TableName' => $tableName,
            'KeyConditionExpression' => 'game_id = :game_id',
            'ExpressionAttributeValues' => [
                ':game_id' => ['S' => $gameId]
            ],
        ]);

        if (isset($result['Items']) && count($result['Items']) > 0) {
            return $result['Items']; // Return list of games
        } else {
            return []; // No games found
        }
    } catch (AwsException $ex) {
        throw new Exception($ex->getMessage());
    }
}

// Function to add a player to a game
function addPlayerToGame($dynamoDb, $tableName, $gameId, $playerId, $playerName)
{
    try {
        // Check if the game exists
        $gameResult = $dynamoDb->getItem([
            'TableName' => $tableName,
            'Key' => [
                'game_id' => ['S' => $gameId],
                'player_id' => ['S' => 'game'], // Game metadata has a player_id of 'game'
            ],
        ]);

        if (!isset($gameResult['Item'])) {
            throw new Exception('Game not found.');
        }

        // Add the player to the game
        $result = $dynamoDb->putItem([
            'TableName' => $tableName,
            'Item' => [
                'game_id' => ['S' => $gameId],
                'player_id' => ['S' => $playerId],
                'player_name' => ['S' => $playerName],
                'attempts' => ['N' => '0'],
                'last_guess' => ['N' => '0'], // Initially no guess
            ],
        ]);

        return $playerId; // Return the player's unique ID

    } catch (AwsException $ex) {
        throw new Exception($ex->getMessage());
    }
}
