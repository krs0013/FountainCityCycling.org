<?php

require_once('Database.php');
require_once('Coord.php');

class CoordFactory
{
	static $class = 'Coord';
	
	public static function insert_bulk( $trip_id, $user_id, $coords ) {
		//Util::log( __METHOD__ . "() begin bulk insert of coord data PROTOCOL 3." );
		$db = DatabaseConnectionFactory::getConnection();
		
		$query_body = array();
		foreach( $coords as $coord){
			$query_body[] = "( '" . 
							$db->escape_string( $trip_id  ) . "', '" .
							$db->escape_string( $coord->r ) . "', '" .
							$db->escape_string( $coord->l ) . "', '" .
							$db->escape_string( $coord->n ) . "', '" .
							$db->escape_string( $coord->a ) . "', '" .
							$db->escape_string( $coord->s ) . "', '" .
							$db->escape_string( $coord->h ) . "', '" .
							$db->escape_string( $coord->v ) . "', '" .
							$db->escape_string( $user_id  ) . "' )";
		}
		if ( $coord && isset( $coord->r ) )
			$stop = $coord->r;
		
		if ( $db->query( "INSERT INTO coord ( trip_id, recorded, latitude, longitude, altitude, speed, hAccuracy, vAccuracy, user_id ) VALUES ".implode(", ", $query_body) ) === true )
		{
			//Util::log( __METHOD__ . "() added coord ( {$latitude}, {$longitude} ) to trip $trip_id" );
			Util::log( "INFO " . __METHOD__ . "() for trip {$trip_id} and user id {$user_id}, stopped at {$stop}" );		
			return $stop;
		}
		else
			Util::log( "ERROR " . __METHOD__ . "() failed to add coord ( {$latitude}, {$longitude} ) to trip $trip_id" );
		return false;
	}
	
	//to handle old data protocol.
	public static function insert_bulk_protocol_2( $trip_id, $coords ) {
		//Util::log( __METHOD__ . "() begin bulk insert of coord data PROTOCOL 2." );
		$db = DatabaseConnectionFactory::getConnection();
		
		$query_body = array();
		foreach( $coords as $coord){
			$query_body[] = "( '" . 
							$db->escape_string( $trip_id  ) . "', '" .
							$db->escape_string( $coord->rec ) . "', '" .
							$db->escape_string( $coord->lat ) . "', '" .
							$db->escape_string( $coord->lon ) . "', '" .
							$db->escape_string( $coord->alt ) . "', '" .
							$db->escape_string( $coord->spd ) . "', '" .
							$db->escape_string( $coord->hac ) . "', '" .
							$db->escape_string( $coord->vac ) . "', '" .
							$db->escape_string( $user_id  ) . "' )"; 
		}
		if ( $coord && isset( $coord->rec ) )
			$stop = $coord->rec;
		
		if ( $db->query( "INSERT INTO coord ( trip_id, recorded, latitude, longitude, altitude, speed, hAccuracy, vAccuracy, user_id ) VALUES ".implode(", ", $query_body) ) === true )
		{
			//Util::log( __METHOD__ . "() added coord ( {$latitude}, {$longitude} ) to trip $trip_id" );
			Util::log( "INFO " . __METHOD__ . "() for trip {$trip_id}, stopped at {$stop}" );
			return $stop;
		}
		else
			Util::log( "ERROR " . __METHOD__ . "() failed to add coord data to trip $trip_id" );
		return false;
	}

	public static function insert( $trip_id, $user_id, $recorded, $latitude, $longitude, $altitude=0, $speed=0, $hAccuracy=0, $vAccuracy=0 )
	{
		$db = DatabaseConnectionFactory::getConnection();
		
		$query = "INSERT INTO coord ( trip_id, recorded, latitude, longitude, altitude, speed, hAccuracy, vAccuracy, user_id ) VALUES ( '" .
				$db->escape_string( $trip_id ) . "', '" .
				$db->escape_string( $recorded ) . "', '" .
				$db->escape_string( $latitude ) . "', '" .
				$db->escape_string( $longitude ) . "', '" .
				$db->escape_string( $altitude ) . "', '" .
				$db->escape_string( $speed ) . "', '" .
				$db->escape_string( $hAccuracy ) . "', '" .
				$db->escape_string( $vAccuracy ) . "', '" .
				$db->escape_string( $user_id ) . "' )";

		if ( $db->query( $query ) === true )
		{
			//Util::log( __METHOD__ . "() added coord ( {$latitude}, {$longitude} ) to trip $trip_id" );
			return true;
		}
		else
			Util::log( "ERROR " . __METHOD__ . "() failed to add coord ( {$latitude}, {$longitude} ) to trip $trip_id" );

		return false;
	}

	// trip_id can be a single id, or an array of ids
	// if it's an array of ids, returns the result object directly because creating an
	// array of hundreds of thousands of Coord objects is memory-intensive and not useful
	public static function getCoordsByTrip( $trip_id )
	{
		$db = DatabaseConnectionFactory::getConnection();
		$coords = array();
		
		$latLongMinThreshold = 0.007; //delta must be more than 7m
		$latLongMaxThreshold = 0.067; //delta must be less than 67m (or whole trip is ignored).
		$query = "SELECT * FROM coord WHERE ";
	    if (is_array($trip_id)) {
	      $first = True;
	  		foreach ($trip_id as $idx => $single_trip_id ) {
	        	if ($first) {
					$first = False;
				} else {
					$query .= " OR ";
				}
				$query .= "trip_id='" . $db->escape_string($single_trip_id) . "'";
			}
		} else {
			$query .= "trip_id IN (" . $db->escape_string( $trip_id ) . ")";
		}
		//$query .= " ORDER BY trip_id ASC, recorded ASC";
/*
Util::log( __METHOD__ . "() with query of length " . strlen($query) . 
			': memory_usage = ' . memory_get_usage(True));
*/
		
		if ( ( $result = $db->query( $query ) ) && $result->num_rows )
		{
/*
		  Util::log( __METHOD__ . "() with query of length " . strlen($query) . 
				' returned ' . $result->num_rows .' rows: memory_usage = ' . memory_get_usage(True));
*/

			// if the request was for an array of trip_ids then just return the $result class
			// (I know, this is not very OO but putting it all in a structure in memory is no good either
			if (is_array($trip_id)) {
				return $result;			
			}
			$skipTrip = null;
			$last = null;
			while ( $coord = $result->fetch_object( self::$class ) ){
				/*
				Would be great to move this into the mysql query: on coords w/ same trip_id, if the delta between the points is
				more than the min threshold and less than the max threshold, return the point. If the delta is more than the max
				threshold, then remove the trip from the returned set completely.
				
				I suspect doing that here is slower than if i could do it directly in the query.
				*/
				if($skipTrip && $coord->trip_id == $skipTrip->trip_id){
				//no-op, this is a trip to skip
				}else{
					$skipTrip = null;
					if($last && $coord->trip_id == $last->trip_id){
						$latLongDistance = Util::latlongPointDistance($last->latitude, $last->longitude, $coord->latitude, $coord->longitude);
						if( $latLongDistance >= $latLongMinThreshold ){
							$coords[] = $coord;
						}
						if( $latLongDistance >= $latLongMaxThreshold ){
							$skipTrip = $coord;
							for($i=count($coords)-1;$i >=0 ; $i--){
								//start at the end of the array, remove items until we get to the previous trip
								if($coords[$i]->trip_id == $skipTrip->trip_id){
									array_pop($coords);	
								}else{
									break;
								}
							}
						}	
					}else{ 
						$coords[] = $coord;						
					}
				}
				$last = $coord;
			}
			$result->close();
		}
		$result = null;
		Util::log( "INFO " . __METHOD__ . "() with query of length " . strlen($query) . ' RET2: memory_usage = ' . memory_get_usage(True));

		return json_encode($coords);
	}
	
	public static function getAllCoordsByTrip( $trip_id )
  // original get function w/ no coor filtering
	{
		$db = DatabaseConnectionFactory::getConnection();
		$coords = array();
		$query = "SELECT * FROM coord WHERE ";
	    if (is_array($trip_id)) {
	      $first = True;
	  		foreach ($trip_id as $idx => $single_trip_id ) {
	        	if ($first) {
					$first = False;
				} else {
					$query .= " OR ";
				}
				$query .= "trip_id IN (" . $db->escape_string($single_trip_id) . ")";
			}
		} else {
			$query .= "trip_id IN (" .  $trip_id  . ")";
		}
		$query .= " ORDER BY trip_id ASC, recorded ASC";
		
		Util::log( "INFO " . __METHOD__ . "() with query of length " . strlen($query) . ': memory_usage = ' . memory_get_usage(True));

		if ( ( $result = $db->query( $query ) ) && $result->num_rows )
		{
		  Util::log( "INFO " . __METHOD__ . "() with query of length " . strlen($query) . 
				' returned ' . $result->num_rows .' rows: memory_usage = ' . memory_get_usage(True));

			// if the request was for an array of trip_ids then just return the $result class
			// (I know, this is not very OO but putting it all in a structure in memory is no good either
			// cL note: not clear this will work over JSON.
			if (is_array($trip_id)) {
				return $result;
			}

			while ( $coord = $result->fetch_object( self::$class ) )
				$coords[] = $coord;

			$result->close();
		}
		Util::log( "INFO " . __METHOD__ . "() with query of length " . strlen($query) . 
			' RET2: memory_usage = ' . memory_get_usage(True));

//		return $coords;
		return json_encode($coords);
	}
}
