
<!DOCTYPE html>
<html>
<head>
<style>
table, th, td {
    border: 1px solid black;
}
</style>
</head>
<body>
<!DOCTYPE HTML>
<html>
<head>
<title>Ofir & Yael</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/bootstrap.css" rel='stylesheet' type='text/css' />
<!-- Custom Theme files -->
<link href="css/resultsstyle.css" rel='stylesheet' type='text/css' />	
<link href="css/jquery.bootstrap-touchspin.css" rel="stylesheet" type="text/css" media="all">
<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/jquery.bootstrap-touchspin.js"></script>
<!--webfonts-->
  <link href='http://fonts.googleapis.com/css?family=Lora:400,400italic|Niconne' rel='stylesheet' type='text/css'>
  <link href='https://fonts.googleapis.com/earlyaccess/opensanshebrewcondensed.css' rel='stylesheet' type='text/css'>
<!--//webfonts-->
</head>
<body>

<div class="banner">
	<div class="container">
		<li>
			<div class="banner-info">
				<h3>Ofir <lable>&</lable> Yael</h3>
				<p>24/10/2017</p>
			</div>
		</li>
	</div>
</div>
<!--//banner-->
	<!--/welcome-->
	<div class="welcome-section">
		<div class="container">
			<?php
				$servername = "localhost";
				$username = "root";
				$password = "a";
				$dbname = "weddings";

				// Create connection
				$conn = new mysqli($servername, $username, $password, $dbname);
				// Check connection
				if ($conn->connect_error) {
				    die("Connection failed: " . $conn->connect_error);
				} 

				$sql = "SELECT id, name, is_confirmed, actual_amount FROM ofiryael";
				$result = $conn->query($sql);

				if ($result->num_rows > 0) {
				    echo "<table><tr><th>כמות מגיעים</th><th>?האם אישרו הגעה</th><th>שם</th><th>מספר</th></tr>";
				    // output data of each row
				    while($row = $result->fetch_assoc()) {
				    	$is_confirmed = $row["is_confirmed"];
				    	echo "";
				    	if($is_confirmed == 1) {
				    		$message = "מגיעים";
				    		$color = "green";
				    	}
				    	elseif ($is_confirmed == NULL) {
							$message = "לא נקלטה בחירה";
				    		$color = "yellow";
				    	}
				    	else {
				    		$message = "לא מגיעים";
				    		$color = "red";

				    	}
				        echo "<tr><td>" . $row["actual_amount"]. "</td><td style='background-color: $color'>$message</td><td>". $row["name"]."</td><td>". $row["id"]."</td></tr>";
				    }
				    echo "</table>";
				} else {
				    echo "0 results";
				}

				$conn->close();
				?> 

					
	</div>
</body>
</html>

