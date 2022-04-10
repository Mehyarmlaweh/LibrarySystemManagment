<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "verify_member.php";
	require "header_member.php";
?>

<html>
	<head>
		<title>LMS</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/custom_checkbox_style.css">
		<link rel="stylesheet" type="text/css" href="css/my_books_style.css">
	</head>
	<body>
	
		<?php
			$query = $con->prepare("SELECT book_isbn FROM book_issue_log WHERE member = ?;");
			$query->execute(array( $_SESSION['username']));
			$result = $query->fetch();
				$query2 = $con->prepare("SELECT count(*) FROM book_issue_log WHERE member = ?;");
			$query2->execute(array( $_SESSION['username']));
			$rows = $query2->fetchColumn();
			if($rows == 0)
				echo "<h2 align='center'>There Are No Issued Books Yet!</h2>";
			else
			{
				echo "<form class='cd-form' method='POST' action='#'>";
				echo "<center><legend>My Books</legend></center>";
				echo "<div class='success-message' id='success-message'>
						<p id='success'></p>
					</div>";
				echo "<div class='error-message' id='error-message'>
						<p id='error'></p>
					</div>";
				echo"<table width='100%' cellpadding='10' cellspacing='10'>
						<tr>
							<th></th>
							<th>ISBN<hr></th>
							<th>Title<hr></th>
							<th>Author<hr></th>
							<th>Category<hr></th>
							<th>Due Date<hr></th>
						</tr>";
				for($i=0; $i<$rows; $i++)
				{
					$isbn = $result[0];
					if($isbn != NULL)
					{
						$query = $con->prepare("SELECT title, author, category FROM book WHERE isbn = ?;");
						$query->execute(array($isbn));
						$innerRow =$query->fetch();
						echo "<tr>
								<td>
									<label class='control control--checkbox'>
										<input type='checkbox' name='cb_book".$i."' value='".$isbn."'>
										<div class='control__indicator'></div>
									</label>
								</td>";
						echo "<td>".$isbn."</td>";
						for($j=0; $j<3; $j++)
							echo "<td>".$innerRow[$j]."</td>";
						$query = $con->prepare("SELECT due_date FROM book_issue_log WHERE member = ? AND book_isbn = ?;");
						$query->execute(array($_SESSION['username'], $isbn));
						echo "<td>".$query->fetch()[0]."</td>";
						echo "</tr>";
					}
				}
				echo "</table><br />";
				echo "<input type='submit' name='b_return' value='Return Selected Books' />";
				echo "</form>";
			}
			
			if(isset($_POST['b_return']))
			{
				$books = 0;
				for($i=0; $i<$rows; $i++)
					if(isset($_POST['cb_book'.$i]))
					{
						$query = $con->prepare("SELECT due_date FROM book_issue_log WHERE member = ? AND book_isbn = ?;");
						$query->execute(array( $_SESSION['username'], $_POST['cb_book'.$i]));
						$due_date = $query->fetch()[0];
						
						$query = $con->prepare("SELECT DATEDIFF(CURRENT_DATE, ?);");
						$query->execute(array( $due_date));
						$days = (int)$query->fetch()[0];
						
						$query = $con->prepare("DELETE FROM book_issue_log WHERE member = ? AND book_isbn = ?;");
						try {
							$query->execute(array($_SESSION['username'], $_POST['cb_book'.$i]));
						}catch(Exception $e){
						die(error_without_field("ERROR: Couldn\'t return the books"));}
						
						if($days > 0)
						{
							$penalty = 5*$days;
							$query = $con->prepare("SELECT price FROM book WHERE isbn = ?;");
							$query->execute(array($_POST['cb_book'.$i]));
							$price = $query->fetch()[0];
							if($price < $penalty)
								$penalty = $price;
							$query = $con->prepare("UPDATE member SET balance = balance - ? WHERE username = ?;");
							$query->execute(array($penalty, $_SESSION['username']));
							echo '<script>
									document.getElementById("error").innerHTML += "A penalty of Rs. '.$penalty.' was charged for keeping book '.$_POST['cb_book'.$i].' for '.$days.' days after the due date.<br />";
									document.getElementById("error-message").style.display = "block";
								</script>';
						}
						$books++;
					}
				if($books > 0)
				{
					echo '<script>
							document.getElementById("success").innerHTML = "Successfully returned '.$books.' books";
							document.getElementById("success-message").style.display = "block";
						</script>';
					$query = $con->prepare("SELECT balance FROM member WHERE username = ?;");
					$query->execute(array($_SESSION['username']));
					$balance = (int)$query->fetch()[0];
					if($balance < 0)
						header("Location: ../logout.php");
				}
				else
					echo error_without_field("Please select a book to return");
			}
		?>
		
	</body>
</html>