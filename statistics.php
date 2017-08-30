<?php
/* This script will:
- calculate some basic statistics, like average price of the course or the index vs average benchmark - whatever, just to do some calcualtions
- display the results in some tables
- tables and sets of analysis will be set - no filters - maybe later
- the purpose is to play with MySQL queries and arrays containing data */

define('TITLE', 'Dive Cost Statistics');
include ('templates/header.html');

// Print the header of the page
print '<header><h1>Find your dive center</h1></header>';

if (loggedin()) {
	
	// Connect to the database
	
	$dbc = mysqli_connect('localhost', 'user', 'user', 'dive_cost');
	
	$query = "SELECT Part_of_the_World, Country, Region, Location, Currency, DSD_price, OWD_price, AOWD_price FROM dive_centers";
	
	if($r = mysqli_query($dbc, $query)) {// Run the query
		
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
		
		// Retrieve the data from the main query into an array, including changing the currency 
		$i=0;
		$data_array = array();
		while ($row = mysqli_fetch_array($r,MYSQLI_ASSOC)) { // Fetch row by row and turn it into array
			$data_array[$i]['Part_of_the_World'] = $row['Part_of_the_World'];
			$data_array[$i]['Country'] = $row['Country'];
			$data_array[$i]['Region'] = $row['Region'];
			$data_array[$i]['Location'] = $row['Location'];
			$data_array[$i]['Currency'] = $row['Currency'];
			// Turn all prices into USD
			$k=0;
			$m=count($forex);
			for($k=0;$k<$m;$k++){
				if($data_array[$i]['Currency'] == $forex[$k]['currency']){
					$data_array[$i]['OWD_price'] = ($row['OWD_price']/$forex[$k]['rate']);
					$data_array[$i]['AOWD_price'] = ($row['AOWD_price']/$forex[$k]['rate']);
					$data_array[$i]['DSD_price'] = ($row['DSD_price']/$forex[$k]['rate']);
				} // End of IF
			} // End of FOR
			$i++;
		}
		
	} else {
		print '<p class="error">Could not run the query because:' . mysqli_error($dbc) . '.</p>';
	}
	

	// Use the array to create new arrays, calcualting the necessary indexes and averages and stuff
	
	// Create the array of average prices by Country
	// Create the query
	$query = "SELECT DISTINCT Country FROM dive_centers";
	
	if($r = mysqli_query($dbc, $query)){ // Run the query
		$i=0;
		while($row = mysqli_fetch_array($r)){ // Retrieve the information
			$country_array[$i]['Country'] = $row[0];
			$i++;
		}
		$m=count($country_array); // No of rows of $country_array
		$n=count($data_array); // No of rows of $data_array
		for ($i=0; $i<$m; $i++) {
			$l_OWD=0; // No of data points by country
			$l_AOWD=0;
			$l_DSD=0;
			$OWD=0;
			$AOWD=0;
			$DSD=0;
			for($k=0; $k<$n; $k++){
				if($country_array[$i]['Country'] == $data_array[$k]['Country']) {
					if($data_array[$k]['OWD_price'] > 0) {
							$OWD = $OWD + $data_array[$k]['OWD_price'];
							$l_OWD++;
					}
					if($data_array[$k]['AOWD_price'] > 0) {
							$AOWD = $AOWD + $data_array[$k]['AOWD_price'];
							$l_AOWD++;
					}
					if($data_array[$k]['DSD_price'] > 0) {
							$DSD = $DSD + $data_array[$k]['DSD_price'];
							$l_DSD++;
					}
				} // End of IF
			} // End of for $k
			$country_array[$i]['OWD_price'] = $OWD / $l_OWD;
			$country_array[$i]['AOWD_price'] = $AOWD / $l_AOWD;
			$country_array[$i]['DSD_price'] = $DSD / $l_DSD;
		} // End of for $i
		
	} else {
		print '<p class="error">Could not run the query because:' . mysqli_error($dbc) . '.</p>';
	}
	
	// Create the array of average prices by Location
	// Create the query
	$query = "SELECT DISTINCT Country, Location FROM dive_centers";
	
	if($r = mysqli_query($dbc, $query)){ // Run the query
		$i=0;
		while($row = mysqli_fetch_array($r,MYSQLI_ASSOC)){ // Retrieve the information
			$location_array[$i]['Country'] = $row['Country'];
			$location_array[$i]['Location'] = $row['Location'];
			$i++;
		}
		
		$m=count($location_array); // No of rows of $location_array
		$n=count($data_array); // No of rows of $data_array
		for ($i=0; $i<$m; $i++) {
			$l_OWD=0; // No of data points by country
			$l_AOWD=0;
			$l_DSD=0;
			$OWD=0;
			$AOWD=0;
			$DSD=0;
			for($k=0; $k<$n; $k++){
				if($location_array[$i]['Location'] == $data_array[$k]['Location']) {
					if($data_array[$k]['OWD_price'] > 0) {
							$OWD = $OWD + $data_array[$k]['OWD_price'];
							$l_OWD++;
					}
					if($data_array[$k]['AOWD_price'] > 0) {
							$AOWD = $AOWD + $data_array[$k]['AOWD_price'];
							$l_AOWD++;
					}
					if($data_array[$k]['DSD_price'] > 0) {
							$DSD = $DSD + $data_array[$k]['DSD_price'];
							$l_DSD++;
					}
				} // End of IF
			} // End of for $k
			$location_array[$i]['OWD_price'] = $OWD / $l_OWD;
			$location_array[$i]['AOWD_price'] = $AOWD / $l_AOWD;
			$location_array[$i]['DSD_price'] = $DSD / $l_DSD;
		} // End of for $i
		
	} else {
		print '<p class="error">Could not run the query because:' . mysqli_error($dbc) . '.</p>';
	}
	
	// Calculate the global average prices
	$OWD_avg=0;
	$AOWD_avg=0;
	$DSD_avg=0;
	$l_OWD=0;
	$l_AOWD=0;
	$l_DSD=0;
	$n=count($data_array);
	for ($i=0; $i<$n; $i++) { // Sum the data and count how many times they appear
		if($data_array[$i]['OWD_price'] > 0) {
			$OWD_avg=$OWD_avg+$data_array[$i]['OWD_price'];
			$l_OWD++;
		}
		if($data_array[$i]['AOWD_price']>0) {
			$AOWD_avg=$AOWD_avg+$data_array[$i]['AOWD_price'];
			$l_AOWD++;
		}
		if($data_array[$i]['DSD_price']>0) {
			$DSD_avg=$DSD_avg+$data_array[$i]['DSD_price'];
			$l_DSD++;
		}
	}
	// Calculate the averages - divide the sum by the number of appearances
	$OWD_avg=$OWD_avg / $l_OWD;
	$AOWD_avg=$AOWD_avg / $l_AOWD;
	$DSD_avg=$DSD_avg / $l_DSD;
	
	// Print out the data in tables - inline, not block

	print '<div style="margin-top: 16px;">';
	// OWD price table

	print '<table class="result" style="display:inline-block; margin:16px;">
	<caption>Average OWD prices by country</caption>
	<tr>
		<th class="result" style="text-align:left">Country</th>
		<th class="result">Average OWD price</th>
		<th class="result">Index vs global average</th>
	</tr>';
	
	$m=count($country_array);
	for ($i=0; $i<$m; $i++) {
		print '</tr>';
		print "<td class=\"result\" style=\"text-align:left;\">" . $country_array[$i]['Country'] . "</td>";
		print "<td class=\"result\">". number_format($country_array[$i]['OWD_price']) . "</td>";
		$country_array[$i]['OWD_index'] = $country_array[$i]['OWD_price']/$OWD_avg*100;
		print "<td class=\"result\">". number_format($country_array[$i]['OWD_index']) . "</td>";
		print '</tr>';	
	}
	print '</table>';
	
	// AOWD price table
	print '<table class="result" style="display:inline-block; margin:16px;">
	<caption style="border-style:none;">Average AOWD prices by country</caption>
	<tr>
		<th class="result" style="text-align:left">Country</th>
		<th class="result">Average AOWD price</th>
		<th class="result">Index vs global average</th>
	</tr>';
	
	$m=count($country_array);
	for ($i=0; $i<$m; $i++) {
		print '</tr>';
		print "<td class=\"result\" style=\"text-align:left;\">" . $country_array[$i]['Country'] . "</td>";
		print "<td class=\"result\">". number_format($country_array[$i]['AOWD_price']) . "</td>";
		$country_array[$i]['AOWD_index'] = $country_array[$i]['AOWD_price']/$AOWD_avg*100;
		print "<td class=\"result\">". number_format($country_array[$i]['AOWD_index']) . "</td>";
		print '</tr>';	
	}
	print '</table>';
	
	// DSD price table
	print '<table class="result" style="display:inline-block; margin:16px;">
	<caption style="border-style:none;">Average DSD prices by country</caption>
	<tr>
		<th class="result" style="text-align:left">Country</th>
		<th class="result">Average DSD price</th>
		<th class="result">Index vs global average</th>
	</tr>';
	
	$m=count($country_array);
	for ($i=0; $i<$m; $i++) {
		print '</tr>';
		print "<td class=\"result\" style=\"text-align:left;\">" . $country_array[$i]['Country'] . "</td>";
		print "<td class=\"result\">". number_format($country_array[$i]['DSD_price']) . "</td>";
		$country_array[$i]['DSD_index'] = $country_array[$i]['DSD_price']/$DSD_avg*100;
		print "<td class=\"result\">". number_format($country_array[$i]['DSD_index']) . "</td>";
		print '</tr>';	
	}
	print '</table>';
	
	print '</div>';
	
	// BY LOCATION - Print out the data in tables - inline, not block
	print '<div style="margin-top: 16px;">';
	
	// OWD price table
	print '<table class="result" style="display:inline-block; margin:16px;">
	<caption>Average OWD prices by location</caption>
	<tr>
		<th class="result" style="text-align:left;">Country</th>
		<th class="result" style="text-align:left;">Location</th>
		<th class="result">Average OWD price</th>
		<th class="result">Index vs global average</th>
	</tr>';
	
	$m=count($location_array);
	for ($i=0; $i<$m; $i++) {
		print '</tr>';
		print "<td class=\"result\" style=\"text-align:left;\">" . $location_array[$i]['Country'] . "</td>";
		print "<td class=\"result\" style=\"text-align:left;\">" . $location_array[$i]['Location'] . "</td>";
		print "<td class=\"result\">". number_format($location_array[$i]['OWD_price']) . "</td>";
		$location_array[$i]['OWD_index'] = $location_array[$i]['OWD_price']/$OWD_avg*100;
		print "<td class=\"result\">". number_format($location_array[$i]['OWD_index']) . "</td>";
		print '</tr>';	
	}
	print '</table>';
	
	// AOWD price table
	print '<table class="result" style="display:inline-block; margin:16px;">
	<caption>Average AOWD prices by location</caption>
	<tr>
		<th class="result" style="text-align:left;">Country</th>
		<th class="result" style="text-align:left;">Location</th>
		<th class="result">Average AOWD price</th>
		<th class="result">Index vs global average</th>
	</tr>';
	
	$m=count($location_array);
	for ($i=0; $i<$m; $i++) {
		print '</tr>';
		print "<td class=\"result\" style=\"text-align:left;\">" . $location_array[$i]['Country'] . "</td>";
		print "<td class=\"result\" style=\"text-align:left;\">" . $location_array[$i]['Location'] . "</td>";
		print "<td class=\"result\">". number_format($location_array[$i]['AOWD_price']) . "</td>";
		$location_array[$i]['AOWD_index'] = $location_array[$i]['AOWD_price']/$AOWD_avg*100;
		print "<td class=\"result\">". number_format($location_array[$i]['AOWD_index']) . "</td>";
		print '</tr>';	
	}
	print '</table>';
	
	// DSD price table
	print '<table class="result" style="display:inline-block; margin:16px;">
	<caption>Average DSD prices by location</caption>
	<tr>
		<th class="result" style="text-align:left;">Country</th>
		<th class="result" style="text-align:left;">Location</th>
		<th class="result">Average DSD price</th>
		<th class="result">Index vs global average</th>
	</tr>';
	
	$m=count($location_array);
	for ($i=0; $i<$m; $i++) {
		print '</tr>';
		print "<td class=\"result\" style=\"text-align:left;\">" . $location_array[$i]['Country'] . "</td>";
		print "<td class=\"result\" style=\"text-align:left;\">" . $location_array[$i]['Location'] . "</td>";
		print "<td class=\"result\">". number_format($location_array[$i]['DSD_price']) . "</td>";
		$location_array[$i]['DSD_index'] = $location_array[$i]['DSD_price']/$DSD_avg*100;
		print "<td class=\"result\">". number_format($location_array[$i]['DSD_index']) . "</td>";
		print '</tr>';	
	}
	print '</table>';
	
	print '</div>';
} else {// If the user had not logged in
	print '<p class="error">This website is restricted to logged users only</p>';
}

include('templates/footer.html');
?>