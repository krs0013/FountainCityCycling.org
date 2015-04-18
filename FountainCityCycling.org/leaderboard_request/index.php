<?php

require_once('Database.php');
require_once('Util.php');

    $DBHost = "mysql.fountaincitycycling.org";
    $DBUser = "krs0013_user";
    $DBPass = "databaseFountainCity";
    $DBName = "cycling_data_db";

    /* Connect to the server and put it into the con varibale */
    $con = mysqli_connect($DBHost,$DBUser,$DBPass,$DBName) or die ("Unable to connect to database");
 
    if ($con) {

        //Select records from the database
        $query = "SELECT * FROM leaderboard ORDER BY score DESC";
	$result = mysqli_query($con, $query);	// Store result here
		
	$leaderRows = $result->num_rows;		// Get number of distance rows with that id

	if ($leaderRows > 0) {
		// looping through all results
    		// leaders node
    		$response["leaders"] = array();

		$row = $result->fetch_row();
 
    		while ($row) {
        		// temp user array
        		$product = array();
        		$product["user_id"] = $row[0];
        		$product["score"] = $row[1];
        		$product["distance"] = $row[2];
			$product["device"] = $row[3];
				
			Util::log( "Within While Loop: User ID array: {$row["user_id"]}" );
 
        		// push single product into final response array
        		array_push($response["leaders"], $product);
	                $row = $result->fetch_row();
    		}
				
		Util::log( "---- Created product! ---- Product: {$product}" );

    		// success
    		$response["success"] = 1;
 
    		// echoing JSON response
    		echo json_encode($response);

	} else {
            	//Return error
            	$response["success"] = 0;
            	$response["error"] = 1;
            	$response["error_msg"] = "User could not be found";
            	echo json_encode($response);
	}
    } else {
        $response["success"] = 0;
       	$response["error"] = 1;
        $response["error_msg"] = "No leaders found";
	echo json_encode($response);
    }
?>