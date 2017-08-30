<form action="search.php" method="post">
	
	<p>Part of the world:
	<select name="part_of_the_world">
	<option value="*">All</option>
	<?php // Print out the list from SQL
	foreach ($part_of_the_world_list as $part_of_the_world_list) {
		print "<option value=\"$part_of_the_world_list\">$part_of_the_world_list</option>\n";
	}?>
	</select></p>
	
	<p>Country:
	<select name="country">
	<option value="*">All</option>
	<?php // Print out the list from SQL
	foreach($country_list as $val) {
		print "<option value=\"$val\">$val</option>\n";
	}?>
	</select></p>
	
	<p>Region: 
	<select name="region">
	<option value="*">All</option>
	<?php // Print out the list from SQL
	foreach($region_list as $val){
		print "<option value=\"$val\">$val</option>\n";
	}?>
	</select></p>
	
	<p>Location:
	<select name="location">
	<option value="*">All</option>
	<?php // Print out the list from SQL
	foreach($location_list as $val){
		print "<option value=\"$val\">$val</option>\n";
	}?>
	</select></p>
	
	<p><input type="submit" name="submit" value="Search!"></p>
</form>
