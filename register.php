<?php
/* This part of php code will:
- verify the submission form
- insert new user's data into SQL database
- link to the login page */

define('TITLE','Register');
include('templates/header.html');

print '<header><h1>Find your dive center</h1></header>';
print '<div style="margin-top: 1em;">';

// Verify the submission form

// Verify if the form had been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// Connect to the database
	$dbc = mysqli_connect('localhost', 'admin', 'admin', 'dive_cost');
	
	// Set character set
	mysqli_set_charset($dbc, 'utf8');
	
	$problem = FALSE; // Variable checking if there is any problem with the submission form
	
	// Check each field if it had been submitted
	
	if (empty($_POST['first_name'])) { // Email is empty
		$problem = TRUE;
		print '<p class="error">Please enter your first name</p>';
	}
	
	if (empty($_POST['last_name'])) { // Last name is empty
		$problem = TRUE;
		print '<p class="error">Please enter your last name</p>';
	}
	
	// Check email if it has proper email structure (only 1 @)
	if (empty($_POST['email']) || (substr_count($_POST['email'], '@') !== 1)) {
		$problem = TRUE;
		print '<p class="error">Please enter your email correctly</p>';
	} else { // Check if the email already exists in the MySQL database
		
		$query1 = "SELECT first_name FROM users WHERE email = '{$_POST['email']}'";
			
		if ($result = mysqli_query($dbc,$query1)) { // Run the query
			
			$row = mysqli_fetch_array($result); // Retrieve the information
			
			if (!empty($row)) { // Check if there is a result to the query - if so, it means the email already exists in the database
				$problem = TRUE;
				print '<p class="error">This email already exists in the database</p>';
			}
		
		} else{ // an error occured
		
		print '<p class="error">The query could not be run because: ' . mysqli_error($dbc) . '</p>';
					
		} // End of query IF
	} // End of Email verification IF
	
	if (empty($_POST['password1'])) { // Password is empty
		$problem = TRUE;
		print '<p class="error">Please enter a password</p>';
	}
	
	// Check if the passwords match
	
	if ($_POST['password1'] !== $_POST['password2']) { // Passwords do not match
		$problem = TRUE;
		print '<p class="error">Your confirmed password does not match the original password</p>';
	}
	
	if (!$problem) { // The form had been filled in correctly and the user does not exist in the database already
		
		// Prepare the values for storing
		
		$first_name = mysqli_real_escape_string($dbc,trim(strip_tags($_POST['first_name'])));
		$last_name = mysqli_real_escape_string($dbc,trim(strip_tags($_POST['last_name'])));
		$email = mysqli_real_escape_string($dbc,trim(strip_tags($_POST['email'])));
		$password = password_hash(mysqli_real_escape_string($dbc,trim(strip_tags($_POST['password1']))), PASSWORD_DEFAULT);
		
		$query2 = "INSERT INTO users (first_name, last_name, email, password) VALUES ('$first_name', '$last_name', '$email', '$password')"; // Inserts the data from the form into the MySQL database
		
		if ($result = mysqli_query($dbc, $query2)) { // Run the query - insert the form data into SQL database
		
			print '<p style="text-align:center;">You have been registered!</p>
			<p style="text-align:center;">Go ahead and log in <a href="login.php">here</a></p>';
		
			// Clear the posted values
			$_POST = [];
		
		} else { // There was a problem
		
			print '<p class="error">Could not register you because: <br> ' . mysqli_error($dbc) . '</p>';
				
		}
	}	else { ?>
	
	<form action="register.php" method="post">
		<table class="centered">
			<caption style="font-weight: bold;">Registration form:</caption>
			<tr>
				<td>First Name:</td><td><input type="text" name="first_name" size="20" value="<?php if(isset($_POST['first_name'])) { print htmlspecialchars($_POST['first_name']);}?>"/></td>
			</tr>
			<tr>
				<td>Last Name:</td><td><input type="text" name="last_name" size="20" value="<?php if(isset($_POST['last_name'])) {print htmlspecialchars($_POST['last_name']);}?>"/></td>
			</tr>
			<tr>
				<td>Email:</td><td><input type="email" name="email" size="20" value="<?php if(isset($_POST['email'])) {print htmlspecialchars($_POST['email']);}?>"/></td>
			</tr>
			<tr>
				<td>Password:</td><td><input type="password" name="password1" size="20"/></td>
			</tr>
			<tr>
				<td style="width:130px">Confirm password:</td><td><input type="password" name="password2" size="20"/></td>
			</tr>
			<tr>
				<td><input type="submit" name="submit" value="Register!"/></td>
			</tr>
		</table>
	</form>
	<?php

	}
	mysqli_close($dbc);

} else { ?>
	
	<form action="register.php" method="post">
		<table class="centered">
			<caption style="font-weight: bold;">Registration form:</caption>
			<tr>
				<td>First Name:</td><td><input type="text" name="first_name" size="20" value="<?php if(isset($_POST['first_name'])) { print htmlspecialchars($_POST['first_name']);}?>"/></td>
			</tr>
			<tr>
				<td>Last Name:</td><td><input type="text" name="last_name" size="20" value="<?php if(isset($_POST['last_name'])) {print htmlspecialchars($_POST['last_name']);}?>"/></td>
			</tr>
			<tr>
				<td>Email:</td><td><input type="email" name="email" size="20" value="<?php if(isset($_POST['email'])) {print htmlspecialchars($_POST['email']);}?>"/></td>
			</tr>
			<tr>
				<td>Password:</td><td><input type="password" name="password1" size="20"/></td>
			</tr>
			<tr>
				<td style="width:130px">Confirm password:</td><td><input type="password" name="password2" size="20"/></td>
			</tr>
			<tr>
				<td><input type="submit" name="submit" value="Register!"/></td>
			</tr>
		</table>
	</form>
<?php
} // End the submission IF / else?>
</div> <!-- Ends the form division -->
<div style="text-align: center;">
<h4>If you are already registered, log in <a href="login.php" style="text-decoration: underline">here</a></h4>

</div>

<?php // Include the footer 
include('templates/footer.html');
?>

	
