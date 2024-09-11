<?php
session_start();
include 'config.php';
?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

<?php
if (!isset($_SESSION["team"]))
{
   if (isset($_POST["team_minus"]))
   {
      print "You joined team good...";
      $_SESSION["team"]="minus";
   }
   elseif (isset($_POST["team_plus"]))
   {
      print "You joined team evil...";
      $_SESSION["team"]="plus";
   }
   else
   {
      print "<h1>Choose your side!</h1>";
      ?>
      <form action="" method="post">
      <input type="submit" name="team_minus" value="Team Good">
      <input type="submit" name="team_plus" value="Team Evil">
      </form>
      <?php
   }
}

// TODO when round start, "the cloud" needs to decide on a random number within the max 

if (isset($_SESSION["team"]))
{
   ?>

   <h1>Guess!!!</h1>
   <p>
   <?php
   if (isset($_GET['cloud_id']))
   {
      $cloudId = $_GET['cloud_id'];

      // Query DynamoDB
      $result = $dynamodb->getItem([
         'TableName' => 'cloud_tug_of_war',
         'Key' => [
               'cloud_id' => ['N' => (string) $cloudId],
         ],
      ]);

      $item = $result->get('Item');

      if ($item) {
         $value = $item['value']['N'];
         $maxValue = $item['max_value']['N'];

         if (abs(intval($value)) >= intval($maxValue)) {
            unset($_SESSION["team"]);
            $_SESSION["finished"] = true;
        }

        if (isset($_POST['pull'])) {
            if ($_SESSION["team"] == "plus") {
               print "You guessed for team evil...";
               $dynamodb->updateItem([
                  'TableName' => 'cloud_tug_of_war',
                  'Key' => [
                        'cloud_id' => ['N' => (string) $cloudId],
                  ],
                  'UpdateExpression' => 'SET value = value + :val',
                  'ExpressionAttributeValues' => [
                        ':val' => ['N' => '1'],
                  ],
               ]);
            }
            if ($_SESSION["team"] == "minus") {
               print "You guessed for team good...";
               $dynamodb->updateItem([
                  'TableName' => 'cloud_tug_of_war',
                  'Key' => [
                     'cloud_id' => ['N' => (string) $cloudId],
                  ],
                  'UpdateExpression' => 'SET value = value - :val',
                  'ExpressionAttributeValues' => [
                     ':val' => ['N' => '1'],
                  ],
               ]);
            }
         }
      

         print "<h2>Value: " . $value . " (Goal: " . $maxValue . ")</h2>";

         if (!isset($_SESSION["finished"])) {
               ?>
               <form action="" method="post">
                  <input type="submit" name="pull" value="Pull">
               </form>
               <?php
         } else {
               print "<h2>game over</h2>";
               if (intval($value) == intval($maxValue)) {
                  print "Team Evil won the round!";
               } else {
                  print "Team Good won the round!";
               }
               session_destroy();
         }
      }  
   }
}
?>
<br />
<a href='index.php'>Leave Game</a>
