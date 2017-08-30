<?php
/* This part of code will:
- display a login form
- verify the login information with the ones in the MySQL database
- create a cookie
- link to the search.php website if the cookie had been created */

define('TITLE', 'Log In');
include('templates/header.html');

print '<header><h1>Find your dive center</h1></header>';

if($_SERVER['REQUEST_METHOD'] == 'POST') { // Check if the form had been submitted
	
	$problem = FALSE;
	$dbc = mysqli_connect('localhost', 'user', 'user', 'dive_cost'); // Connect to the database
	
	
	// Check if the email is not empty
	
	if (empty($_POST['email'])) {
		
		$problem = TRUE;
		print '<p class="error">Please enter your email adress</p>';
		
	} else { // Check if the email exists in the database
	
		$query = "SELECT password FROM users WHERE email = '{$_POST['email']}'";
		
		if ($result = mysqli_query($dbc, $query)) { // Run the query
		
			$r = mysqli_fetch_array($result);
			
			if (empty($r)) { // Email not existing in the database
			
			$problem = TRUE;
			print '<p class="error">This email does not exist in our database</p>
			<p class="error">Insert email correctly or register, if you haven\'t done it yet</p>';
			
			} else { // Email existing, retrieve the password for comparison
			
				if(!password_verify($_POST['password'],$r['password'])) { // Password does not match
				
					$problem = TRUE;
					print '<p class="error">The password is incorect</p>';
				} else { // Password does match
				
					setcookie('user', 'loggedin', time() + 10800);
					
					header("Location: search.php");
					exit();
				} // End of password verification
			
			} // End of email verification with the database
			
		} else { // There was a problem with the query
		
		$problem = TRUE;
		print '<p class="error">Could not retrieve the information, because: <br>' . mysqli_error($dbc) . '.</p>';
		} // End of query IF
	
	} // End of email not empty IF
	
	// Create a cookie
	
} // End of submission IF

?>

<!--Display the form-->
<div style="margin-top: 1em;">
<form action="login.php" method="post">
	<table class="centered">
		<caption style="font-weight:bold;">Log in form</caption>
		<tr>
			<td>Email:</td><td><input type="email" name="email" size="20" value="<?php if(isset($_POST['email'])) {print htmlspecialchars($_POST['email']);}?>"/></td>
		</tr>
		<tr>
			<td>Password:</td><td><input type="password" name="password" size="20"/></td>
		</tr>
		<tr>
			<td><input type="submit" name="submit" value="Log In!"/></td>
		</tr>
	</table>
</form>
</div>
<div style="margin-top: 1em; text-align: center;">
<p>If you don't have an account yet, register <a href="register.php" style="text-decoration: underline">here</a></p>
</div>
<?php
include('templates/footer.html');
?>
