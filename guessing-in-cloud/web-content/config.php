<?php
require 'vendor/autoload.php';

use Aws\DynamoDb\DynamoDbClient;

$dynamodb = new DynamoDbClient([
    'region'  => 'us-east-1',
    'version' => 'latest',
    'credentials' => [
        'profile' => 'default',
    ],
]);
?>
