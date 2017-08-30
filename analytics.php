<?php
/* This script will enable users to create their own analytical table. It will:
- let them choose a dive course type and level at whitch they want to analyze prices - from a form
- do the MySQL query to take data out of the database - based on the results of the form
- show the table of the extracted data */

define('TITLE','Dive Cost Analytics');
include('templates/header.html');

// Print the header of the page
print '<header><h1>Find your dive center</h1></header>';


if(loggedin()) { // Check if the user has logged in correctly
	
	// Manage the form
	if($_SERVER['REQUEST_METHOD']=='POST') { // The form has been submitted
		
		include('includes/analytics_form.html');
		
		$problem=FALSE;
		if(!isset($_POST['course'])) { // No course had been selected
			$problem=TRUE;
			print '<p class="error">Please select a course</p>';
		} else {
			$course = $_POST['course'];
		}
		
		if(!isset($_POST['dimension'])) { // No dimension had been selected
			$problem=TRUE;
			print '<p class="error">Please select a dimension</p>';
		} else {
			$dimension = $_POST['dimension'];
		}
		
		if(!$problem) {
		// Create a distinct list of the $dimension variable elements - SQL query
	
		// Open the connection with MySQL
		$dbc = mysqli_connect('localhost', 'user', 'user', 'dive_cost');
		$course = mysqli_real_escape_string($dbc, $course);
		$dimension = mysqli_real_escape_string($dbc, $dimension);
		$query = "SELECT DISTINCT $dimension FROM dive_centers";
		// Run the query
		if($r = mysqli_query($dbc, $query)) {
		$i=0;
			while ($row = mysqli_fetch_array($r,MYSQLI_ASSOC)) {
				$dimension_array[$i]["$dimension"] = $row["$dimension"];
				$i++;
			}
		} else {
			print '<p class="error">Could not retrieve data because: ' . mysqli_error($dbc) . '.</p>';
		}
		
		// Create the forex array
		$query = "SELECT currency, rate FROM forex";
		if($r = mysqli_query($dbc, $query)) {
			$i=0;
			$forex=array();
			while($row=mysqli_fetch_array($r,MYSQLI_ASSOC)){
				$forex[$i]['currency'] = $row['currency'];
				$forex[$i]['rate'] = $row['rate'];
				$i++;
			}
		} else {
		print '<p class="error">Could not run the query because: <br>' . mysqli_error($dbc) . '.</p>';
		}
		
		// Create the $data_array that consists of all the data
		$query = "SELECT $dimension, $course, Currency FROM dive_centers";
		if($r = mysqli_query($dbc, $query)) { // Run the query
			$i=0;
			while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
				$data_array[$i]["$dimension"] = $row["$dimension"];
				// Turn all prices into USD
				$k=0;
				$m=count($forex);
				for ($k=0;$k<$m;$k++){
					if($row['Currency'] == $forex[$k]['currency']) {
						$data_array[$i]["$course"] = $row["$course"] / $forex[$k]['rate'];
					} // End of IF
				} // End of FOR
				$i++;
			} // End of WHILE
		} else {
			print '<p class="error">Could not run the query because: <br>' . mysqli_error($dbc) . '.</p>';
		}	
		
		
		// Calcualte the global average
		$avg=0;
		$l=0;
		$n=count($data_array);
		for ($i=0; $i<$n; $i++) { // Sum the data and count how many times they appear
			if($data_array[$i]["$course"] > 0) {
				$avg=$avg+$data_array[$i]["$course"];
				$l++;
			} // End of IF
		} // End of FOR
		
		$avg = $avg / $l;
		
		// Finish the dimension array with the data from data_array
		
		$m=count($dimension_array);
		$n=count($data_array);
		
		for ($i=0;$i<$m; $i++) {
			$l=0;
			$price=0;
			for ($k=0;$k<$n;$k++) {
				if($dimension_array[$i]["$dimension"] == $data_array[$k]["$dimension"]) {
					if($data_array[$k]["$course"]>0) {
					$price = $price + $data_array[$k]["$course"];
					$l++;
					}
				}
			}
			if ($l == 0){
				$dimension_array[$i]["$course"] = $price;
			} else {
				$dimension_array[$i]["$course"] = $price / $l;
			}
		}
		
		function sort_by_course($a,$b) {
			global $course;
			return $a["$course"] - $b["$course"];
		}
		
		usort($dimension_array, 'sort_by_course');
			
		// Print out the table with the results
		print '<div style="margin-top:16px;text-align:center;">';
		
		print '<table class="result" style="display:inline-block; margin:16px;">
			<caption>Average prices</caption>
			<tr>
			<th class="result" style="text-align:left">' . $dimension . '</th>
			<th class="result">Average ' . $course . '</th>
			<th class="result">Index vs global average</th>
			</tr>';
		
		for ($i=0;$i<$m;$i++) {
			print '<tr>';
			print '<td class="result" style="text-align:left;">' . $dimension_array[$i]["$dimension"] . '</td>';
			print '<td class="result">' . number_format($dimension_array[$i]["$course"]) . '</td>';
			print '<td class="result">' . number_format(($dimension_array[$i]["$course"]/$avg)*100) . '</td>';
			print '</tr>';
		}
		
		print '</table></div>';
		
		}
		
	} else { // The form has not been submitted
	include('includes/analytics_form.html');
	}
	print '<footer><p style="text-align:center;"><a href="search.php" class="button" style="margin:0 auto">Search</a> <a href="logout.php" class="button" style="margin:0 auto">Log out</a></p></footer>';
} else {
	print '<p class="error">This website is restricted to logged users only<br>
		You can log in <a href="login.php">here</a></p>';
}

include('templates/footer.html');
?>