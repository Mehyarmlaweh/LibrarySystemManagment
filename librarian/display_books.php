<?php
    require "../db_connect.php";
    require "../message_display.php";
	require "verify_librarian.php";
	require "header_librarian.php";
?>

<html>
	<head>
		<title>LMS</title>
		<link rel="stylesheet" type="text/css" href="../member/css/home_style.css" />
        <link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/home_style.css">
		<link rel="stylesheet" type="text/css" href="../member/css/custom_radio_button_style.css">
	</head>
	<body>

    <?php
			$query = $con->prepare("SELECT * FROM book ORDER BY title ");
			$query->execute();
			$query2 = $con->prepare("SELECT count(*) FROM book ORDER BY title");
			$query2->execute();
			$rows = $query2->fetchColumn();
			if(!$query->fetch())
				die("ERROR: Couldn't fetch books");
			if($rows == 0)
				echo "<h2 align='center'>No books available</h2>";
			else
			{
				echo "<form class='cd-form'>";
				echo "<div class='error-message' id='error-message'>
						<p id='error'></p>
					</div>";
				echo "<table width='100%' cellpadding=10 cellspacing=10>";
				echo "<tr>
				
						<th>ISBN<hr></th>
						<th>Book Title<hr></th>
						<th>Author<hr></th>
						<th>Category<hr></th>
						<th>Price<hr></th>
                        <th>Copies<hr></th>
					</tr>";
			
								while($result = $query->fetch()){
					echo "<tr>
							";
					for($j=0; $j<6; $j++)
						if($j == 4)
							echo "<td>Rs.".$result[$j]."</td>";
						else
                            echo "<td>".$result[$j]."</td>";
                            
					echo "</tr>";
				}
				echo "</table>";
				
				echo "</form>";
			}
			
			
		?>

    </body>

</html>