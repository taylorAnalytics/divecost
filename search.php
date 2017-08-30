<?php
/* This script will
- check if the user is logged in
- display the search form
- the search form will have a few fields, listboxes actually that will be filtered cascadically
- show the results of the search - the name of the dive center and the prices - which ones?
- it will enable a link to a more detailed website with the dive center info */

session_start();
define('TITLE', 'Dive Center Search');
include('templates/header.html');

// Need the filters to feed in each of the fields - can do it with JavaScript
// Functionality of sorting the results based on a price - JavaScript

// Print the header of the page
	print '<header><h1>Find your dive center</h1></header>';
		
	
if (loggedin()) {
		
	$dbc = mysqli_connect('localhost', 'user', 'user', 'dive_cost');
	
	// Get the list of the parts of the world from dive_centers Database
	$query = "SELECT DISTINCT Part_of_the_World FROM dive_centers";
	
	if($r = mysqli_query($dbc, $query)) { // Run the query
		
		// Retrieve the information
		$part_of_the_world_list = array();
		while ($row = mysqli_fetch_array($r, MYSQLI_NUM)) {
			$part_of_the_world_list[] = $row[0];
		} 
	} else {  // Couldn't run the query
		print '<p style="color: red;">Could not retrieve the data because: <br> ' . mysqli_error($dbc) . '.</p>';
	}
	sort($part_of_the_world_list);
	
	if (isset($_POST['part_of_the_world']) && $_POST['part_of_the_world'] != "ALL") {
		// Get the list of the countries from dive_centers Database
		$query = "SELECT DISTINCT Country FROM dive_centers WHERE Part_of_the_World='{$_POST['part_of_the_world']}'";
	
		if($r = mysqli_query($dbc, $query)) { // Run the query
		
			// Retrieve the information
			$country_list = array();
			while ($row = mysqli_fetch_array($r, MYSQLI_NUM)) {	
				$country_list[] = $row[0];
			}
		
		} else { // Couldn't run the query
			print '<p style="color: red;">Could not retrieve the data because: <br> ' . mysqli_error($dbc) . '.</p>';
		}
		sort($country_list);
		}
	
	if (isset($_POST['country']) && $_POST['country'] != "ALL") {
		// Get the list of regions from dive_centers Database
		$query = "SELECT DISTINCT Region FROM dive_centers WHERE Country='{$_POST['country']}'";
		if($r = mysqli_query($dbc, $query)) { // Run the query
	
			// Retrieve the information
			$region_list = array();
			while ($row = mysqli_fetch_array($r, MYSQLI_NUM)){
				$region_list[] = $row[0];	
			}
		} else { // Couldn't run the query
			print '<p style="color: red;">Could not retrieve the data because: <br> ' . mysqli_error($dbc) . '.</p>';
		}
		sort($region_list);
		}
	
	if (isset($_POST['region']) && $_POST['region'] != "ALL") {
		// Get the list of locations from dive_centers Database
		$query = "SELECT DISTINCT Location FROM dive_centers WHERE Region='{$_POST['region']}'";
	
		if($r = mysqli_query($dbc, $query)) { // Run the query
	
			// Retrieve the information
			$location_list = array();
			while ($row = mysqli_fetch_array($r, MYSQLI_NUM)) {
				$location_list[] = $row[0];
			}
		} else {
			print '<p style="color: red;">Could not retrieve the data because: <br> ' . mysqli_error($dbc) . '.</p>';
		}
		sort($location_list); 
		}
	
		
	// Check if the form had been submitted
	if(isset($_POST['search'])) {
		$column = NULL; // Variable will be used to SELECT from a proper column in SQL
		$filter = NULL; // Variable will be used to WHERE proper values in SQL
		
		if(isset($_SESSION['location']) && $_SESSION['location'] !== '*') { // The location had been selected
			$column = 'Location';
			$filter = $_SESSION['location'];
		} else { // The location had not been selected
		
			if(isset($_SESSION['region']) && $_SESSION['region'] !== '*') { // The region had been selected
				$column = 'Region';
				$filter = $_SESSION['region'];			
			} else { // The region had not been selected
			
				if(isset($_SESSION['country']) && $_SESSION['country'] !== '*') { // The country had been selected
					$column = 'Country';
					$filter = $_SESSION['country'];
				} else { // The country had not been selected
						$column = 'Part_of_the_World';
						$filter = $_SESSION['part_of_the_world'];
				} // End of country IF
			} // End of region IF
		}// End of location IF
				
		// Define the query based on submitted values
		if($filter == '*') { // A full search
			$query = "SELECT Dive_Center_Name, Country, Region, Location, Currency, DSD_price, OWD_price, AOWD_price, Website FROM dive_centers";
		} else { // A search based on one of the selected options from the lists
			$query = "SELECT Dive_Center_Name, Country, Region, Location, Currency, DSD_price, OWD_price, AOWD_price, Website FROM dive_centers WHERE $column = '".$filter."'";
		}
		
		// Print out the search form
		include('includes/search_form.html');
		
		// Create an array with the result of the query
		if($r = mysqli_query($dbc, $query)) { // Run the query
		
			// Prepare the query for the currency exchange rate
			$query2 = "SELECT currency, rate FROM forex";
			
			if ($r2 = mysqli_query($dbc, $query2)) { // Run the forex query
				
				// Turn query results into array
				$i=0;
				$forex=array();
				while($row2=mysqli_fetch_array($r2,MYSQLI_ASSOC)){
					$forex[$i]['currency'] = $row2['currency'];
					$forex[$i]['rate'] = $row2['rate'];
					$i++;
				}
			} else {
			print '<p class="error">Could not run the query because: <br>' . mysqli_error($dbc) . '.</p>';
			}
		
			// Turn the original query results into an array
			$i=0;
			$result=array();
			while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)){
				$result[$i]['Dive_Center_Name'] = $row['Dive_Center_Name'];
				$result[$i]['Country'] = $row['Country'];
				$result[$i]['Region'] = $row['Region'];
				$result[$i]['Location'] = $row['Location'];
				$result[$i]['Currency'] = $row['Currency'];
				if($result[$i]['Currency'] !== 'USD') {
					$k=0;
					$m=count($forex);
					for($k=0;$k<$m;$k++){
						if($result[$i]['Currency'] == $forex[$k]['currency']){
							$result[$i]['OWD_price'] = ($row['OWD_price']/$forex[$k]['rate']);
							$result[$i]['AOWD_price'] = ($row['AOWD_price']/$forex[$k]['rate']);
							$result[$i]['DSD_price'] = ($row['DSD_price']/$forex[$k]['rate']);
						} // End of IF
					} // End of FOR
				} else {
					$result[$i]['OWD_price'] = $row['OWD_price'];
					$result[$i]['AOWD_price'] = $row['AOWD_price'];
					$result[$i]['DSD_price'] = $row['DSD_price'];
				} // End of IF
				$result[$i]['Website'] = $row['Website'];
				$i++;
			}
		$_SESSION['result'] = $result;	
		
		// Print out the header of the table with the results of the search
			print '<div style="margin-top: 16px; overflow-x: auto;"><table class="result" style="margin: 0 auto;">
				<tr>
					<th class="result"  style="text-align:left;">Dive Center Name</th>
					<th class="result">Country</th>
					<th class="result">Region</th>
					<th class="result">Location</th>
					<th class="result">Currency</th>
					<th class="result">DSD price <a class="sort" href="search.php?course=DSD_price&direction=asc">&#8593</a><a class="sort" href="search.php?course=DSD_price&direction=desc">&#8595</a></th>
					<th class="result">OWD price<a class="sort" href="search.php?course=OWD_price&direction=asc">&#8593</a><a class="sort" href="search.php?course=OWD_price&direction=desc">&#8595</a></th>
					<th class="result">AOWD price<a class="sort" href="search.php?course=AOWD_price&direction=asc">&#8593</a><a class="sort" href="search.php?course=AOWD_price&direction=desc">&#8595</a></th>
					<th class="result">Original Currency</th>
					<th class="result">Website</th>
				</tr>';
					
			$n=count($result);
			for($i=0; $i<$n; $i++) {
				print '<tr>';
				print "<td class=\"result\" style=\"text-align: left;\">". $result[$i]['Dive_Center_Name'] . "</td>";
				print "<td class=\"result\">". $result[$i]['Country'] . "</td>";
				print "<td class=\"result\">". $result[$i]['Region'] . "</td>";
				print "<td class=\"result\">". $result[$i]['Location'] . "</td>";
				print "<td class=\"result\">USD</td>";
				print "<td class=\"result\">". number_format($result[$i]['DSD_price']) . "</td>";
				print "<td class=\"result\">". number_format($result[$i]['OWD_price']) . "</td>";
				print "<td class=\"result\">". number_format($result[$i]['AOWD_price']) . "</td>";
				print "<td class=\"result\">". $result[$i]['Currency'] . "</td>";
				print "<td class=\"result\"><a href=\"" . $result[$i]['Website'] . "\" style=\"text-decoration:underline;\" target=\"_blank\">Click</a></td>";
				print '</tr>';
			}
			
			print '</table></div>';
			
			
		
		} else {
			print '<p style="color: red;">Could not run the query because: <br>' . mysqli_error($dbc) . '.</p>';
		}
	
	} else {
	
	// Apply selected values from the form to variables
	
	$_SESSION['part_of_the_world']="*";
	$_SESSION['country']="*";
	$_SESSION['region']="*";
	$_SESSION['location']="*";
	
	if(isset($_POST['part_of_the_world'])) {
		$_SESSION['part_of_the_world'] = $_POST['part_of_the_world'];
	} 
	
	if(isset($_POST['country'])) {
		$_SESSION['country'] = $_POST['country'];
	}
	
	if(isset($_POST['region'])) {
		$_SESSION['region'] = $_POST['region'];
	}
	if(isset($_POST['location'])) {
		$_SESSION['location'] = $_POST['location'];
	}
	
	// Need to close MySQL connection
	mysqli_close($dbc);
	
	// Print out the search form
	include('includes/search_form.html');
	
	if(isset($_SESSION['result'])) {
		// reload the results data from $_SESSION into the $result variable
		$result = $_SESSION['result'];
		// Check if the sort button had been clicked
		if(isset($_GET['course'])) {
			// define the sorting function depending on the course & the direction
			$course = $_GET['course'];
			if($_GET['direction'] === "asc"){
				function sort_by_course($a,$b) {
				global $course;
				return $a["$course"] - $b["$course"];
			} // end of function
			} else {
				function sort_by_course($b,$a) {
				global $course;
				return $a["$course"] - $b["$course"];
			} // end of function
			} // end of if/else
			usort($result, 'sort_by_course');
		}
		// Print out the header of the table with the results of the search
		print '<div style="margin-top: 16px; overflow-x: auto;"><table class="result" style="margin: 0 auto;">
			<tr>
				<th class="result"  style="text-align:left;">Dive Center Name</th>
				<th class="result">Country</th>
				<th class="result">Region</th>
				<th class="result">Location</th>
				<th class="result">Currency</th>
				<th class="result">DSD price <a class="sort" href="search.php?course=DSD_price&direction=asc">&#8593</a><a class="sort" href="search.php?course=DSD_price&direction=desc">&#8595</a></th>
				<th class="result">OWD price<a class="sort" href="search.php?course=OWD_price&direction=asc">&#8593</a><a class="sort" href="search.php?course=OWD_price&direction=desc">&#8595</a></th>
				<th class="result">AOWD price<a class="sort" href="search.php?course=AOWD_price&direction=asc">&#8593</a><a class="sort" href="search.php?course=AOWD_price&direction=desc">&#8595</a></th>
				<th class="result">Original Currency</th>
				<th class="result">Website</th>
			</tr>';		
		// Print out the data fields of the table
		$n=count($result);
		for($i=0; $i<$n; $i++) {
			print '<tr>';
			print "<td class=\"result\" style=\"text-align: left;\">". $result[$i]['Dive_Center_Name'] . "</td>";
			print "<td class=\"result\">". $result[$i]['Country'] . "</td>";
			print "<td class=\"result\">". $result[$i]['Region'] . "</td>";
			print "<td class=\"result\">". $result[$i]['Location'] . "</td>";
			print "<td class=\"result\">USD</td>";
			print "<td class=\"result\">". number_format($result[$i]['DSD_price']) . "</td>";
			print "<td class=\"result\">". number_format($result[$i]['OWD_price']) . "</td>";
			print "<td class=\"result\">". number_format($result[$i]['AOWD_price']) . "</td>";
			print "<td class=\"result\">". $result[$i]['Currency'] . "</td>";
			print "<td class=\"result\"><a href=\"" . $result[$i]['Website'] . "\" style=\"text-decoration:underline;\" target=\"_blank\">Click</a></td>";
			print '</tr>';
		}	
		print '</table></div>';
	}	
	}
	
	print '<footer><p style="text-align:center;"><a href="analytics.php" class="button" style="margin:0 auto">Analyze</a><a href="logout.php" class="button" style="margin:0 auto">Log out</a></p></footer>';
	
} else {
	print '<p class="error">This website is restricted to logged users only<br>
		You can log in <a href="login.php">here</a></p>';
}
include ('templates/footer.html');
?>
 