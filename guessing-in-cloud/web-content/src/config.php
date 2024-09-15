<?php
require 'vendor/autoload.php';

use Aws\DynamoDb\DynamoDbClient;
use Aws\Exception\AwsException;

function createSDK()
{
   try {
      $sdk = new Aws\Sdk([
         'region'   => 'us-east-1',
         'version'  => 'latest',
      ]);
      // Create a DynamoDB client an return
      return $sdk->createDynamoDb();
   } catch (AwsException $e) {
      // Handle errors during SDK initialization
      echo "AWS SDK error: " . $e->getMessage();
   }
}
