<?php
global $catalog_systems ;
$catalog_systems['cedar_auth'] = "CedarAuth" ;

require_once( "rstfl/AbstractCatalog.php" ) ;

class CedarAuth extends AbstractCatalog
{
    public function CedarAuth()
    {
	$this->catalog_functions['cedar_login'] = 'cedar_login' ;
	$this->catalog_functions['cedar_auth'] = 'cedar_auth' ;
	$this->catalog_functions['cedar_session'] = 'cedar_session' ;
    }

    private function authconnect()
    {
	return parent::dbconnect( "localhost:3306", "wikiuser", "tys-jan-yikph-ut", "wikidb" ) ;
    }

    protected function cedar_session()
    {
	if( $_SERVER["HTTPS"] != "on" )
	{
	    print( "Access to this page restricted, must use https\n" ) ;
	    return ;
	}

	$username=$_REQUEST["username"] ;
	if( !$username || $username == "" )
	{
	    print( "username has not been specified. Please try again\n" ) ;
	    return ;
	}

	$sid=$_REQUEST["token"] ;
	if( !$sid || $sid == "" )
	{
	    print( "user token has not been specified. Please try again\n" ) ;
	    return ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->authconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    return ;
	}

	$username = ucfirst( mysql_real_escape_string( $username ) ) ;
	$sid = ucfirst( mysql_real_escape_string( $sid ) ) ;

	// Now modify the entry to the session table for this user using the
	// token.
	$remote_addr = $_SERVER["REMOTE_ADDR"] ;
	$query = "UPDATE cedar_sessions SET token='$sid' WHERE user_name = '$username' AND TIMESTAMPDIFF(SECOND,last_heart_beat,NOW()) <= 86400 AND client_ip = '$remote_addr'" ;
	$insert_result = mysql_query( $query ) ;
	if( !$insert_result )
	{
	    print( "Failed to update session entry" . mysql_error() ) ;
	    parent::dbclose( $result ) ;
	    return ;
	}

	parent::dbclose( $result ) ;
    }

    protected function cedar_auth()
    {
	global $auth_result ;

	if( $_SERVER["HTTPS"] != "on" )
	{
	    print( "Access to this page restricted, must use https\n" ) ;
	    return ;
	}

	$this->cedar_login() ;
	print( "$auth_result\n" ) ;
	return ;
    }

    /*
    */
    protected function cedar_login()
    {
	global $auth_result ;
	$auth_result = "BAD" ;

	$username=$_REQUEST["username"] ;
	if( !$username || $username == "" )
	{
	    $auth_result = "username has not been specified. Please try again" ;
	    return ;
	}

	$password=$_REQUEST["password"] ;
	if( !$password || $password == "" )
	{
	    $auth_result = "password has not been specified. Please try again" ;
	    return ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->authconnect() ;
	if( $isconnected != "good" )
	{
	    $auth_result = $isconnected ;
	    return ;
	}

	$username = mysql_real_escape_string( $username ) ;
	$cusername = ucfirst( $username ) ;
	$password = mysql_real_escape_string( $password ) ;

	$query = "SELECT u.user_password FROM user u,user_groups g,cedar_user_info c WHERE u.user_name = '$cusername' AND u.user_id = g.ug_user AND g.ug_group = 'Cedar' AND u.user_id = c.user_id AND c.status = 'active'" ;

	$result = parent::dbquery( $query ) ;
	$num_rows = mysql_num_rows( $result ) ;
	if( $num_rows != 1 )
	{
	    if( $num_rows == 0 )
	    {
		$auth_result = "User name $username does not exist." ;
		parent::dbclose( $result ) ;
		return ;
	    }
	    $auth_result = "User name $username has multiple entries." ;
	    parent::dbclose( $result ) ;
	    return ;
	}
	$dbpassword = mysql_fetch_row( $result ) ;
	$dbpassword = $dbpassword[0] ;

	list( $salt, $realHash ) = explode( ':', substr( $dbpassword, 3 ), 2 );
	$totest = md5( $salt.'-'.md5( $password ) ) ;

	if( $totest != $realHash )
	{
	    $auth_result = "Incorrect password for user $username" ;
	    parent::dbclose( $result ) ;
	    return ;
	}
	$auth_result = "good" ;


	// delete any rows for this user more that 24 hours old using
	// last_heart_beat
	$query = "DELETE FROM cedar_sessions where user_name = '$username' AND TIMESTAMPDIFF(SECOND,last_heart_beat,NOW()) > 86400" ;
	mysql_query( $query ) ;

	// Now add an entry to the session table.
	$remote_addr = $_SERVER["REMOTE_ADDR"] ;
	$query = "INSERT INTO cedar_sessions (user_name, client_ip ) VALUES ('$username', '$remote_addr' )" ;
	$insert_result = mysql_query( $query ) ;
	if( !$insert_result )
	{
	    $auth_result = "Failed to create session entry" . mysql_error() ;
	    parent::dbclose( $result ) ;
	    return ;
	}

	$auth_result = "good" ;

	parent::dbclose( $result ) ;
    }
} ;
?>
