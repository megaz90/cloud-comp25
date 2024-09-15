<?php
require __DIR__ . '/../vendor/autoload.php';

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
        return $result;
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
            return $result['Items'];
        } else {
            return [];
        }
    } catch (AwsException $ex) {
        throw new Exception($ex->getMessage());
    }
}
