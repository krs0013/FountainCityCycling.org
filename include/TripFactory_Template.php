<?php

require_once('Database.php');
require_once('Trip.php');

class TripFactory
{
	static $class = 'Trip';

	public static function insert( $user_id, $purpose, $notes, $start, $distance, $cotwo, $kcal, $avgCost, $points )
	{
		$db = DatabaseConnectionFactory::getConnection();

		$query = "INSERT INTO trip ( user_id, purpose, notes, start, distance, co_two, kcal, avg_cost, points ) VALUES ( '" .
				$db->escape_string( $user_id ) . "', '" .
				$db->escape_string( $purpose ) . "', '" .
				$db->escape_string( $notes ) . "', '" .
				$db->escape_string( $start ) . "', '" .
				$db->escape_string( $distance ) . "', '" .
				$db->escape_string( $cotwo ) . "', '" .
				$db->escape_string( $kcal ) . "', '" .
				$db->escape_string( $avgCost ) . "', '" .
				$db->escape_string( $points ) . "' )";

		if ( ( $db->query( $query ) === true ) &&
			 ( $id = $db->insert_id ) )
		{
			Util::log( "INFO " . __METHOD__ . "() created new trip {$id} for user {$user_id}, start {$start}, {$purpose}: {$notes}" );
			Util::log( "**EXTRA** **EXTRA** INFO " . __METHOD__ . "() created new trip extras for distance {$distance}, CO2 {$cotwo}, KCal {$kcal}, Avg Cost {$avgCost}, and Points {$points}" );
			return self::getTrip( $id );

		}
		else
			Util::log( "ERROR " . __METHOD__ . "() failed to create new trip for user {$user_id}, start {$start}, {$purpose}: {$notes}" );
			Util::log( "ERROR " . __METHOD__ . "() failed to create EXTRAS for distance {$distance}, CO2 {$cotwo}, KCal {$kcal}, Avg Cost {$avgCost}, and Points {$points}" );

		return false;
	}

	/**
	 * Updates the user's total score and distance in the leaderboard DB 
	 */
	public static function updateLeaderboard( $user_id, $score, $distance, $device ) {

		$db = DatabaseConnectionFactory::getConnection();

		$DBHost = "<enter_db_host_here>";
        	$DBUser = "<enter_db_username_here>";
        	$DBPass = "<enter_db_password_here>";
        	$DBName = "<enter_db_name_here>";

        	/* Connect to the server and put it into the con varibale */
        	$con = mysqli_connect($DBHost,$DBUser,$DBPass,$DBName) or die ("Unable to connect to server");

		/* Checks to make sure the table is there */
		$initialDistance = "SELECT distance FROM leaderboard WHERE user_id='" . $db->escape_string( $user_id ) . "'";
		$distanceResult = mysqli_query($con, $initialDistance);	// Store the result here

		$initialScore = "SELECT score FROM leaderboard WHERE user_id='" . $db->escape_string( $user_id ) . "'";
		$scoreResult = mysqli_query($con, $initialScore);	// Store result here


		Util::log( "Initial Distance Query: {$initialDistance}, Initial Score Query: {$initialScore}" );

		$numScoreRows = $scoreResult->num_rows;			// Get number of score rows with that id
		$numDistanceRows = $distanceResult->num_rows;		// Get number of distance rows with that id

		if ($numScoreRows > 0 && $numDistanceRows > 0) {	// If both have a row, do this

			$query = "UPDATE leaderboard SET " .
					"distance = distance + '" . $db->escape_string( $distance ) . 
					"', score = score + '" . $db->escape_string( $score ) . 
					"' WHERE user_id='" . $db->escape_string( $user_id ) . "'";
			$updateResult = mysqli_query($con, $query);

			if (!$updateResult) {
				Util::log( "***UPDATE ERROR*** Could not update New Distance and Score {$distance}, {$score}, with device {$device}" );
				Util::log( "***UPDATE ERROR*** Query: {$query}" );
				return false;
        		} else {
				Util::log( "***UPDATE SUCCESS*** Newly Recorded Distance {$distance}, Newly Recorded Score {$score}, device used {$device}" );
				Util::log( "***UPDATE SUCCESS*** Query: {$query}" );
				return self::getTrip( $id );
			}
		}
		else {							// If no rows, create a row and add these values into it.
			
			$query = "INSERT INTO leaderboard ( user_id, score, distance, device ) VALUES ( '" .
					$db->escape_string( $user_id ) . "', '" .
					$db->escape_string( $score ) . "', '" .
					$db->escape_string( $distance ) . "', '" .
					$db->escape_string( $device ) . "' )";
			
			if ( ( $db->query( $query ) === true ) && ( $id = $db->insert_id ) ) {
				Util::log( "***UPDATE WENT TO INSERT*** Inserted new leaderboard row with New Distance and Score {$distance}, {$score}" );
				return self::getTrip( $id );
			} else 
				Util::log( "***UPDATE WENT TO INSERT WITH ERROR*** " . __METHOD__ . "() failed to create new leaderboard for user {$user_id}, New Distance {$distance}, New Score {$distance}" );

			return false;

		}
	}

	/* Returns a Trip object for the given tripid */ 
	public static function getTrip( $id )
	{
		$db = DatabaseConnectionFactory::getConnection();
		$trip = null;

		if ( ( $result = $db->query( "SELECT * FROM trip WHERE id='" . $db->escape_string( $id ) . "'" ) ) &&
				( $result->num_rows ) )
		{
			$trip = $result->fetch_object( self::$class );
			$result->close();
		}

		return $trip;
	}
  
	/**
	* Returns an array of trip ids within the given bounding box. 
	* Returns null for error.
	* Returns an empty array if no Trips are found.
	**/
	public static function getTripsByBoundingBox( $lat_center, $lat_maxdist, 
		$long_center, $long_maxdist) 
	{
		$query = "SELECT distinct trip_id from coord where latitude>=" . ($lat_center-$lat_maxdist);
		$query .= " and latitude<=" . ($lat_center+$lat_maxdist);
		$query .= " and longitude>=" . ($long_center-$long_maxdist);
		$query .= " and longitude<=" . ($long_center+$long_maxdist);
		$db = DatabaseConnectionFactory::getConnection();
		$result = $db->query($db->escape_string($query));

		// no result, empty array
		if ($result->num_rows == 0) { return array(); }

		$trips = array();
		while ($trip_id = $result->fetch_array())
		{
			$trips[] = $trip_id['trip_id'];
		}

		$result->close();
		return $trips;
	}

	public static function getTripByUserStart( $user_id, $start )
	{
		$db = DatabaseConnectionFactory::getConnection();
		$trip = null;

		$query = "SELECT * FROM trip WHERE user_id='" . $db->escape_string( $user_id ) . "' AND " .
				 "start='" . $db->escape_string( $start ) . "'";

		if ( ( $result = $db->query( $query ) ) &&
				( $result->num_rows ) )
		{
			$trip = $result->fetch_object( self::$class );
			$result->close();
		}

		return $trip;
	}

	/**
	* Given a trip_id, returns the age, gender, homeZIP, schoolZIP, workZIP and
	* cycling frequency as an associative array.  Returns null if nothing found.
	*/
	public static function getTripAttrsByTrips($trip_id) {
		$db = DatabaseConnectionFactory::getConnection();
		$query = "select trip.id,user.id,age,gender,homezip,schoolzip,workzip,cycling_freq.text,purpose,device from trip LEFT JOIN (user,cycling_freq) on (user.id=trip.user_id AND user.cycling_freq=cycling_freq.id) where trip.id='" . $db->escape_string($trip_id) . "' ORDER BY trip.id ASC";

		if ( ( $result = $db->query( $query ) ) && $result->num_rows )
		{
			$toret = $result->fetch_array();
			$result->close();
			return $toret;
		}
		return null;
 	}

	public static function update( $id, $stop, $n_coord )
	{
		$db = DatabaseConnectionFactory::getConnection();

		$query = "UPDATE trip SET " .
				 "stop='" . $db->escape_string( $stop ) . "', " .
				 "n_coord='" . $db->escape_string( $n_coord ) . "' " .
				 "WHERE id='" . $db->escape_string( $id ) . "' LIMIT 1";

		if ( $db->query( $query ) ) 
		{
			Util::log( "INFO " . __METHOD__ . "() updated trip {$id}" );
			return self::getTrip( $id );
		}
		else
			Util::log( "ERROR " . __METHOD__ . "() failed to update trip {$id}: {$query}" );

		return false;
	}
	
	public static function getTripAttrsByFilteredUser($filterByDemographics, $filterByPurpose){
		$db = DatabaseConnectionFactory::getConnection();
		
		$trip_ids = array();

		$query = "SELECT trip.id,age,gender,ethnicity,rider_type,cycling_freq.text,purpose FROM trip LEFT JOIN (user,cycling_freq) ON (user.id=trip.user_id AND user.cycling_freq=cycling_freq.id) WHERE trip.id IN (SELECT trip.id FROM trip WHERE user_id IN(SELECT user.id FROM user " . $db->escape_string($filterByDemographics) . ") ) AND n_coord>'120' AND purpose IN (" . $filterByPurpose . ") ORDER BY trip.id ASC";

		Util::log(  "INFO " . __METHOD__ . "() with query: {$query}" );
		$result = $db->query( $query );		
		while ( $trip = $result->fetch_array())
				$trip_ids[] = $trip;

		$result->close();
		 
		return json_encode($trip_ids);
	}
	
	public static function getTripsByUser($user){
		$db = DatabaseConnectionFactory::getConnection();
		$trips = array();

		$query = "SELECT * FROM trip WHERE user_id={$user}";

		$result = $db->query( $query );		
		while ( $trip = $result->fetch_object( self::$class ) )
				$trips[] = $trip;

		$result->close();
		 
		return $trips;
	}
	
	public static function getTripIds(){
		$db = DatabaseConnectionFactory::getConnection();
		$trip_ids = array();

		$query = "SELECT id FROM trip";

		$result = $db->query( $query );		
		while ( $trip = $result->fetch_object( self::$class ) )
				$trip_ids[] = $trip;

		$result->close();
		 
		return json_encode($trip_ids);
	}
	
	public static function getTripIdsByNotes($tag){
		$db = DatabaseConnectionFactory::getConnection();
		$trip_ids = array();

		$query = "SELECT * FROM trip WHERE notes LIKE '%{$tag}%'";

		$result = $db->query( $query );		
		while ( $trip = $result->fetch_object( self::$class ) )
				$trip_ids[] = $trip;

		$result->close();
		 
		return json_encode($trip_ids);
	}
	
	public static function getTripNumsByMonth(){
		$db = DatabaseConnectionFactory::getConnection();
		$tripNums = array();

		$query = "SELECT DATE_FORMAT(start, '%Y') as 'year',
					DATE_FORMAT(start, '%m') as 'month',
					COUNT(id) as 'total',
					id
					FROM trip
					GROUP BY DATE_FORMAT(start, '%Y%m')";

		$result = $db->query( $query );		
		while ( $trip = $result->fetch_object( self::$class )  )
				$tripNums[] = $trip;

		$db->close();
		 
		return json_encode($tripNums);
	}
	
	public static function getUserNumsByTrip(){
		$db = DatabaseConnectionFactory::getConnection();
		$tripNums = array();

		$query = "SELECT DATE_FORMAT(start, '%Y') as 'year',
					DATE_FORMAT(start, '%m') as 'month',
					COUNT(id) as 'total',
					COUNT(DISTINCT user_id) as 'users',
					id
					FROM trip
					GROUP BY DATE_FORMAT(start, '%Y%m')";

		$result = $db->query( $query );		
		while ( $trip = $result->fetch_object( self::$class )  )
				$tripNums[] = $trip;

		$db->close();
		 
		return json_encode($tripNums);
	}
	
	public static function getRidePurposeNumsByCategory(){
		$db = DatabaseConnectionFactory::getConnection();
		$ridePurposeNums = array();

		$query = "SELECT purpose,
					COUNT(id) as 'total'
					FROM trip
					GROUP BY purpose";

		$result = $db->query( $query );		
		while ( $user = $result->fetch_object( self::$class )  )
				$ridePurposeNums[] = $user;

		$db->close();
		 
		return json_encode($ridePurposeNums);
	}
}
