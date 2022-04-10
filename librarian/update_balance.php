<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "verify_librarian.php";
	require "header_librarian.php";
?>

<html>
	<head>
		<title>LMS</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css" />
		<link rel="stylesheet" type="text/css" href="../css/form_styles.css" />
		<link rel="stylesheet" href="css/update_balance_style.css">
	</head>
	<body>
		<form class="cd-form" method="POST" action="#">
			<center><legend>Update Member's Total Balance</legend></center>
			
				<div class="error-message" id="error-message">
					<p id="error"></p>
				</div>
				
				<div class="icon">
					<input class="m-user" type='text' name='m_user' id="m_user" placeholder="Member username" required />
				</div>
				
				<div class="icon">
					<input class="m-balance" type="number" name="m_balance" placeholder="Balance to add" required />
				</div>
				
				<input type="submit" name="m_add" value="Update Balance" />
		</form>
	</body>
	
	<?php
		if(isset($_POST['m_add']))
		{
			$query = $con->prepare("SELECT username FROM member WHERE username = ?;");
			$query->execute(array( $_POST['m_user']));
				$query2 = $con->prepare("SELECT count(*) FROM member WHERE username = ? AND state='active';");
			$query2->execute(array( $_POST['m_user']));
			
			if($query2->fetchColumn() != 1)
				echo error_with_field("Invalid username", "m_user");
			else
			{
				$query = $con->prepare("UPDATE member SET balance = balance + ? WHERE username = ?;");
				try {$query->execute(array($_POST['m_balance'], $_POST['m_user']));
				echo success("Balance successfully updated");}
				catch(Eexception $e){
					die(error_without_field("ERROR: Couldn\'t add balance"));
			}
			}
		}
	?>
</html>