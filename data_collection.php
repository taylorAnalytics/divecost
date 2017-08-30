<?php
/* This script is aimed at collecting data of dive centers. It will:
- have a form to collect data
- verify if the data is not already in the database
- insert the data into the database */

define('TITLE', 'Dive cost data collection');
include('templates/header.html');

print '<header><h1>Find your dive center</h1></header>';

// Check for the form submission

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$problem = FALSE;
		
	// Check each field if it's been filled and apply their values to variables (to be later used in a query)
	
	if(empty($_POST['Part_of_the_World'])) {
		print '<p class="error">Fill in Part of the world</p>';
		$problem = TRUE;
	} else {
		$potw = $_POST['Part_of_the_World'];
	}
	
	if(empty($_POST['Country'])) {
		print '<p class="error">Fill in Country</p>';
		$problem = TRUE;
	} else {
		$country = $_POST['Country'];
	}
	
	if(empty($_POST['Region'])) {
		print '<p class="error">Fill in Region</p>';
		$problem = TRUE;
	} else {
		$region = $_POST['Region'];
	}
	
	if(empty($_POST['Location'])) {
		print '<p class="error">Fill in Location</p>';
		$problem = TRUE;
	} else {
		$location = $_POST['Location'];
	}

	if(empty($_POST['Dive_Center_Name'])){
		print '<p class="error">Fill in Dive center name</p>';
		$problem = TRUE;
	} else {
		$dcn = $_POST['Dive_Center_Name'];
	}
	
	if(empty($_POST['Website'])){
		print '<p class="error">Fill in Website</p>';
		$problem = TRUE;
	} else {
		$website = $_POST['Website'];
	}
	
	if(empty($_POST['Email'])){
		print '<p class="error">Fill in Email</p>';
		$problem = TRUE;
	} else {
		$email = $_POST['Email'];
	}
	
	$address = $_POST['Address'];
	$phone = $_POST['Phone_No'];
	
	if(empty($_POST['Currency'])){
		print '<p class="error">Fill in Currency</p>';
		$problem = TRUE;
	} else {
		$currency = $_POST['Currency'];
	}
	
	$OWDprice=intval($_POST['OWD_price']);
	$AOWDprice=intval($_POST['AOWD_price']);
	$DSDprice=intval($_POST['DSD_price']);
	$dives2price=intval($_POST['Price_per_2_dives']);
	$dives10price=intval($_POST['Price_per_10_dives']);
	
	// If there is no problem, insert data into MySQL
	
	if(!$problem){ // The form had been succesfully submitted
		
		$dbc = mysqli_connect('localhost', 'admin', 'admin', 'dive_cost');
		
		// Prepare variables to INSERT
		$potw = mysqli_real_escape_string($dbc,$potw);
		$country = mysqli_real_escape_string($dbc,$country);
		$region = mysqli_real_escape_string($dbc,$region);
		$location = mysqli_real_escape_string($dbc,$location);
		$dcn = mysqli_real_escape_string($dbc,$dcn);
		$website = mysqli_real_escape_string($dbc,$website);
		$email = mysqli_real_escape_string($dbc,$email);
		$address = mysqli_real_escape_string($dbc,$address);
		$phone = mysqli_real_escape_string($dbc,$phone);
		$currency = mysqli_real_escape_string($dbc,$currency);
		
		// Check if this dive site already exists in the database
		$query = "SELECT Dive_Center_Name FROM dive_centers WHERE Dive_Center_Name='$dcn'";
		
		if($result = mysqli_query($dbc, $query)){ // Run the query
			$r = mysqli_fetch_array($result); // Retrieve the information
			if(empty($r)) {
				$new_record = TRUE; // Dive center did not exist yet
			} else {
				$new_record = FALSE; // Dive center already exists
			} // End of "empty" If
		} else {
			print '<p class="error">The query could not run because:' . mysqli_error($dbc) . '.</p>';
		}// End of run the query If
		
		// INSERT data into MySQL
		
		
		// Condition by wether it's an insert or an update
		if($new_record) {// It should be a new record in the database
			
			$query = "INSERT INTO dive_centers (Part_of_the_World, Country, Region, Location, Dive_Center_Name, Website, Email, Address, Phone_No, Currency, DSD_price, OWD_price, AOWD_price, Price_per_2_dives, Price_per_10_dives) 
			VALUES ('$potw', '$country', '$region', '$location', '$dcn', '$website', '$email', '$address', '$phone', '$currency', $DSDprice, $OWDprice, $AOWDprice, $dives2price, $dives10price)";
		
			if(mysqli_query($dbc,$query)){
				print '<p style="text-align:center;">Dive Center has been added to the database</p>';
			} else {
				print '<p class="error">Could not insert data into database because:' . mysqli_error($dbc) . '.</p>';
			}
		} else { // It's an existing record, that needs to be updated
			
			$query = "UPDATE dive_centers SET Part_of_the_World='$potw', Country='$country', Region='$region', Location='$location', Dive_Center_Name='$dcn', Website='$website', Email='$email', Address='$address',
			Phone_No='$phone', Currency='$currency', DSD_price=$DSDprice, OWD_price=$OWDprice, AOWD_price=$AOWDprice, Price_per_2_dives=$dives2price, Price_per_10_dives=$dives10price WHERE Dive_Center_Name='$dcn'";
			
			if(mysqli_query($dbc,$query)){
				print '<p style="text-align:center;">Dive Center data has been updated in the database</p>';
			} else {
				print '<p class="error">Could not update data in the database because:' . mysqli_error($dbc) . '.</p>';
			}
			
		}
	
	$_POST = array();
	
	} // End of "no problem" IF
	
	
} 
?>
<!--Print out the form-->
<div style="margin-top:1em;">
<form action="data_collection.php" method="post">
	<table class="centered">
	<caption style="font-weight:bold;">Enter your dive center data</caption>
	<tr><td>Part of the world:</td><td><input type="text" name="Part_of_the_World" size="20" value="<?php if(isset($_POST['Part_of_the_World'])){print htmlspecialchars($_POST['Part_of_the_World']);}?>"/></td></tr>
	<tr><td>Country:</td><td><input type="text" name="Country" size="20" value="<?php if(isset($_POST['Country'])){print htmlspecialchars($_POST['Country']);}?>"/></td></tr>
	<tr><td>Region:</td><td><input type="text" name="Region" size="20" value="<?php if(isset($_POST['Region'])){print htmlspecialchars($_POST['Region']);}?>"/></td></tr>
	<tr><td>Location:</td><td><input type="text" name="Location" size="20" value="<?php if(isset($_POST['Location'])){print htmlspecialchars($_POST['Location']);}?>"/></td></tr>
	<tr><td>Dive Center Name:</td><td><input type="text" name="Dive_Center_Name" size="20" value="<?php if(isset($_POST['Dive_Center_Name'])){print htmlspecialchars($_POST['Dive_Center_Name']);}?>"/></td></tr>
	<tr><td>Website:</td><td><input type="url" name="Website" size="20" value="<?php if(isset($_POST['Website'])){print htmlspecialchars($_POST['Website']);}?>"/></td></tr>
	<tr><td>Email:</td><td><input type="email" name="Email" size="20" value="<?php if(isset($_POST['Email'])){print htmlspecialchars($_POST['Email']);}?>"/></td></tr>
	<tr><td>Address:</td><td><input type="text" name="Address" size="20" value="<?php if(isset($_POST['Address'])){print htmlspecialchars($_POST['Address']);}?>"/></td></tr>
	<tr><td>Phone Number:</td><td><input type="text" name="Phone_No" size="20" value="<?php if(isset($_POST['Phone_No'])){print htmlspecialchars($_POST['Phone_No']);}?>"/></td></tr>
	<tr><td>Currency:</td><td><input type="text" name="Currency" size="20" value="<?php if(isset($_POST['Currency'])){print htmlspecialchars($_POST['Currency']);}?>"/></td></tr>
	<tr><td>DSD price:</td><td><input type="number" name="DSD_price" size="10" value="<?php if(isset($_POST['DSD_price'])){print htmlspecialchars($_POST['DSD_price']);}?>"/></td></tr>
	<tr><td>OWD price:</td><td><input type="number" name="OWD_price" size="10" value="<?php if(isset($_POST['OWD_price'])){print htmlspecialchars($_POST['OWD_price']);}?>"/></td></tr>
	<tr><td>AOWD price:</td><td><input type="number" name="AOWD_price" size="10" value="<?php if(isset($_POST['AOWD_price'])){print htmlspecialchars($_POST['AOWD_price']);}?>"/></td></tr>
	<tr><td>Price per 2 dives:</td><td><input type="number" name="Price_per_2_dives" size="10" value="<?php if(isset($_POST['Price_per_2_dives'])){print htmlspecialchars($_POST['Price_per_2_dives']);}?>"/></td></tr>
	<tr><td>Price per 10 dives:</td><td><input type="number" name="Price_per_10_dives" size="10" value="<?php if(isset($_POST['Price_per_10_dives'])){print htmlspecialchars($_POST['Price_per_10_dives']);}?>"/></td></tr>
	<tr><td></td><td style="text-align:right;"><input type="submit" name="submit" value="Submit"/></td></tr>
</form>
</div>

<?php
include('templates/footer.html');
?>