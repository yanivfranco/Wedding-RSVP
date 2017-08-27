
<?php
	//auth
	if($_GET['token'] != 'fa1g50') { //if token not the same
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		exit;
	}
	//debug
	ini_set('display_errors', 'On');
	error_reporting(E_ALL | E_STRICT);
	session_start();
	//sms gateway login
	include "smsGateway.php";
	$smsGateway = new SmsGateway('yanivfranco@gmail.com', 'smsgatewayfa1g50');
	$device_id = 57323;
	//functions
	function sqlNoResult($query){
		$conn = new mysqli($_SESSION["host"],$_SESSION["username"],$_SESSION["password"],$_SESSION["db_name"]);
		if ($conn->query($query) === TRUE) {
			    return 1;
		} else {
			   return $conn->error;
		}
	}
	function sqlResult($query){
		$conn = new mysqli($_SESSION["host"],$_SESSION["username"],$_SESSION["password"],$_SESSION["db_name"]);
		return mysqli_query($conn, $query);

	}
	function generateRandomString($length = 8) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
?>
<html>
<head>
<title>Wedding SMS</title>
<link href="style.css" rel='stylesheet' type='text/css' />
<link href='http://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>	
</head>
<body>
<!-- MySQL connection and setup -->
<div class="db_connect">
	<h>MySQL Setup</h><br>
	<p><?php if(isset($_SESSION["host"])) echo "<span style='font-size: 70%; color: red; margin: 0;'>There are saved connection details: ".$_SESSION["host"]." ".$_SESSION["username"]." ".$_SESSION["password"]." ".$_SESSION["db_name"]."</span>";?></p>
	<form action="" method="post" enctype="multipart/form-data">
		  MySQL host: <input type="text" name="host" id="host"/><br>
		  MySQL username: <input type="text" name="username" id="username"/><br>
		  MySQL password: <input type="text" name="password" id="password"/><br>
		  MySQL database name: <input type="text" name="db_name" id="db_name"/><br>
		  <input type="submit" name="db_connect" value="Store connection details"/>
	</form>
	<?php
		if(isset($_POST['db_connect'])){ //check if form was submitted
			$_SESSION["host"] = $_POST['host'];
			$_SESSION["username"] = $_POST['username'];
			$_SESSION["password"] = $_POST['password'];
			$_SESSION["db_name"] = $_POST['db_name'];
			echo "<span style='font-size: 70%; color: red; margin: 0;'>Details stored</span>";
		}

	?>
	<!-- create database after connecting -->
	<form action="" method="post" enctype="multipart/form-data">
			Create new database:  <input type="text" name="db_name" id="db_name"/><br>
		  <input type="submit" name="create_db" value="Create database"/>
	</form>
	<?php
		if(isset($_POST['create_db'])){
			$db_name = $_POST['db_name'];
			$query = "CREATE DATABASE " . $db_name;
			$result = sqlNoResult($query);
			if($result == TRUE) echo "<span style='font-size: 70%; color: red; margin: 0;'>Create " .$db_name .  " database succeded</span>";
			else echo "<span style='font-size: 70%; color: red; margin: 0;'>Create table failed: " . $conn->error."</span>";
		}
	?>
	<!-- drop table -->
		<form action="" method="post" enctype="multipart/form-data">
			Drop Table:  <input type="text" name="drop_name" id="drop_name"/><br>
		  <input type="submit" name="drop" value="Drop"/>
	</form>
	<?php
		if(isset($_POST['drop'])){
			$drop_name = $_POST['drop_name'];
			$query = "DROP TABLE $drop_name;";
			$result = sqlNoResult($query);
			if($result == TRUE) echo "<span style='font-size: 70%; color: red; margin: 0;'>Drop table " .$drop_name .  " succeded</span>";
			else echo "<span style='font-size: 70%; color: red; margin: 0;'>Drop table failed: " . $conn->error."</span>";
		}
	?>
</div>




<!-- exel file handeling -->
<div class="xlupload">
	<h>CSV Guests file upload to database </h>
	table will be [id,name,phone,token,is_confirmed(1/0), actual_amount] <br>
	CSV file : [id,name,phone]
	<form action="" method="post" enctype="multipart/form-data">
	  Select exel guests file: <input type="file" name="file" id="file"/><br>
	  Choose SQL table name: <input type="text" name="table_name" id="table_name"/><br>
	  <input type="submit" name="file_submit" value="Enter To Database"/>
	</form>

	<!-- php for exel handeling -->
	<?php
		if(isset($_POST['file_submit'])){ //check if form was submitted
			if(ctype_alnum($_POST['table_name'])){ //checks if table name is valid
				$table_name = $_POST['table_name'];
			}
			else{
				echo "<span style='font-size: 70%; color: red; margin: 0;'>Table name is not valid</span>";
				return;
			}
			$filename = $_FILES['file']['name'];
			$allowed =  array('csv');
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if(!in_array($ext,$allowed) ) {//checks if file is csv
			    echo "<span style='font-size: 70%; color: red; margin: 0;'>error - not a CSV file!</span>";
			}
			else{//if its a valid csv file
				//creates a table in the database
				$query = "CREATE TABLE $table_name (
				id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
				name VARCHAR(30) NOT NULL,
				phone VARCHAR(50),
				token VARCHAR(10),
				is_confirmed INT(1),
				actual_amount INT(2)
				)";
				$result = sqlNoResult($query);
				if($result == 1) echo "<span style='font-size: 70%; color: red; margin: 0;'>Table $table_name created successfuly</span><br>";
				else echo "<span style='font-size: 70%; color: red; margin: 0;'>Table $table_name create failed $result</span><br>";

				//inserting the csv information into database
				$filepath = $_FILES["file"]["tmp_name"];
				$file = fopen($filepath,"r");
				$line = fgetcsv($file);
				$success = 0 ; $error = NULL; $error_counter = 0;
				 while(! feof($file)){
				 	$id = $line[0]; $name = $line[1]; $phone = $line[2]; $token = generateRandomString();
				 	$query = "INSERT INTO $table_name (id, name, phone,token,actual_amount)
					VALUES ($id,'$name',$phone,'$token', 1)";
					$result = sqlNoResult($query);
					//checking sql results and counting
					if($result == 1) $success = $success + 1;
					else {
						$error_counter = $error_counter+1;
						$error = $result;
					}		
					$line = fgetcsv($file);		  
				}
				 echo "<span style='font-size: 70%; color: red; margin: 0;'> $success/$id successful sql inserts<br>$error_counter errors, last one is: $error</span>";
			}
		}

	?>
</div>

<div>
	<h>SMS to guests</h>
	<form action="" method="post" enctype="multipart/form-data">
		Index page url: <input type="text" name="index_page"><br>
		Table name in database: <input type="text" name="table_name"><br>
		Messege before the link and after "hello name,": <input type="text" name="message" style="width: 250px; height: 150px"><br>
		<input type="submit" name="sms_submit" value="Generate links & Send SMS"/>
	</form>

<!-- php for sms sending -->
	<?php
		if(isset($_POST['sms_submit'])){ //check if form was submitted
			$index_page = $_POST['index_page'];
			$message = $_POST['message'];
			$table_name = $_POST['table_name'];
			$result = sqlResult("SELECT id, name, phone, token FROM $table_name");
			//iterate over the guests, building links and personal messages and sends sms.
			if(mysqli_num_rows($result) > 0){
				while($row = mysqli_fetch_array($result)){
					$id = $row["id"]; $name = $row["name"]; $phone = $row["phone"]; $token = $row["token"]; //getting guest info
					$link="$index_page/?id=$id&token=$token";
					$complete_message = "שלום $name,\n$message, \n$link";
					//sends sms
					$smsGateway->sendMessageToNumber($phone, $complete_message, $device_id);

				}
			}
		}
	?>

</div>
</body>
</html>