<?php

require_once('Database_dev.php');
require_once('AssetIssue.php');

class AssetIssueFactory
{
	static $class = 'AssetIssue';

	public static function insert( $trip_id, $recorded, $latitude, $longitude, $altitude=0, $speed=0, $hAccuracy=0, $vAccuracy=0, $type, $details, $photo )
	{
		$db = DatabaseConnectionFactory::getConnection();

		$query = "INSERT INTO assetissue ( trip_id, recorded, latitude, longitude, altitude, speed, hAccuracy, vAccuracy, type, details, photo ) VALUES ( '" .
				$db->escape_string( $trip_id ) . "', '" .
				$db->escape_string( $recorded ) . "', '" .
				$db->escape_string( $latitude ) . "', '" .
				$db->escape_string( $longitude ) . "', '" .
				$db->escape_string( $altitude ) . "', '" .
				$db->escape_string( $speed ) . "', '" .
				$db->escape_string( $hAccuracy ) . "', '" .
				$db->escape_string( $vAccuracy ) . "', '" .
				$db->escape_string( $type ) . "', '" .
				$db->escape_string( $details ) . "', '" .
				$db->escape_string( $photo ) . "' )";

		if ( $db->query( $query ) === true )
		{
			Util::log( __METHOD__ . "() added assetissue at {$recorded}, in ( {$latitude}, {$longitude} ), type {$type}, details {$details}, to trip $trip_id" );
			return true;
		}
		else
			Util::log( __METHOD__ . "() ERROR failed to added assetissue at {$recorded}, in ( {$latitude}, {$longitude} ), type {$type}, details {$details}, to trip $trip_id" );

		return false;
	}
}
