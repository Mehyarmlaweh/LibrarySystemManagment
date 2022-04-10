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
		<link rel="stylesheet" type="text/css" href="css/home_style.css">
		<link rel="stylesheet" type="text/css" href="../css/custom_radio_button_style.css">
	</head>
	<body>
		<?php
			$query = $con->prepare("SELECT * FROM book ORDER BY title");
				$query2 = $con->prepare("SELECT count(*) FROM book ");
				$query2->execute();
				$rows= $query2->fetchColumn();
			$query->execute();

			if($rows == 0)
			echo "<h2 align='center'>No books available</h2>";
		else {
		
				echo "<form class='cd-form' method='POST' action='#'>";
				echo "<center><legend>List of Available Books</legend></center>";
				echo "<div class='error-message' id='error-message'>
						<p id='error'></p>
					</div>";
				echo "<table width='100%' cellpadding=10 cellspacing=10>";
				echo "<tr>
						<th></th>
						<th>ISBN<hr></th>
						<th>Book Title<hr></th>
						<th>Author<hr></th>
						<th>Category<hr></th>
						<th>Price<hr></th>
						<th>Copies<hr></th>
					</tr>";
					while($row= $query->fetch())
			
				{
					
					echo "<tr>
							<td>
								<label class='control control--radio'>
									<input type='radio' name='rd_book' value=".$row[0]." />
								<div class='control__indicator'></div>
							</td>";
					for($j=0; $j<6; $j++)
						if($j == 4)
							echo "<td>Rs.".$row[$j]."</td>";
						else
							echo "<td>".$row[$j]."</td>";
					echo "</tr>";
				}
				echo "</table>";
				echo "<br /><br /><input type='submit' name='m_request' value='Request Book' />";
				echo "</form>";
			
		}
			
			if(isset($_POST['m_request']))
			{
				if(empty($_POST['rd_book']))
					echo error_without_field("Please select a book to issue");
				else
				{
					$query = $con->prepare("SELECT copies FROM book WHERE isbn = ?;");
					$query->execute(array($_POST['rd_book']));
					$copies = $query->fetch()['copies'];
					if($copies == 0)
						echo error_without_field("No copies of the selected book are available");
					else
					{
						$query = $con->prepare("SELECT count(request_id) FROM pending_book_requests WHERE member = ?;");
						$query->execute (array($_SESSION['username']));
						if($query->fetchColumn()!=0){
						echo error_without_field("You can only request one book at a time");}
						else
						{
							$query = $con->prepare("SELECT book_isbn FROM book_issue_log WHERE member = ?;");
							$query2= $con->prepare("Select count(book_isbn) FROM book_issue_log WHERE member = ?;");
							$query->execute(array($_SESSION['username']));
									$query2->execute(array( $_SESSION['username']));
									$rows=$query2->fetchColumn();
							$result = $query->fetch();
							if($rows>= 3)
								echo error_without_field("You cannot issue more than 3 books at a time");
							else
							{
								for($i=0; $i<$rows; $i++)
									if(strcmp($result[0], $_POST['rd_book']) == 0)
										break;
								if($i < $rows)
									echo error_without_field("You have already issued a copy of this book");
								else
								{
									$query = $con->prepare("SELECT balance FROM member WHERE username = ?;");
									$query->execute(array($_SESSION['username']));
									$memberBalance = $query->fetch()[0];
									$query = $con->prepare("SELECT price FROM book WHERE isbn = ?;");
									$query->execute(array($_POST['rd_book']));
									$bookPrice = $query->fetch()[0];
									if($memberBalance < $bookPrice)
										echo error_without_field("You do not have sufficient balance to issue this book");
									else
									{
										$query = $con->prepare("INSERT INTO pending_book_requests(member, book_isbn) VALUES(?, ?);");
										try {$query->execute(array( $_SESSION['username'], $_POST['rd_book']));
							echo success("Selected book has been requested. Soon you'll' be notified when the book is issued to your account!");
										} catch(Exception $e){
											echo error_without_field("ERROR: Couldn\'t request book");
										}
											
									}
								}
							}
						}
					}
				}
			}
		?>
	</body>
</html>