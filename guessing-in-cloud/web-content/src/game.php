<?php
include './config.php';
include './functions.php';

// Get DynamoDB Client
$client = createSDK();

// Get game ID from the URL
$gameId = $_GET['game_id'] ?? null;

if (!$gameId) {
   echo "Invalid Game ID!";
   exit;
}

// Fetch the game data from DynamoDB
$game = getGameById($client, TABLE_NAME, $gameId);

// Check if the game exists
if (!$game) {
   echo "Game not found!";
   exit;
}

// Handle game guesses
$guess = (int) ($_POST['guess'] ?? 0);
$message = '';
if ($guess) {
   $message = makeGuess($client, TABLE_NAME, $gameId, $_SESSION['player_id'], $guess);
}

// Fetch the list of players in the game
$players = listPlayersInGame($client, TABLE_NAME, $gameId);
$gameWon = checkIfGameIsWon($client, TABLE_NAME, $gameId);

?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Play Number Guesser</title>
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>

<body>
   <div class="container">
      <div class="row">
         <div class="col-md-12">
            <nav class="navbar navbar-dark bg-dark">
               <span class="navbar-brand mb-0 h1">Number Guesser Game</span>
            </nav>
         </div>
      </div>

      <?php if (isset($_SESSION['player_name']) && isset($_SESSION['player_id'])): ?>
         <div class="mt-4">
            <h1 class="text-center">Guess a number between 1 and <?= $game['max_value']['N'] ?>.</h1>
            <p class="text-center">Game: <strong><?= $game['name']['S'] ?></strong></p>
         </div>

         <?php if ($gameWon): ?>
            <div class="alert alert-success text-center">
               <strong>Game Over!</strong> The correct number has been guessed!
            </div>
         <?php else: ?>
            <div class="row justify-content-center">
               <div class="col-md-6">
                  <div class="card">
                     <div class="card-body">
                        <!-- Display Player Feedback -->
                        <?php if ($message): ?>
                           <div class="alert alert-info text-center" role="alert">
                              <?= $message ?>
                           </div>
                        <?php endif; ?>

                        <!-- Guessing Form (only if player is part of the game) -->
                        <?php if ($playerId): ?>
                           <form method="POST" action="">
                              <div class="form-group">
                                 <label for="guess">Enter your guess:</label>
                                 <input type="number" name="guess" id="guess" class="form-control" placeholder="Your guess" required min="1" max="<?= $game['max_value']['N'] ?>">
                              </div>
                              <button type="submit" class="btn btn-primary btn-block">Submit Guess</button>
                           </form>
                        <?php else: ?>
                           <div class="alert alert-warning text-center">
                              You need to join the game before guessing.
                           </div>
                        <?php endif; ?>
                     </div>
                  </div>
               </div>
            </div>
         <?php endif; ?>
      <?php else: ?>
         <div class="mt-4">
            <h1 class="text-center">Join the Game</h1>
            <form method="POST" action="./process_player.php?game_id=<?= $gameId ?>">
               <div class="form-group">
                  <label for="player_name">Enter your name:</label>
                  <input type="text" name="player_name" id="player_name" class="form-control" placeholder="Your name" required>
               </div>
               <div class="form-group">
                  <label for="player_id">Enter your ID to continue old game:</label>
                  <input type="text" name="player_id" id="player_id" class="form-control" placeholder="If you remember your ID then enter here other new Game will begin...">
               </div>
               <button type="submit" class="btn btn-primary btn-block">Join Game</button>
            </form>
         </div>
      <?php endif; ?>

      <!-- Display Current Players -->
      <div class="mt-4">
         <h3 class="text-center">Current Players</h3>
         <ul class="list-group">
            <?php if (count($players) > 0): ?>
               <?php foreach ($players as $player): ?>
                  <li class="list-group-item">
                     <?= $player['player_name']['S'] ?> (Attempts: <?= $player['attempts']['N'] ?>, Last Guess: <?= $player['last_guess']['N'] ?>)
                  </li>
               <?php endforeach; ?>
            <?php else: ?>
               <li class="list-group-item text-center">No players have joined yet.</li>
            <?php endif; ?>
         </ul>
      </div>
   </div>

   <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>