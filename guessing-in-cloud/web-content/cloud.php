<?php
session_start();
include 'config.php';
include 'index.php';

// Set up Bootstrap
?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

<?php
// Team selection logic
if (!isset($_SESSION["team"])) {
   if (isset($_POST["team_good"])) {
      print "You joined Team Good!";
      $_SESSION["team"] = "good";
   } elseif (isset($_POST["team_evil"])) {
      print "You joined Team Evil!";
      $_SESSION["team"] = "evil";
   } else {
      print "<h1>Choose your team!</h1>";
      ?>
      <form action="" method="post">
         <input type="submit" name="team_good" value="Team Good" class="btn btn-success">
         <input type="submit" name="team_evil" value="Team Evil" class="btn btn-danger">
      </form>
      <?php
   }
}

// After a team is selected
if (isset($_SESSION["team"])) {
    // Get the maximum number from the form, set default to 10
    $max_number = isset($_POST['new_cloud_goal']) ? (int)$_POST['new_cloud_goal'] : 10;
    $_SESSION['max_number'] = $max_number;

    // Check if random number is already set, if not generate one
    if (!isset($_SESSION['random_number'])) {
        $_SESSION['random_number'] = rand(1, $max_number);
    }

    // Prepare to store guesses for each team
    if (!isset($_SESSION['guesses'])) {
        $_SESSION['guesses'] = ['good' => null, 'evil' => null]; // Store guesses for both teams
    }

    // Handle the guess submission
    if (isset($_POST['guess'])) {
        $guess = (int)$_POST['guess'];

        // Save the guess for the corresponding team
        if ($_SESSION["team"] == "good") {
            $_SESSION['guesses']['good'] = $guess;
            echo "<p>Team Good guessed: $guess</p>";
        } else {
            $_SESSION['guesses']['evil'] = $guess;
            echo "<p>Team Evil guessed: $guess</p>";
        }
    }

    // Check if both teams have made their guesses
    if ($_SESSION['guesses']['good'] !== null && $_SESSION['guesses']['evil'] !== null) {
        $random_number = $_SESSION['random_number'];
        $good_guess = $_SESSION['guesses']['good'];
        $evil_guess = $_SESSION['guesses']['evil'];

        // Calculate how close each guess is to the random number
        $good_diff = abs($random_number - $good_guess);
        $evil_diff = abs($random_number - $evil_guess);

        // Determine the winner
        if ($good_diff < $evil_diff) {
            echo "<h2>Team Good Wins! The random number was: $random_number</h2>";
        } elseif ($evil_diff < $good_diff) {
            echo "<h2>Team Evil Wins! The random number was: $random_number</h2>";
        } else {
            echo "<h2>It's a tie! Both teams guessed equally close to the random number: $random_number</h2>";
        }

        // End the game and reset the session
        session_destroy();
        echo '<a href="index.php" class="btn btn-primary">Start a New Game</a>';
        exit;
    }
}

?>

<!-- Display the guess form -->
<h1>Make Your Guess!</h1>
<form method="post" action="">
    <label for="guess">Enter your guess (between 1 and <?= $_SESSION['max_number'] ?>): </label>
    <input type="number" name="guess" min="1" max="<?= $_SESSION['max_number'] ?>" required>
    <input type="submit" value="Submit Guess" class="btn btn-primary">
</form>

<br />
<a href='index.php' class="btn btn-secondary">Leave Game</a>

<?php
?>
