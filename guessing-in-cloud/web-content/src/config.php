<?php
require __DIR__ . '/../vendor/autoload.php';

session_start();

use Aws\Exception\AwsException;

define('ADMIN_PASSWORD', 'password');
define('TABLE_NAME', 'cloud_guessing_game');

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
