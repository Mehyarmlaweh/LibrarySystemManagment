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
		<link rel="stylesheet" type="text/css" href="css/pending_book_requests_style.css">
	</head>
	<body>
		<?php
			$query = $con->prepare("SELECT * FROM pending_book_requests;");
			$query->execute();
						$query2 = $con->prepare("SELECT count(*) FROM pending_book_requests;");
			$query2->execute();
			$rows = $query2->fetchColumn();
			if($rows == 0)
				echo "<h2 align='center'>No requests pending</h2>";
			else
			{
				echo "<form class='cd-form' method='POST' action='#'>";
				echo "<center><legend>Pending book requests</legend></center>";
				echo "<div class='error-message' id='error-message'>
						<p id='error'></p>
					</div>";
				echo "<table width='100%' cellpadding=10 cellspacing=10>
						<tr>
							<th></th>
							<th>Username<hr></th>
							<th>Book<hr></th>
							<th>Time<hr></th>
						</tr>";
			while($row=$query->fetch())
				{ $i=0;
					echo "<tr>";
					echo "<td>
							<label class='control control--checkbox'>
								<input type='checkbox' name='cb_".$i."' value='".$row[0]."' />
								<div class='control__indicator'></div>
							</label>
						</td>";
					for($j=1; $j<4; $j++)
						echo "<td>".$row[$j]."</td>";
					echo "</tr>";
					$i++;
				}
				echo "</table>";
				echo "<br /><br /><div style='float: right;'>";
				echo "<input type='submit' value='Reject Request' name='l_reject' />&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<input type='submit' value='Allow' name='l_grant'/>";
				echo "</div>";
				echo "</form>";
			}
			
			$header = 'From: <noreply@MehyarLibrary.com>' . "\r\n";
			
			if(isset($_POST['l_grant']))
			{
				$requests = 0;
				for($i=0; $i<$rows; $i++)
				{
					if(isset($_POST['cb_'.$i]))
					{
						$request_id =  $_POST['cb_'.$i];
						$query = $con->prepare("SELECT member, book_isbn FROM pending_book_requests WHERE request_id = ?;");
						$query->execute(array($request_id));
						$resultRow = $query->fetch();
						$member = $resultRow[0];
						$isbn = $resultRow[1];
						$query = $con->prepare("INSERT INTO book_issue_log(member, book_isbn) VALUES(?, ?);");
						$query->execute(array($member, $isbn));
						$requests++;
                        $query = $con->prepare("select  balace from books WHERE isbn = ?;");
						$query->execute(array($isbn));
						$price=$query->fetchColumn();

						$query = $con->prepare("update member set balance= balance-? WHERE username = ?;");
						$query->execute(array($price,$member));
						
						$query = $con->prepare("SELECT email FROM member WHERE username = ?;");
						$query->execute(array( $member));
						$to = $query->fetch()[0];
						$subject = "Book has been issued !";
						
						$query = $con->prepare("SELECT title FROM book WHERE isbn = ?;");
						$query->execute(array($isbn));
						$title = $query->fetch()[0];
						
						$query = $con->prepare("SELECT due_date FROM book_issue_log WHERE member = ? AND book_isbn = ?;");
						$query->execute(array($member, $isbn));
						$due_date = $query->fetch()[0];
						$message = "The book '".$title."' with ISBN ".$isbn." has been issued to your account. The due date to return the book is ".$due_date.".";
						
						mail($to, $subject, $message, $header);
					}
				}
				if($requests > 0)
					echo success("Granted Successfully!".$requests." requests");
				else
					echo error_without_field("No request selected");
			}
			
			if(isset($_POST['l_reject']))
			{
				$requests = 0;
				for($i=0; $i<$rows; $i++)
				{
					if(isset($_POST['cb_'.$i]))
					{
						$requests++;
						$request_id =  $_POST['cb_'.$i];
						
						$query = $con->prepare("SELECT member, book_isbn FROM pending_book_requests WHERE request_id = ?;");
						$query->execute(array( $request_id));
						$resultRow = $query->fetch();
						$member = $resultRow[0];
						$isbn = $resultRow[1];
						
						$query = $con->prepare("SELECT email FROM member WHERE username = ?;");
						$query->execute(array($member));
						$to = $query->fetch()[0];
						$subject = "Book issue rejected";
						
						$query = $con->prepare("SELECT title FROM book WHERE isbn = ?;");
						$query->execute(array($isbn));
						$title = $query->fetch()[0];
						$message = "Your request for issuing the book '".$title."' with ISBN ".$isbn." has been rejected. You can request the book again or visit a librarian for further information.";
						
						$query = $con->prepare("DELETE FROM pending_book_requests WHERE request_id = ?");
						$query->execute(array($request_id));
	
						mail($to, $subject, $message, $header);
					}
				}
				if($requests > 0)
					echo success("Successfully deleted ".$requests." requests");
				else
					echo error_without_field("No request selected");
			}