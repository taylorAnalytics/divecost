<?php
/* This script will display a header, a welcome sentence and two buttons:
- login - linking to login page,
- register - linking to register page */

define ('TITLE', 'Dive Cost Website');
include ('templates/header.html');
?>

<header><h1>Find your dive center</h1></header>
<div style="margin-top:1em">
<h2 style="text-align: center">Welcome to the Dive Cost Website.<br>
You can find the best and the cheapest dive center anywhere in the world.</h2>
<h3 style="text-align: center">Log in or register and start looking for your dive center</h3>
<p style="text-align: center;"><a class="button" href="login.php" style="margin:0 auto">Login</a>
<a class="button" href="register.php" style="margin:0 auto">Register</a></p>

</div>

<?php
// include the footer
include('templates/footer.html');
?>