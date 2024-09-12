<?php
require 'vendor/autoload.php';

use Aws\DynamoDb\DynamoDbClient;

// Initialize the DynamoDB client
$dynamoDbClient = new DynamoDbClient([
   'region'  => 'us-east-1',
   'version' => 'latest',
]);

// Example: Create a DynamoDB table (if you want to create it via PHP)
// Note: Tables should typically be managed via infrastructure code (CloudFormation/Terraform)
$tableName = 'guessing-game';
$dynamoDbClient->createTable([
   'TableName' => $tableName,
   'KeySchema' => [
      [
         'AttributeName' => 'cloud_id',
         'KeyType' => 'HASH'  // Partition key
      ]
   ],
   'AttributeDefinitions' => [
      [
         'AttributeName' => 'cloud_id',
         'AttributeType' => 'N'  // Number type
      ]
   ],
   'ProvisionedThroughput' => [
      'ReadCapacityUnits' => 5,
      'WriteCapacityUnits' => 5
   ]
]);

// Insert example data
$dynamoDbClient->putItem([
   'TableName' => $tableName,
   'Item' => [
      'cloud_id' => ['N' => '1'],
      'name' => ['S' => 'example'],
      'value' => ['N' => '100'],
      'max_value' => ['N' => '200']
   ]
]);
