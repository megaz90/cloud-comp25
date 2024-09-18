<?php
include './config.php';
include './functions.php';

//Get DynamoDB Client
$client = createSDK();
//Get all games
$games = listGames($client, TABLE_NAME);

?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Guessing Game</title>
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
   <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>

<body>
   <div class="container">
      <div class="row">
         <div class="col-md-12">
            <nav class="navbar navbar-dark bg-dark">
               <a class="navbar-brand mr-auto" href="index.php">Home</a>
               <span class="navbar-brand mb-0 h1">Number Guesser Game</span>
            </nav>
         </div>
      </div>
      <div class="mt-4">
         <h1 class="text-center">Guess a number and win.</h1>
      </div>
      <div class="card my-5">
         <h5 class="card-header text-center">Already existing games:</h5>
         <div class="card-body">
            <?php if (count($games) > 0): ?>
               <ul class="list-group">
                  <?php foreach ($games['game'] as $game): ?>
                     <li class="list-group-item">
                        <a href='game.php?game_id=<?= $game['game_id']['S'] ?>'>
                           <?= $game['name']['S'] ?>
                        </a> (goal: <?= $game['max_value']['N'] ?>)
                     </li>
                  <?php endforeach; ?>
               </ul>
            <?php else: ?>
               <h4 class="text-center">No games found.</h4>
            <?php endif; ?>
         </div>
      </div>

      <div class="card mt-5">
         <h5 class="card-header text-center">Create New Game</h5>
         <div class="card-body">
            <form action="./process_game.php" method="POST">
               <div class="form-group">
                  <label for="name">Game Name:</label>
                  <input type="text" class="form-control" id="name" name="new_cloud_name">
               </div>
               <div class="form-group">
                  <label for="number">Maximum Number</label>
                  <input type="number" class="form-control" id="number" name="new_cloud_goal">
               </div>
               <div class="form-group">
                  <label for="password">Password</label>
                  <input type="password" class="form-control" id="password" name="password" autocomplete="off">
                  <?php if (isset($_SESSION['error_password'])): ?>
                     <span class="text-danger"><?= $_SESSION['error_password'] ?></span>
                     <?php unset($_SESSION['error_password']); ?>
                  <?php endif; ?>
               </div>
               <button type="submit" class="btn btn-primary">Create</button>
            </form>
         </div>
      </div>
   </div>
</body>

</html>