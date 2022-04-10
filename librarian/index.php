<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "../verify_logged_out.php";
	require "../header.php";
?>

<html>
	<head>
		<title>LMS</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/form_styles.css">
		<link rel="stylesheet" type="text/css" href="css/index_style.css">
	</head>
	<body>
		<form class="cd-form" method="POST" action="#">
		
		<center><legend>Librarian Login</legend></center>

			<div class="error-message" id="error-message">
				<p id="error"></p>
			</div>
			
			<div class="icon">
				<input class="l-user" type="text" name="l_user" placeholder="Username" required />
			</div>
			
			<div class="icon">
				<input class="l-pass" type="password" name="l_pass" placeholder="Password" required />
			</div>
			
			<input type="submit" value="Login" name="l_login"/>

			
			
		</form>
		<p align="center"><a href="../index.php" style="text-decoration:none;">Go Back</a>
	</body>
	
	<?php
		if(isset($_POST['l_login']))
		{
			$query = $con->prepare("SELECT id FROM librarian WHERE username = ? AND password = ?;");
			$query->execute(array( $_POST['l_user'], $_POST['l_pass']));
			$row=$query->fetch();
			if($row==0)
				echo error_without_field("Invalid username/password combination");
			else
			{
				$_SESSION['type'] = "librarian";
				$_SESSION['id'] = $row['id'] ;
				$_SESSION['username'] = $_POST['l_user'];
				header('Location: home.php');
			}
		}
	?>
	
</html>