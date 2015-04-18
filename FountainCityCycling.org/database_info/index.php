<!DOCTYPE html>
<html>

<head>
	<br>
	<div id="txtHint"><b>Select the database to display: </b></div>
	<form method="post">
		<select name="tableName">
  			<option value="leaderboard">Leaderboard</option>
  			<option value="coord">Trip Coordinates</option>
  			<option value="trip">Trip Info</option>
  			<option value="note">Note Info</option>
  			<option value="user">User Info</option>
		</select> 
		<input type="submit"/>
		<br>
		<br>
		<br>
	</form>
</head>

<body>
<?php

        $DBHost = "mysql.fountaincitycycling.org";
        $DBUser = "krs0013_user";
        $DBPass = "databaseFountainCity";
        $DBName = "cycling_data_db";

        /* Connect to the server and put it into the con varibale */
        $con = mysqli_connect($DBHost,$DBUser,$DBPass,$DBName) or die ("Unable to connect to server");
        
        $query = "";
	if (isset($_POST['tableName']))
		$query = $_POST['tableName'];
	else 
		$query = "leaderboard";

	switch ($query) {
	    
	    case "trip":
        	$query = "SELECT * FROM trip";
        	$result = mysqli_query($con, $query);

		if (!$result) {
                	printf("Error: %s\n", $con->error);
        	} else {
			echo "<b>Trip Information:</b>";
			echo "<ul>This table holds general information about each trip</ul>";
			echo "<ul>Each Trip ID will be unique.</ul>";
			echo "<ul>To get a more specified version of each trip, check out Trip Coordinates in the dropdown.</ul>";
			echo "<ul>Note that each Trip ID has a certian Number of Coordinates.  That is how many rows the trip will take up in Trip Coordinates table.</ul>";
	    		echo '<table border="1">';
 	        	// This echo's the boarder and all of the names of the table
			// Displays table headers
	        	echo "<td>Trip ID</td>";
	        	echo "<td>User ID</td>";
	        	echo "<td>Purpose</td>";
	        	echo "<td>Notes</td>";
	        	echo "<td>Start Time</td>";
	        	echo "<td>End Time</td>";
	        	echo "<td>Number of Coordinates</td>";
	        	echo "<td>Distance</td>";
	        	echo "<td>CO2 Savings</td>";
	        	echo "<td>KiliCalories</td>";
	        	echo "<td>Avg Savings</td>";
	        	echo "<td>Score</td>";
	
	                $row = $result->fetch_row();
	                while($row) {
	                        echo '<tr>';
	                        for ($i = 0; $i<count($row); $i++) {
	                                echo '<td>' . $row[$i] . '</td>';
	                        }
	                        echo '</tr>';
	                        $row = $result->fetch_row();
	                }
	                echo '</table>';
		}
	        break;
	    case "note":
        	$query = "SELECT * FROM note";
        	$result = mysqli_query($con, $query);

		if (!$result) {
                	printf("Error: %s\n", $con->error);
        	} else {
			echo "<b>Note Information:</b>";
			echo "<ul>This table holds general information about each note</ul>";
			echo "<ul>Each Note ID will be unique.</ul>";
	    		echo '<table border="1">';
 	        	// This echo's the boarder and all of the names of the table
			// Displays table headers
	        	echo "<td>Note ID</td>";
	        	echo "<td>User ID</td>";
	        	echo "<td>Trip ID</td>";
	        	echo "<td>Date Created</td>";
	        	echo "<td>Latitude</td>";
	        	echo "<td>Longitude</td>";
	        	echo "<td>Altitude</td>";
	        	echo "<td>Speed</td>";
	        	echo "<td>hAccuracy</td>";
	        	echo "<td>vAccuracy</td>";
	        	echo "<td>Note Type</td>";
	        	echo "<td>Details</td>";
	        	echo "<td>Image URL</td>";
	
	                $row = $result->fetch_row();
	                while($row) {
	                        echo '<tr>';
	                        for ($i = 0; $i<count($row); $i++) {
	                                echo '<td>' . $row[$i] . '</td>';
	                        }
	                        echo '</tr>';
	                        $row = $result->fetch_row();
	                }
       		        echo '</table>';
		}
	        break;
	    case "coord":
        	$query = "SELECT * FROM coord";
        	$result = mysqli_query($con, $query);

		if (!$result) {
                	printf("Error: %s\n", $con->error);
        	} else {
			echo "<b>Trip Coordinates:</b>";
			echo "<ul><b><i><u>This is the most important table ever!</u></i></b></ul>";
			echo "<ul>This table allows a users trip to be mapped out again.</ul>";
			echo "<ul><b>Important: </b>The Trip ID will correspond with the Trip ID in Trip Information.</ul>";
			echo "<ul><b>Notice: </b>Coordinates are recorded every two seconds.</ul>";
	    		echo '<table border="1">';
 	        	// This echo's the boarder and all of the names of the table
			// Displays table headers
	        	echo "<td>Trip ID</td>";
	        	echo "<td>Date Recorded</td>";
	        	echo "<td>Latitude</td>";
	        	echo "<td>Longitude</td>";
	        	echo "<td>Altitude</td>";
	        	echo "<td>Speed</td>";
	        	echo "<td>hAccuracy</td>";
	        	echo "<td>vAccuracy</td>";

                	$row = $result->fetch_row();
                	while($row) {
                	        echo '<tr>';
                	        for ($i = 0; $i<count($row); $i++) {
                	                echo '<td>' . $row[$i] . '</td>';
                	        }
                	        echo '</tr>';
                	        $row = $result->fetch_row();
                	}
                	echo '</table>';
		}
	        break;
	    case "user":
        	$query = "SELECT * FROM user";
        	$result = mysqli_query($con, $query);

		if (!$result) {
                	printf("Error: %s\n", $con->error);
        	} else {
			echo "<b>User Information:</b>";
			echo "<ul>This table holds the voluntary information provided by each user (may or may not be factual).</ul>";
			echo "<ul><b>Important: </b>This User ID corresponds to the User ID found in Note Info and Trip Info</ul>";
			echo "<ul>If home, school, or work zip is blank, that means the user did not fill it out</ul>";
			echo "<ul>If age, gender, income, ethnicity, cycle frequency, rider history, or rider type equals 0, that means the user did not fill it out</ul>";
	    		echo '<table border="1">';
 	        	// This echo's the boarder and all of the names of the table
			// Displays table headers
	        	echo "<td>User ID</td>";
	        	echo "<td>Date Created</td>";
	        	echo "<td>Device ID</td>";
	        	echo "<td>OS Version</td>";
	        	echo "<td>Email</td>";
	        	echo "<td>Age</td>";
	        	echo "<td>Gender</td>";
	        	echo "<td>Income</td>";
	        	echo "<td>Ethnicity</td>";
	        	echo "<td>Home Zip</td>";
	        	echo "<td>School Zip</td>";
	        	echo "<td>Work Zip</td>";
	        	echo "<td>Cycling Frequency</td>";
	        	echo "<td>Rider History</td>";
	        	echo "<td>Rider Type</td>";
	        	echo "<td>Agreed to Contest</td>";

                	$row = $result->fetch_row();
                	while($row) {
                	        echo '<tr>';
                	        for ($i = 0; $i<count($row); $i++) {
                	                echo '<td>' . $row[$i] . '</td>';
                	        }
                	        echo '</tr>';
                	        $row = $result->fetch_row();
                	}
                	echo '</table>';
		}
	        break;
	    case "leaderboard":
        	$query = "SELECT * FROM leaderboard ORDER BY score DESC";
        	$result = mysqli_query($con, $query);

		if (!$result) {
                	printf("Error: %s\n", $con->error);
        	} else {
			echo "<b>Leaderboard:</b>";
			echo "<ul>Lists the people in order of highest riding score.</ul>";
			echo "<ul><b>Note: </b>It is calculated by multiplying the miles ridden by 10</ul>";

	    		echo '<table border="1">';
 	        	// This echo's the boarder and all of the names of the table
			// Displays table headers
	        	echo "<td>User ID</td>";
	        	echo "<td>Score</td>";
	        	echo "<td>Distance</td>";
	        	echo "<td>Device</td>";

                	$row = $result->fetch_row();
                	while($row) {
                	        echo '<tr>';
                	        for ($i = 0; $i<count($row); $i++) {
                	                echo '<td>' . $row[$i] . '</td>';
                	        }
                	        echo '</tr>';
                	        $row = $result->fetch_row();
                	}
                	echo '</table>';
		}
	        break;
	    default:
	        echo "No database selected";
	}
        mysqli_close($con);     // Closes the Database!!!
?>
</body>

</html>
