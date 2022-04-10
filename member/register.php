<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "../header.php";
?>

<html>
	<head>
		<title>LMS</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/form_styles.css">
		<link rel="stylesheet" href="css/register_style.css">
	</head>
	<body>
		<form class="cd-form" method="POST" action="#">
			<center><legend>Member Registration</legend><p>Please fillup the form below:</p></center>
			
				<div class="error-message" id="error-message">
					<p id="error"></p>
				</div>

				<div class="icon">
					<input class="m-name" type="text" name="m_name" placeholder="Full Name" required />
				</div>

				<div class="icon">
					<input class="m-email" type="email" name="m_email" id="m_email" placeholder="Email" required />
				</div>
				
				<div class="icon">
					<input class="m-user" type="text" name="m_user" id="m_user" placeholder="Username" required />
				</div>
				
				<div class="icon">
					<input class="m-pass" type="password" name="m_pass" placeholder="Password" required />
				</div>
			
				
				<div class="icon">
					<input class="m-balance" type="number" name="m_balance" id="m_balance" placeholder="Initial Balance" required />
				</div>
				
				<br />
				<input type="submit" name="m_register" value="Submit" />
		</form>
	</body>
	
	<?php
		if(isset($_POST['m_register']))
		{
			if($_POST['m_balance'] < 500)
				echo error_with_field("Initial balance must be at least 500 in order to create an account", "m_balance");
			else
			{
				$query = $con->prepare("(SELECT username FROM member WHERE username = ?);");
				$query->execute(array($_POST['m_user']));
				if($row=$query->fetch())
					echo error_with_field("The username you entered is already taken", "m_user");
				else
				{
					$query = $con->prepare("(SELECT email FROM member WHERE email = ?) ;	");
					$query->execute(array($_POST['m_email']));
					if($row=$query->fetch())
						echo error_with_field("An account is already registered with that email", "m_email");
					else
					{
						$query = $con->prepare("INSERT INTO member(username, password, name, email, balance,state) VALUES(?, ?, ?, ?,?, 'pending');");
						try {$query->execute(array( $_POST['m_user'], sha1($_POST['m_pass']), $_POST['m_name'], $_POST['m_email'], $_POST['m_balance']));
						echo success("Details submitted, soon you'll will be notified after verifications!");
						}
						catch( Exception $e)
						{
							echo error_without_field("Couldn\'t record details. Please try again later");
						}
					}
				}
			}
		}
	?>
	
</html>