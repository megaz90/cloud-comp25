<?php
require 'vendor/autoload.php';

use Aws\DynamoDb\DynamoDbClient;

// Initialize the DynamoDB client
$dynamoDbClient = new DynamoDbClient([
   'region'  => 'us-east-1',
   'version' => 'latest',
]);

$tables = $dynamoDbClient->listTables();
if (!in_array($tableName, $tables['TableNames'])) {
   print_r('Tables does not exist');
   die;
} else {
   die('sss');
}

// Example: Create a DynamoDB table (if you want to create it via PHP)
// Note: Tables should typically be managed via infrastructure code (CloudFormation/Terraform)
$tableName = 'cloud_guessing_game';

// // Insert example data
// $dynamoDbClient->putItem([
//    'TableName' => $tableName,
//    'Item' => [
//       'cloud_id' => ['N' => '1'],
//       'name' => ['S' => 'example'],
//       'value' => ['N' => '100'],
//       'max_value' => ['N' => '200']
//    ]
// ]);
