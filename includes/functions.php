<?php
/* This script creates functions that will be used in divecost website.
The functions will do the following:
- check if the user is logged in */

function loggedin($name = 'user', $value = 'loggedin') {
	
	// Check for the cookie and check its value:
	if (isset($_COOKIE[$name]) && $_COOKIE[$name] == $value) {
		return true;
	} else {
		return false;
	}
} // End of the function
?>
