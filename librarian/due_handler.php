<?php
	require "../db_connect.php";
	require "verify_librarian.php";
	require "header_librarian.php";
?>

<html>
	<head>
		<title>LMS</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css" />
	</head>
	<body>
	
	<?php

		$query = $con->prepare("CALL generate_due_list();");
		$query->execute();		
		if($rows =$query->fetch())
		{
			$successfulEmails = 0;
			$idArray;
			$header = 'From: <noreply@library.com>' . "\r\n";
			$subject = "Return your book today";
			$query = "";
		
			while($row =$query->fetch())
			{$i=0;
				$to = $row[1];
				$message = "This is a reminder to return the book '".$row[3]."' with ISBN ".$row[2]." to the library.";
				if(mail($to, $subject, $message, $header) != FALSE)
				{
					$idArray[$i] = $row[0];
					$successfulEmails++;
					$i++;
				}
			}
			
			
			while($row =$query->fetch())
			{
				$query = $con->prepare("UPDATE book_issue_log SET last_reminded = CURRENT_DATE WHERE issue_id = ?;");
				$query->execute(array($idArray[$i]));
				$query->fetch();
			}
			
			if($successfulEmails > 0)
				echo "<h2 align='center'>Successfully notified ".$successfulEmails." members</h2>";
			else
				echo "ERROR: Couldn't notify any member.";
		}
		else
			echo "<h2 align='center'>No Pending Reminders</h2>";
	?>
	</body>
</html>