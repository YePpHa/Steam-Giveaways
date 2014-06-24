<!DOCTYPE html>
<html>
<head>
	<title>Steam Giveaways</title>
	<link rel="stylesheet" href="css/bootstrap.min.css"/>
	<style>
	.form {margin-top: 40px;}
	</style>
</head>
<body>
	<nav class="navbar navbar-default">
		<div class="container">
			<div class="navbar-header">
				<a class="navbar-brand" href="/">Steam Giveaways</a>
			</div>
			<ul class="nav navbar-nav">
				<li><a href="/">Home</a></li>
				<li class="active"><a href="/newgiveaway.php">New Giveaway</a></li>
				<li><a href="/donate.html">Donate</a></li>
			</ul>
		</div>
	</nav>
	<div class="container">
		<?php
		if ($_POST) {
			$errors = false;
			if ($_POST['gamename'] !== '' && $_POST['gamekey'] !== '' && $_POST['gameplatform'] !== '' && $_POST['chance'] !== '') {			
				if (ctype_digit($_POST['chance'])) {
					if ((int)$_POST['chance'] > 5000) {
						$errors = true;
						$message = 'Chance too low.';
					}
				} else {
					$errors = true;
					$message = 'Chance must be an integer.';
				}
			} else {
				$errors = true;
				$message = 'You left something blank.';
			}
			if ($errors) {
				?>
				<div class="alert alert-danger"><span class="glyphicon glyphicon-info-sign"></span> <?php echo $message;?></div>
				<?php
			}

			if (!$errors) {
				$gamename = stripslashes(htmlspecialchars($_POST['gamename']));
				$gamekey = htmlspecialchars($_POST['gamekey']);
				$gameplatform = htmlspecialchars($_POST['gameplatform']);
				$chance = (int)$_POST['chance'];

				$db = new mysqli('REDACTED', 'REDACTED', 'REDACTED', 'REDACTED');

				if($db->connect_errno > 0){
				    die('Unable to connect to database [' . $db->connect_error . ']');
				}

				$stmt = $db->prepare('INSERT INTO `keys` (gamekey, gametitle, gameplatform, chance) VALUES (?,?,?,?)');
				$stmt->bind_param('sssi', $gamekey, $gamename, $gameplatform, $chance);
				$stmt->execute();
				$id = $db->insert_id;

				?>
				<div class="alert alert-success"><span class="glyphicon glyphicon-ok-sign"></span> Your giveaway has been created.<br>
					Your public URL is <a href="http://steamgiveaways.jacksonroberts.me/giveaway.php?id=<?php echo $id?>">http://steamgiveaways.jacksonroberts.me/giveaway.php?id=<?php echo $id?></a></div>
				<?php
			}
		}
		?>
		<div class="well">
			<h3>Create a New Giveaway!</h3>
			<p>Aren't you generous?</p>
			<div class="form">
				<form class="form-horizontal" action="" method="POST">
				  <div class="form-group">
				    <label class="col-sm-2 control-label">Game Name</label>
				    <div class="col-sm-8">
				      <input name="gamename" type="text" class="form-control" placeholder="As it would appear in your game library.">
				    </div>
				  </div>
				  <div class="form-group">
				  	<label class="col-sm-2 control-label">Game Key</label>
				  	<div class="col-sm-8">
				  		<input name="gamekey" type="text" class="form-control" placeholder="N0TR3-ALK3Y-JSTEX">
				  	</div>
				  </div>
				  <div class="form-group">
				  	<label class="col-sm-2 control-label">Game platform</label>
				  	<div class="col-sm-5">
				  		<select class="form-control" name="gameplatform">
				  			<option value="Steam">Steam</option>
				  			<option value="Origin">Origin</option>
				  		</select>
				  	</div>
				  </div>
				  <div class="form-group">
				  	<label class="col-sm-2 control-label">Chance of giving</label>
				  	<div class="col-sm-3">
				  		<div class="input-group">
				  			<span class="input-group-addon">One out of</span>
				  			<input class="form-control" type="text" name="chance" value="100">
				  		</div>
				  	</div>
				  </div>
				  <div class="form-group">
				    <div class="col-sm-offset-2 col-sm-10">
				      <button type="submit" class="btn btn-primary">Create!</button>
				    </div>
				  </div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>