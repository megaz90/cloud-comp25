<?php
include './config.php';
include './functions.php';

//Dynamo Client
$client = createSDK();
//All games
$games = listGames($client, 'cloud_guessing_game');
?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<div class="container-fluid">
   <div class="row">
      <nav class="navbar navbar-dark bg-dark">
         <span class="navbar-brand mb-0 h1">Number Guesser</span>
      </nav>
   </div>
   <div class="mt-4">
      <h1 class="text-center">Welcome to the world of 25th Group!</h1>
   </div>
   <p>
      Already existing games:
   </p>
   <p>
      <?php if (count($games) > 0): ?>
         <?php foreach ($games as $game): ?>
            <a href='cloud.php?cloud_id=<?= $game['cloud_id']['S'] ?>'>
               <?= $game['name']['S'] ?>
            </a> (score: <?= $game['value']['N'] ?>, goal: <?= $game['max_value']['N'] ?>)<br />
         <?php endforeach; ?>
      <?php else: ?>
   <p>No games found.</p>
<?php endif; ?>
</p>
<p>
   or create a new game:
<form action="./process_game.php" method="POST">
   Game Name: <input class="form-control" name="new_cloud_name" value="">
   Maximum Number: <input class="form-control" name="new_cloud_goal" value="10">
   <input type="submit" value="Create">
</form>
</p>
</div>