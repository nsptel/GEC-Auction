<?php 

	session_start();
	$_SESSION["fromHomepage"] = "false";
	$_SESSION["fromIndex"] = "false";
	
	$error = "";
	$msg = "";

	if(isset($_POST["signout"])) {
		if(isset($_COOKIE["remember"])) {
			setcookie("remember", "set", time() - (24*60*60*91), "/");
			header("Location: ".$_SERVER['PHP_SELF']);
			die;
		}
	}

	if(!isset($_COOKIE["remember"])) { 

		if(array_key_exists("submit", $_POST)) {
			$link = mysqli_connect("localhost", "root", "", "auction");
			if(mysqli_connect_error()) {
				die("There was an error connecting to database.");
			}

			if(!$_POST["email"] || !$_POST["password"]) {
				$error  .= "Please enter all details carefully.</br>";
			} else if(!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
				$error .= "Entered Email id is not valid.</br>";
			}

			if($error != "") {
				$error = "<strong>There were error(s) in your form :</strong></br>".$error;
			} else {

				if($_POST["signup"] == '1') {

					$query = "SELECT `id` FROM `users` WHERE `email` = '".mysqli_real_escape_string($link, $_POST["email"])."' LIMIT 1";
					$query1 = "SELECT `id` FROM `temp-data` WHERE `email` = '".mysqli_real_escape_string($link, $_POST["email"])."' LIMIT 1";
					$result = mysqli_query($link, $query);
					$result1 = mysqli_query($link, $query1);

					if(mysqli_num_rows($result) > 0 || mysqli_num_rows($result1) > 0) {
						$error = "Entered email id is taken OR it is yet to be verified.";
					} else {

						if(!mysqli_query($link, $query)) {
							$error = "Could not Sign up. Please try again later.";
						} else {

							$to = $_POST["email"];
							$subject = "test!";
							$body = "here is the link...oooo... disappeared.!";
							
							if(send($to, $subject, $body)) {
								//$query = "INSERT INTO `temp-data` (`email`, `password`, `en-email`) VALUES ('".mysqli_real_escape_string($link, $_POST["email"])."', '".mysqli_real_escape_string($link, $_POST["password"])."', '".md5($_POST["email"])."')";

								$msg = "An email containing a link has been sent to given email id.Please check for the email and visit the link given in the email to verify your account.";

							} else {
								$error .= "</br>Error sending an E-mail.</br>";
							}

						}

					}

				} else {

					$query = "SELECT * FROM `users` WHERE `email` = '".mysqli_real_escape_string($link, $_POST["email"])."' LIMIT 1";
					$result = mysqli_query($link, $query);
					$row = mysqli_fetch_array($result);

					if(isset($row)) {
						$hashpass = md5(md5($row["id"]).$_POST["password"]);

						$query1 = "SELECT * FROM `random-string` WHERE `id` = '".mt_rand(1,50)."' LIMIT 1";

						$row1 = mysqli_fetch_array(mysqli_query($link, $query1));

						if($hashpass == $row["password"]) {

							
							$username = explode("@", $_POST["email"]);

							$_SESSION["username"] = $username[0];

							if(isset($_POST["check"])) {
								setcookie("remember", "set", time() + (24*60*60*90), "/");
								setcookie("username", $username[0], time() + (24*60*60*90), "/");
							}
							
							$_SESSION["fromIndex"] = "true";

							header("location: homepage.php?r=".$row1["random"]);
						} else {
							$error = "Incorrect password.";
						}
					} else {
						$error = "That email/password combination could not be found.";
					}

				}

			}
		
		}

	} else {

		$link = mysqli_connect("localhost", "root", "", "auction");
		if(mysqli_connect_error()) {
			die("There was an error connecting to database.");
		}		

		$_SESSION["fromIndex"] = "true";
		$query = "SELECT * FROM `random-string` WHERE `id` = '".mt_rand(1,30)."' LIMIT 1";
		$row = mysqli_fetch_array(mysqli_query($link, $query));

		header("location: homepage.php?r=".$row["random"]);

	}

?>

<?php	

/*__________________________________________________________________________________
					THIS DOESN'T WORK
__________________________________________________________________________________*/

	$message = "";

	function send($to, $subject, $body) {

		$uname = "nsp4898@gmail.com";
		$passw = "n4e8e9l8";

		require_once('mail_class/class.phpmailer.php');
		include("mail_class/class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

		$mail             = new PHPMailer();

		$body             = $body;

		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->Host       = "smtp.gmail.com"; // SMTP server
		$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
		                                           // 1 = errors and messages
		                                           // 2 = messages only

		$mail->SMTPOptions = array (
			'tls' => array (
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);


		$mail->SMTPAuth   = true; 
		$mail->SMTPSecure = 'tls';                 // enable SMTP authentication
		$mail->Host       = "smtp.gmail.com"; // sets the SMTP server
		$mail->Port       = 587;                    // set the SMTP port for the GMAIL server
		$mail->Username   = $uname; // SMTP account username
		$mail->Password   = $passw;        // SMTP account password

		$mail->SetFrom('nsp4898@gmail.com', 'Neel');

		$mail->AddReplyTo("nsp4898@gmail.com","Neel Patel");

		$mail->Subject    = $subject;

		//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

		$mail->MsgHTML($body);

		$address = $to;
		$mail->AddAddress($address, "By Neel");

		//$mail->AddAttachment("images/phpmailer.gif");      // attachment
		//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
		
	}	

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>GEC-Auction</title>
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="css/font-awesome.css">
	<script type="text/javascript" src="jQuery.js"></script>
	<script type="text/javascript" src="main.js"></script>
</head>
<body>

	<div class="topbar">
		<h1 id="heading">Gec-Auction</h1>
	</div>

	<div class="background" id="bgimage">
		<div class="content-bg">
			<p id="intro">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dapibus sem elit. Vestibulum ante tellus, semper eu lobortis a, congue maximus odio. Aliquam accumsan ac tortor at venenatis. Donec ultrices dignissim lectus, et condimentum enim convallis finibus. Pellentesque ut nunc feugiat, tincidunt libero at, lobortis leo. Mauris a mauris pulvinar, vehicula ante vel, rhoncus ipsum.</p>

			<form name="signup" id="signup" method="POST">
				<h3>Like the idea? Sign up now.</h3>

				<input type="text" name="email" id="email" placeholder="Your E-mail"><br>
				<input type="password" name="password" placeholder="Password"><br>
				<input type="hidden" name="signup" value="1">
				<button type="submit" name="submit">Sign Up</button>

				<p>Have an account?	<a class="change">Login</a></p>
			</form>

			<form name="login" id="login" method="POST">
				<h3>Continue with logging in!</h3>

				<input type="text" name="email" id="email" placeholder="Your E-mail"><br>
				<input type="password" name="password" placeholder="Password"><br>
				<input type="checkbox" name="check"><br>(Remember me.)<br>
				<input type="hidden" name="signup" value="0">
				<button type="submit" name="submit">Log In</button>

				<p>New here?	<a class="change">Sign Up</a></p>				
			</form>
		</div>

		<div id="error">
			<?php echo $error; ?>
		</div>
		<div id="good">
			<?php echo $msg; ?>
		</div>
		<?php echo $message; ?>

	</div>

</body>
</html>