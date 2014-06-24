<?php
require_once "base.php";
?>
<!DOCTYPE html>
<html>
<head>
	<title>Steam Giveaways</title>
	<link rel="stylesheet" href="css/bootstrap.min.css"/>
</head>
<body>
	<nav class="navbar navbar-default">
		<div class="container">
			<div class="navbar-header">
				<a class="navbar-brand" href="/">Steam Giveaways</a>
			</div>
			<ul class="nav navbar-nav">
				<li><a href="/">Home</a></li>
				<li><a href="/newgiveaway.php">New Giveaway</a></li>
				<li><a href="/donate.html">Donate</a></li>
			</ul>
		</div>
	</nav>
	<div class="container">
		<?php
		if (isset($_GET['token']) || isset($_GET['id'])) {
      if (isset($_GET['token'])) {
        $token = $_GET['token'];
        $stmt = $db->prepare('SELECT id, gametitle, gameplatform, chance, claimed, thankyou, userthankyou, gamekey FROM `keys` WHERE token=?');
        $stmt->bind_param('s', $token);
      } else if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $db->prepare('SELECT id, gametitle, gameplatform, chance, claimed, thankyou, userthankyou, gamekey FROM `keys` WHERE token=\'\' AND id=?');
        $stmt->bind_param('i', $id);
      }
			$stmt->execute();
			$stmt->store_result();
			$num_rows = $stmt->num_rows;
		}
		if ($num_rows == 0) {
			echo 'Giveaway not found.';
			die();
		}

		$stmt->bind_result($id, $title, $platform, $chance, $claimed, $ty, $uty, $gamekey);
		$stmt->fetch();
    $showGiveawayEntry = true;
    if ($_POST) {
      $showGiveawayEntry = false;
      if (isset($_POST['submit'])) {
        if (empty(RECAPTCHA_PRIVATE_KEY)) {
          $validReCAPTCHA = true;
        } else {
          require_once('recaptchalib.php');
          $resp = recaptcha_check_answer(RECAPTCHA_PRIVATE_KEY, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
          $validReCAPTCHA = $resp->is_valid;
        }

        if ($validReCAPTCHA) {
          $roll = crypto_rand_secure(1, $chance);
          if ($roll == $chance) {
            ?>
            <div class="alert alert-success">You won! Hooray! Your <?php echo $platform?> game key is <strong><?php echo $gamekey?></strong>.
              <p>Please leave a note for the person who gifted you the game. This closes the giveaway and is mandatory.
                <div class="form" style="margin-top: 20px">
                  <form class="form-horizontal" action="" method="POST">
                    <input name="key" type="hidden" value="<?php echo $gamekey; ?>">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Your username</label>
                      <div class="col-sm-5">
                        <input name="name" type="text" class="form-control" placeholder="Preferably your reddit one.">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Your message</label>
                      <div class="col-sm-5">
                        <textarea class="form-control" name="message"></textarea>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Go!</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
              <?php
          } else {
            ?>
            <div class="alert alert-warning">You didn't win. :(<br>Feel free to try again!<br>You rolled a <?php echo $roll?>. You must roll a <?php echo $chance?> to get the key.</div>
            <?php
            $showGiveawayEntry = true;
          }
        } else {
          ?>
          <div class="alert alert-danger">You failed the captcha! Robot scum.</div>
          <?php
          $showGiveawayEntry = true;
        }
      } else if (isset($_POST['key']) && ($_POST['key'] == $gamekey)) {
        $ty = stripslashes(htmlspecialchars($_POST['message']));
        $uty = stripslashes(htmlspecialchars($_POST['name']));
        $stmt = $db->prepare('UPDATE `keys` SET claimed=?, thankyou=?, userthankyou=? WHERE id=?');
        $claimed = true;
        $stmt->bind_param('issi', $claimed, $ty, $uty, $id);
        $stmt->execute();
        $showGiveawayEntry = true;
      } else {
        // This should never be reached, unless some data is missing from POST requests...
        echo "<div class=\"alert alert-warning\">Something went wrong - please try again.</div>";
        $showGiveawayEntry = true;
      }
    }
    if ($showGiveawayEntry) {
		?>
		<div class="well">
		<?php
		if ($claimed == 0){
			?>
			<p class="lead">This <?php echo $platform?> copy of <strong><?php echo $title?></strong> has not been claimed yet!</p>
			<p>Fill out this CAPTCHA and click "Go!" for a 1 in <?php echo $chance;?> chance of getting the key! Good luck!</p>
			<form action="" method="POST">
			<?php
      if (!empty(RECAPTCHA_PUBLIC_KEY)) {
        require_once('recaptchalib.php');
        echo recaptcha_get_html(RECAPTCHA_PUBLIC_KEY);
      }
			?>
      <input type="submit" name="submit" value="Go!" class="btn btn-lg btn-primary" style="margin-top: 15px;">
			</form>
			<?php
		} else {
			?>
			<p class="lead">This <?php echo $platform?> copy of <strong><?php echo $title?></strong> has been claimed!</p>
			<p>A message from the winner:</p>
			<blockquote>
				<p><?php echo $ty?></p>
				<footer><?php echo $uty?></footer>
			</blockquote>
				<?php
		}
		?>
		</div>
    <?php
    }
    ?>
	</div>
</body>
</html>