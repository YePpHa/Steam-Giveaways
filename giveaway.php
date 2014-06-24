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
		if (isset($_GET['id'])) {
			$id = $_GET['id'];
			$db = new mysqli('REDACTED', 'REDACTED', 'REDACTED', 'REDACTED');

			if($db->connect_errno > 0){
			    die('Unable to connect to database [' . $db->connect_error . ']');
			}
			$stmt = $db->prepare('SELECT gametitle, gameplatform, chance, claimed, thankyou, userthankyou, gamekey FROM `keys` WHERE id=?');
			$stmt->bind_param('i', $id);
			$stmt->execute();
			$stmt->store_result();
			$num_rows = $stmt->num_rows;
		}
		if ($num_rows == 0) {
			echo 'Giveaway not found.';
			die();
		}

		$stmt->bind_result($title, $platform, $chance, $claimed, $ty, $uty, $gamekey);
		$stmt->fetch();

		if ($_POST) {
			if (!isset($_POST['name'])) {

				require_once('recaptchalib.php');
				$private_key = 'REDACTED';
				$resp = recaptcha_check_answer($private_key, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);

				if (!$resp->is_valid) {
					?>
					<div class="alert alert-danger">You failed the captcha! Robot scum.</div>
					<?php
				} else {
					$roll = mt_rand(1, $chance);
					if ($roll == $chance) {
						?>
						<div class="alert alert-success">You won! Hooray! Your <?php echo $platform?> game key is <strong><?php echo $gamekey?></strong>.
							<p>Please leave a note for the person who gifted you the game. This closes the giveaway and is mandatory.
								<div class="form" style="margin-top: 20px">
									<input name="key" type="hidden" value="<?php echo $gamekey; ?>">
									<form class="form-horizontal" action="" method="POST">
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
							}
						}
					} else if (isset($_POST['key']) && ($_POST['key'] == $gamekey)) {
						$thankyou = stripslashes(htmlspecialchars($_POST['message']));
						$userthankyou = stripslashes(htmlspecialchars($_POST['name']));
						$stmt = $db->prepare('UPDATE `keys` SET claimed=?, thankyou=?, userthankyou=? WHERE id=?');
						$claimed = true;
						$stmt->bind_param('issi', $claimed, $thankyou, $userthankyou, $id);
						$stmt->execute();
					} else {
						// This should never be reached, unless some data is missing from POST requests...
						echo "<div class=\"alert alert-warning\">Something went wrong - please try again.";
					}
				}
		?>
		<div class="well">
		<?php
		if ($claimed == 0){
			?>
			<p class="lead">This <?php echo $platform?> copy of <strong><?php echo $title?></strong> has not been claimed yet!</p>
			<p>Fill out this CAPTCHA and click "Go!" for a 1 in <?php echo $chance;?> chance of getting the key! Good luck!</p>
			<form action="" method="post">
			<?php
			require_once('recaptchalib.php');
			$pubkey = 'REDACTED';
			echo recaptcha_get_html($pubkey);
			?>
			<button class="btn btn-lg btn-primary" style="margin-top: 15px;" type="submit">Go!</button>
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
	</div>
</body>
</html>