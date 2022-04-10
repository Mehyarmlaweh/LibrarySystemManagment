<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "verify_librarian.php";
	require "header_librarian.php";
?>

<html>
	<head>
		<title>LMS</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/custom_checkbox_style.css">
		<link rel="stylesheet" type="text/css" href="css/pending_registrations_style.css">
	</head>
	<body>
		<?php
			$query = $con->prepare("SELECT username, name, email, balance FROM member where state='pending'");
			$query->execute();
				$query2 = $con->prepare("SELECT count(*) FROM member where state='pending'");
			$query2->execute();
			$rows=$query2->fetchColumn();
			if($rows == 0)
				echo "<h2 align='center'>None at the moment!</h2>";
			else
			{
				echo "<form class='cd-form' method='POST' action='#'>";
				echo "<center><legend>Pending Membership Registration</legend></center>";
				echo "<div class='error-message' id='error-message'>
						<p id='error'></p>
					</div>";
				echo "<table width='100%' cellpadding=10 cellspacing=10>
						<tr>
							<th></th>
							<th>Username<hr></th>
							<th>Name<hr></th>
							<th>Email<hr></th>
							<th>Balance<hr></th>
						</tr>";
          while($row=$query->fetch()){
			  $i=0;
					echo "<tr>";
					echo "<td>
							<label class='control control--checkbox'>
								<input type='checkbox' name='cb_".$i."' value='".$row[0]."' />
								<div class='control__indicator'></div>
							</label>
						</td>";
					$j;
					for($j=0; $j<3; $j++)
						echo "<td>".$row[$j]."</td>";
					echo "<td>Rs.".$row[$j]."</td>";
					echo "</tr>";
					$i++;
				}
				echo "</table><br /><br />";
				echo "<div style='float: right;'>";
				
				echo "<input type='submit' value='Confirm Verification' name='l_confirm' />&nbsp;&nbsp;&nbsp;";
				echo "<input type='submit' value='Reject' name='l_delete' />";
				echo "</div>";
				echo "</form>";
			}
			
			$header = 'From: <noreply@libraryms.com>' . "\r\n";
			
			if(isset($_POST['l_confirm']))
			{
				$members = 0;
				for($i=0; $i<$rows; $i++)
				{
					if(isset($_POST['cb_'.$i]))
					{
						$username =  $_POST['cb_'.$i];
						
						$query = $con->prepare("UPDATE member set state ='active' where username=?;");
					try{	$query->execute( array($username));
					}catch(Exception $e){
					die(error_without_field("ERROR: Couldn\'t insert values"));}
						$members++;
						
						$to = $row[3];
						$subject = "Library membership has been accepted";
						$message = "Your membership has been accepted by the library. You can now issue books using your account.";
						mail($to, $subject, $message, $header);
					}
				}
				if($members > 0)
					echo success("Successfully added ".$members." members");
				else
					echo error_without_field("No registration selected");
			}
			
			if(isset($_POST['l_delete']))
			{
				$requests = 0;
				for($i=0; $i<$rows; $i++)
				{
					if(isset($_POST['cb_'.$i]))
					{
						$username =  $_POST['cb_'.$i];
		
						$query = $con->prepare("SELECT email FROM member WHERE username = ?;");
						$query->execute(array($username));
						$resultRow = $query->fetchColumn();
						$email = $resultRow['balance'];
						$query = $con->prepare("DELETE FROM member WHERE username = ?;");
						try{$query->execute(array($username));}
						catch(Exception $e){
						die(error_without_field("ERROR: Couldn\'t delete values"));}
						$requests++;
						
						$to = $email;
						$subject = "Library membership rejected";
						$message = "Your membership has been rejected by the library. Please contact a librarian for further information.";
						mail($to, $subject, $message, $header);
					}
				}
				if($requests > 0)
					echo success("Successfully Deleted ".$requests." requests");
				else
					echo error_without_field("No registration selected");
			}
		?>
	</body>
</html>