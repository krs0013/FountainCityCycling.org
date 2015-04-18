<?php

require_once('Database.php');
require_once('User.php');

class UserFactory
{
	static $class = 'User';

	public static function insert( $device )
	{
		$db = DatabaseConnectionFactory::getConnection();

		$query = "INSERT INTO user ( device ) VALUES ( '" .
				$db->escape_string( $device ) . "' )";

		if ( ( $db->query( $query ) === true ) &&
			 ( $id = $db->insert_id ) )
		{
			Util::log( "INFO " . __METHOD__ . "() created new user {$id} for device {$device}" );
			return self::getUser( $id );
		}
		else
			Util::log( "ERROR " . __METHOD__ . "() failed to create new user for device {$device}" );

		return false;
	}

	public static function getUser( $id )
	{
		$db = DatabaseConnectionFactory::getConnection();
		$user = null;

		if ( ( $result = $db->query( "SELECT * FROM user WHERE id='" . $db->escape_string( $id ) . "'" ) ) &&
				( $result->num_rows ) )
		{
			$user = $result->fetch_object( self::$class );
			$result->close();
		}

		return $user;
	}

	public static function getUserByDevice( $device )
	{
		$db = DatabaseConnectionFactory::getConnection();
		$user = null;

		if ( ( $result = $db->query( "SELECT * FROM user WHERE device='" . $db->escape_string( $device ) . "'" ) ) &&
				( $result->num_rows ) )
		{
			$user = $result->fetch_object( self::$class );
			$result->close();
		}

		return $user;
	}

	/**
	* @desc update user record identified by $old with diffs in $new
	* @param User $old object instantiated from current DB record for user
	* @param User $new object instantiated from client data
	*/
	public static function update( User $old, User $new )
	{
		$db = DatabaseConnectionFactory::getConnection();

		$update = '';
		
		$noAppVersion = true;
		//$fields = (array) $old;
		foreach ( $new->getPersonalInfo() as $key => $value )
		{
			// only update values if non-null or '0'

			if ( !empty( $value ) || is_numeric( $value ) )
			{
				//and only if it's not the email address, handle that elsewhere
				//if($key != 'email'){
				Util::log( "INFO " . __METHOD__ . "() updating {$key}\t=> '{$value}'" );
				if ( !empty( $update ) )
					$update .= ', ';

				$update .= "{$key}='" . $db->escape_string( $value ) . "'";
				//}
				if($key == 'email'){
					//add the email address to the email table as well
					self::addEmail( $value );						
					
				}
				if($key == 'app_version'){
					$noAppVersion = false;
				}			
			}
		}
		if($noAppVersion){
			if ( !empty( $update ) )
					$update .= ', ';
			$update .= "app_version='1.0'";
		}

		// Update Contest Agreement if it has changed.
		self::updateAgreement( $old->id, $new->agree );

		// sanity check - ensure we have at least one field to update
		// and a valid user.id to work with
		if ( $update && isset( $old->id ) && $old->id )
		{
			// build update query
			$query = "UPDATE user SET {$update} WHERE id='" . $db->escape_string( $old->id ) . "' LIMIT 1";

			if ( $db->query( $query ) ) 
			{
				Util::log( "INFO " . __METHOD__ . "() updated user {$old->id}:" );
				//Util::log( $query );
				return self::getUser( $old->id );
			}
			else
				Util::log( "ERROR " . __METHOD__ . "() failed to update user {$old->id}" );
		}
		else
			Util::log( "INFO " . __METHOD__ . "() nothing to do" );

		return false;
	}
	
	public static function addEmail( $email )
	{
		$db = DatabaseConnectionFactory::getConnection();
//		$query = "INSERT into email (email_address) VALUES ('".$db->escape_string( $email )."')";
		$query = "INSERT into email (email_address) SELECT '".$db->escape_string( $email )."' FROM email WHERE email_address='".$db->escape_string( $email )."' HAVING COUNT(*)=0";
		if ( $db->query( $query ) ) 
		{
			Util::log( "INFO " . __METHOD__ . "() added email {$email}:" );
			//Util::log( $query );			
		}
		else
			Util::log( "ERROR " . __METHOD__ . "() failed to add email {$email}" );		
	}
	
	public static function updateAgreement( $id, $agree )
	{
		$db = DatabaseConnectionFactory::getConnection();
		$query = "UPDATE user SET agree='".$db->escape_string( $agree )."' WHERE id='".$db->escape_string( $id )."'";

		if ( $db->query( $query ) ) 
		{
			Util::log( "INFO " . __METHOD__ . "() updated Contest Agreement {$agree}, ID {$id}" );
			//Util::log( $query );			
		}
		else
			Util::log( "ERROR " . __METHOD__ . "() failed to update Contest Agreement {$agree}, ID {$id}" );		
	}
	
	public static function getUserNumsByMonth(){
		$db = DatabaseConnectionFactory::getConnection();
		$userNums = array();

		$query = "SELECT DATE_FORMAT(created, '%Y') as 'year',
					DATE_FORMAT(created, '%m') as 'month',
					COUNT(id) as 'total',
					id
					FROM user
					GROUP BY DATE_FORMAT(created, '%Y%m')";

		$result = $db->query( $query );		
		while ( $user = $result->fetch_object( self::$class )  )
				$userNums[] = $user;

		$db->close();
		 
		return json_encode($userNums);
	}
	
	public static function getAgeNumsByCategory(){
		$db = DatabaseConnectionFactory::getConnection();
		$ageNums = array();

		$query = "SELECT age,
					COUNT(id) as 'total'
					FROM user
					GROUP BY age";

		$result = $db->query( $query );		
		while ( $user = $result->fetch_object( self::$class )  )
				$ageNums[] = $user;

		$db->close();
		 
		return json_encode($ageNums);
	}
	
	public static function getGenderNumsByCategory(){
		$db = DatabaseConnectionFactory::getConnection();
		$genderNums = array();

		$query = "SELECT gender,
					COUNT(id) as 'total'
					FROM user
					GROUP BY gender";

		$result = $db->query( $query );		
		while ( $user = $result->fetch_object( self::$class )  )
				$genderNums[] = $user;

		$db->close();
		 
		return json_encode($genderNums);
	}
	
	public static function getEthnicityNumsByCategory(){
		$db = DatabaseConnectionFactory::getConnection();
		$ethnicityNums = array();

		$query = "SELECT ethnicity,
					COUNT(id) as 'total'
					FROM user
					GROUP BY ethnicity";

		$result = $db->query( $query );		
		while ( $user = $result->fetch_object( self::$class )  )
				$ethnicityNums[] = $user;

		$db->close();
		 
		return json_encode($ethnicityNums);
	}
	
	public static function getIncomeNumsByCategory(){
		$db = DatabaseConnectionFactory::getConnection();
		$incomeNums = array();

		$query = "SELECT income,
					COUNT(id) as 'total'
					FROM user
					GROUP BY income";

		$result = $db->query( $query );		
		while ( $user = $result->fetch_object( self::$class )  )
				$incomeNums[] = $user;

		$db->close();
		 
		return json_encode($incomeNums);
	}
	
	public static function getRiderFrequencyNumsByCategory(){
		$db = DatabaseConnectionFactory::getConnection();
		$riderFrequencyNums = array();

		$query = "SELECT cycling_freq,
					COUNT(id) as 'total'
					FROM user
					GROUP BY cycling_freq";

		$result = $db->query( $query );		
		while ( $user = $result->fetch_object( self::$class )  )
				$riderFrequencyNums[] = $user;

		$db->close();
		 
		return json_encode($riderFrequencyNums);
	}
	
	public static function getRiderTypeNumsByCategory(){
		$db = DatabaseConnectionFactory::getConnection();
		$riderTypeNums = array();

		$query = "SELECT rider_type,
					COUNT(id) as 'total'
					FROM user
					GROUP BY rider_type";

		$result = $db->query( $query );		
		while ( $user = $result->fetch_object( self::$class )  )
				$riderTypeNums[] = $user;

		$db->close();
		 
		return json_encode($riderTypeNums);
	}
	
	public static function getRiderHistoryNumsByCategory(){
		$db = DatabaseConnectionFactory::getConnection();
		$riderHistoryNums = array();

		$query = "SELECT rider_history,
					COUNT(id) as 'total'
					FROM user
					GROUP BY rider_history";

		$result = $db->query( $query );		
		while ( $user = $result->fetch_object( self::$class )  )
				$riderHistoryNums[] = $user;

		$db->close();
		 
		return json_encode($riderHistoryNums);
	}
	
	public static function getAppVersionNumsByCategory(){
		$db = DatabaseConnectionFactory::getConnection();
		$appVersionNums = array();

		$query = "SELECT app_version,
					COUNT(id) as 'total',
					id
					FROM user
					GROUP BY app_version";

		$result = $db->query( $query );		
		while ( $user = $result->fetch_object( self::$class )  )
				$appVersionNums[] = $user;

		$db->close();
		 
		return json_encode($appVersionNums);
	}
}
