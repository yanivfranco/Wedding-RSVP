<?php 
	//debug
	ini_set('display_errors', 'On');
	error_reporting(E_ALL | E_STRICT);
	session_start();
	//functions
	function sqlResult($query){
		$conn = new mysqli("localhost","root","a","weddings");
		return mysqli_query($conn, $query);

	}
	if ((isset($_GET['id'])) && (isset($_GET['token']))){
		$id = $_GET['id']; $token = $_GET['token'];
		$_SESSION['id'] = $_GET['id'];
		$_SESSION['token'] = $_GET['token'];
		$query = "SELECT token, name, actual_amount FROM ofiryael WHERE id=$id;";
		$result = sqlResult($query);
		//checking tokens
		if(mysqli_num_rows($result) > 0){
			$row = mysqli_fetch_array($result);
			$db_token = $row['token']; $actual_amount = $row['actual_amount'] ; $name = $row['name'];
			if($token != $db_token) { //if token not the same
				header('HTTP/1.0 403 Forbidden');
				echo 'Forbidden!';
				exit;
			}
			
		}
		else{//id not found
			header('HTTP/1.0 403 Forbidden');
			echo 'Forbidden!';
			exit;
		}
	}
	else{//if no parameters token and id
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		exit;
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
<link href="css/style.css" rel='stylesheet' type='text/css' />	
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
		   <h2>שלום <?php echo "$name" ;?></h2>
		   <p>אופיר ויעל מתרגשים להזמינכם לטקס הנישואים שיתקיים ביום שלישי ה-</p>
		   <p2> 19:00 24/10/2017 </p2><br>
		   <p1> באקו גן אירועים - חדרה</p1><br><br>
		   <p2>:כמות האורחים שמגיעים</p2>
			<div class="input-group input-group-lg" style="direction: ltr;font-weight:bold;font-size:23px;width: 150px; margin: 5px auto;">
				<form action="" method="post">
				<input id="numOfGuests" type="text" value="<?php echo "$actual_amount"; ?>" name="numOfGuests" class="form-control input-lg" style="background-color: #ebe3de; text-align: center;" 		data-bts-step-interval-delay="500",
						data-bts-min="0"
				       data-bts-max="20"
					    data-bts-booster="false"
						data-bts-mousewheel="false"
					   data-bts-step-interval-delay="500"
					   data-bts-init-val="2"
					   >
			</div>
			<script>
			    $("input[name='numOfGuests']").TouchSpin({
			        postfix: "",
			        postfix_extraclass: "",
			    });
			</script>
			
             <input type="submit" name="no" class="button-form" id="no" style="background: transparent;border: none;background-image: url(images/x-mark.png);background-size: 100%; height: 50px; width: 50px; margin-left: 50px" value='' />
             <input type="submit" name="yes" class="button-form" id="yes" style="background: transparent;border: none;background-image: url(images/check-mark.png);background-size: 100%; height: 50px; width: 50px ;margin-right: 50px" value='' /><br>
             <span class="attend" style="margin-left: 50px">לא מגיע/ה</span><span class="attend" style="margin-right: 50px">מגיע/ה</span>
			</form>
	</div>
</body>
</html>

<?php
	if(isset($_POST['no'])){
		$query = "UPDATE ofiryael
				SET is_confirmed = 0
				WHERE id = $id;";
		$result = sqlResult($query);
		header('Location: thankyou1.php');

	}
	if(isset($_POST['yes'])){
		$actual_amount = $_POST['numOfGuests'];
		$query = "UPDATE ofiryael
				SET is_confirmed = 1, actual_amount= $actual_amount
				WHERE id = $id;";
		$result = sqlResult($query);
		header('Location: thankyou.php');

	}


?>