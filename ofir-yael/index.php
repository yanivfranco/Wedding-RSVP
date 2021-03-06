<?php
	ini_set('display_errors', 'On');
	error_reporting(E_ALL | E_STRICT);
	session_start();
	
	if(isset($_GET['token']))
	{
	    $token = $_GET['token'];
	    if($token != "abcd1234ABCD1234"){
			header('HTTP/1.0 403 Forbidden');
			echo 'Forbidden!';
			exit;	    }
	}
	else{
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		exit;
	}
	
	$_SESSION["host"] = "localhost";
	$_SESSION["username"] = "root";
	$_SESSION["password"] = "a";
	$_SESSION["db_name"] = "weddings";
	$_SESSION["table_name"] = "ofiryael";

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
	}?>

<!-- calculatig SUMS -->
<?php 
	$unknown = 0;
	$coming = 0;
	$not_coming = 0;
	$total_guests = 0;
	// Create connection
	$conn = new mysqli($_SESSION["host"],$_SESSION["username"],$_SESSION["password"],$_SESSION["db_name"]);
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
	$sql = "SELECT is_confirmed,actual_amount FROM ofiryael";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
	    	if($row["is_confirmed"] == NULL){
	    		$unknown = $unknown + 1;
	    	}
	    	elseif($row["is_confirmed"] == 0){
	    		$not_coming = $not_coming + 1;
	    	}
			elseif($row["is_confirmed"] == 1){
	    		$coming = $coming + 1;
	    		$total_guests = $total_guests + $row["actual_amount"];
	    	}
	    }
	}
?>
<!DOCTYPE HTML>
<html dir="rtl">
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
			</div>
		</li>
	</div>
</div>
<!--//banner-->
	<!--/welcome-->
	<div class="welcome-section">
		<div class="container">

			<!-- exel file handeling form-->
			<span class="xlupload">
				<form action="" method="post" enctype="multipart/form-data" onsubmit="return confirm('אתה בטוח שאתה מעוניין להעלות קובץ חדש? אופציה זו תמחק את כל המידע ששמור עד כה')">
				  בחר קובץ אורחים: <input style="display:inline; margin: 20px;" type="file" name="file" id="file"/>
				  <input type="submit" name="file_submit" value="לחץ כאן להעלת הקובץ"/>
				</form>

				<!-- php for exel handeling -->
				<?php
					if(isset($_POST['file_submit'])){ //check if form was submitted
						$filename = $_FILES['file']['name'];
						$allowed =  array('csv');
						$ext = pathinfo($filename, PATHINFO_EXTENSION);
						$date = date("d_m_Y_hisa");
						if(!in_array($ext,$allowed) ) {//checks if file is csv
						    echo "<span style='font-size: 100%; color: red; margin: 0;'>שגיאה! הכנס רק קבצים עם סיומת CSV</span>";
						}
						else{//if its a valid csv file
							$table_name = $_SESSION["table_name"];
							//backup current table
							$query = "CREATE TABLE backup.$date LIKE weddings.$table_name";
							$result = sqlNoResult($query);

							$query = "INSERT INTO backup.$date SELECT *  FROM $table_name";
							$result = sqlNoResult($query);

							//drops last table
							$query = "DROP TABLE $table_name;";
							$result = sqlNoResult($query);
						
							//creates a table in the database
							$query = "CREATE TABLE $table_name (
							id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
							name VARCHAR(100) NOT NULL,
							phone VARCHAR(50),
							token VARCHAR(10),
							is_confirmed INT(1),
							actual_amount INT(2),
							msg_id INT(20)
							)";
							$result = sqlNoResult($query);
							if($result == 1) echo "<span style='font-size: 100%; color: red; margin: 0;'>טבלה הוכנסה בהצלחה</span><br>";
							else echo "<span style='font-size: 100%; color: red; margin: 0;'>תקלה בהכנת הטבלה $result</span><br>";

							//inserting the csv information into database
							$filepath = $_FILES["file"]["tmp_name"];
							$file = fopen($filepath,"r");
							$line = fgetcsv($file);
							$success = 0 ; $error = NULL; $error_counter = 0;
							 while(! feof($file)){
							 	$id = $line[0]; $name = $line[1]; $phone = $line[2]; $token = generateRandomString();

							 	//fixes phone numer
							 	if($phone[0] == '0'){
							 		$phone = substr($phone,1);
							 	}
							 	if($phone[0] != '+'){
							 		$phone = "+972".$phone;
							 	}		

							 	//inserting to table					 	
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
							 echo "<span style='font-size: 100%; color: red; margin: 0;'> $success/$id successful sql inserts<br>$error_counter errors, last one is: $error</span>";
						}
					}

				?>
			</span>




			<!-- SMS sending -->


			<span>
				<form action="" method="post" enctype="multipart/form-data" onsubmit="return confirm('הודעות ישלחו לכל האורחים בטבלה. האם אתה בטוח?')">
					כתוב כאן את ההודעה שתבוא אחרי השם ולפני הלינק: <input style="display:inline; width: 250px; height: 150px"type="text" name="message"><br>
					בחר טלפון ממנו לשלוח: <select name="sending_phone"> 

					<!-- fetching devices info and inserting to options -->
					<?php
						$result = $smsGateway->getDevices(1);
						$result = $result['response']['result']['data'];
						foreach ($result as $data) {
						 	echo "<option value=".$data['id'].">".$data['name']." ".$data['make']. " ".$data['model']."</option>";
						 } 
					?>
					</select>
					<input type="submit" name="sms_submit" value="לחץ כאן לשליחת ההודעות"/>
				</form>

			<!-- php for sms sending -->
				<?php
					if(isset($_POST['sms_submit'])){ //check if form was submitted
						$table_name = $_SESSION["table_name"];
						$sending_phone = $_POST['sending_phone'];
						$message = $_POST['message'];
						$result = sqlResult("SELECT id, name, phone, token FROM $table_name");

						//logs vars
						$failed = 0;
						$success = 0;

						//iterate over the guests, building links and personal messages and sends sms.
						if(mysqli_num_rows($result) > 0){
							while($row = mysqli_fetch_array($result)){
								$id = $row["id"]; $name = $row["name"]; $phone = $row["phone"]; $token = $row["token"]; //getting guest info
								$link="http://". $_SERVER['SERVER_NAME'] ."/ofir-yael/guest.php?id=$id&token=$token";
								$complete_message = "שלום $name,\n$message, \n$link";
								//sends sms
								$sms_result = $smsGateway->sendMessageToNumber($phone, $complete_message, $sending_phone);



								//LOGS 
								if(  count($sms_result['response']['result']['fails']) > 0){
									$data = "	* נכשל * מסםר: $id, שם: $name, טלפון: $phone \n";
									echo $data;
									$failed++;
								}
								if(  count($sms_result['response']['result']['success']) > 0){
									$msg_id = $sms_result['response']['result']['success'][0]["id"];
								 	//inserting to table		
									$query = "UPDATE ofiryael
											SET msg_id = $msg_id
											WHERE id = $id;";
									$tempres = sqlNoResult($query);
									//checking sql results and counting
									if($tempres != 1){
										$error = $tempres;
										echo $error;
									}
								}
							}
							echo "<span style='font-size: 100%; color: red; margin: 0;'> ההודעות נשלחו בהצלחה!!! <br> נכשלו $failed</span>";
						}
					}
				?>
			</span>


			<div class="numbers">
				<span class="unknown"><?php echo $unknown;?></span><span class="not_coming"><?php echo $not_coming;?></span><span class="coming"><?php echo $coming;?></span>
			</div>
			<div class="is_coming">
				<span>מגיעים</span><span>לא מגיעים</span><span>לא ענו</span>
			</div>
			<div class="is_coming">
				<span>סך הכל אורחים:</span><span><?php echo $total_guests;?></span>
			</div>
			<!-- table -->
			<?php

				$sql = "SELECT id, name, is_confirmed, actual_amount, msg_id FROM ofiryael";
				$result = $conn->query($sql);

				if ($result->num_rows > 0) {
				    echo "<table><tr><th>מספר</th><th>שם</th><th>?האם אישרו הגעה</th><th>מצב ההודעה</th><th>כמות מגיעים</th></tr>";
				    // output data of each row
				    while($row = $result->fetch_assoc()) {
				    	$is_confirmed = $row["is_confirmed"];
				    	$msg_id = $row["msg_id"];
				    	$sms_status = "";
				    	$sms_color = "";

				    	//checking sms status
				    	if($msg_id != NULL){
				    		$sms_result = $smsGateway->getMessage($msg_id);
				    		$sms_status = $sms_result['response']['result']['status'];
				    		if($sms_status == "failed"){
				    			$sms_color = "red"; 
				    		}
				    		elseif ($sms_status == "sent"){
				    			$sms_color = "green"; 
				    		}
				    		elseif ($sms_status == "pending"){
				    			$sms_color = "yellow"; 
				    		}
				    	}


				    	//checking if guests confirmed
				    	echo "";
				    	if($is_confirmed == 1) {
				    		$message = "מגיעים";
				    		$confirmed_color = "green";
				    	}
				    	elseif ($is_confirmed == NULL) {
							$message = "לא נקלטה בחירה";
				    		$confirmed_color = "orange";
				    	}
				    	else {
				    		$message = "לא מגיעים";
				    		$confirmed_color = "red";

				    	}

				        echo "<tr><td>". $row["id"]."</td> <td>". $row["name"]."</td><td style='color: $confirmed_color'>$message</td><td style='background-color: $sms_color'>". $sms_status ."</td><td>" . $row["actual_amount"]. "</td></tr>";
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

