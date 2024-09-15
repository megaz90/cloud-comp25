<?php

use Aws\Exception\AwsException;

// Function to insert new game entry into DynamoDB
function createNewGame($dynamoDb, $tableName, $gameName, $maxValue)
{
    try {
        $result = $dynamoDb->putItem([
            'TableName' => $tableName,
            'Item' => [
                'cloud_id' => ['S' => uniqid()], // Unique game ID
                'name' => ['S' => $gameName],   // Game name
                'value' => ['N' => '0'],        // Initial score
                'max_value' => ['N' => (string)$maxValue], // Maximum goal value
            ],
        ]);
        echo "Game '$gameName' created successfully.<br/>";
    } catch (AwsException $e) {
        echo "Error creating game: " . $e->getMessage() . "<br/>";
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
            return $result['Items'];
            // foreach ($result['Items'] as $game) {
            //     echo "<a href='cloud.php?cloud_id=" . $game['cloud_id']['S'] . "'>"
            //         . $game['name']['S'] . "</a> (score: " . $game['value']['N']
            //         . ", goal: " . $game['max_value']['N'] . ")<br />";
            // }
        } else {
            echo "No games available.<br/>";
        }
    } catch (AwsException $e) {
        echo "Error listing games: " . $e->getMessage() . "<br/>";
    }
}
