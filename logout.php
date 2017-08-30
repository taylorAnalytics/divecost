<?php
/* This script will:
- delete the cookie for the logged in user
- show the logout page with the link to log in page again*/

session_start();
define('TITLE', 'Logout');
include('templates/header.html');

session_destroy();

setcookie ('user', FALSE, time()-6000);

?> <!-- End the php part, show the logout information and the link to log-in page-->

<header><h1>Find your dive center</h1></header>

<div style="text-align: center; margin-top: 1em;">

<h3>You have logged out - thank you for your visit</h3>
<h4>If you want, you can log back in <a href="login.php" style="text-decoration: underline">here</a></h4>

</div>


<?php
// Include the footer

include('templates/footer.html');
?>

