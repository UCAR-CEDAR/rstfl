<?php
class AbstractCatalog
{
    protected $catalog_functions = array();
    protected $dbh ;

    public function AbstractCatalog()
    {
    }

    public function execute( $requested )
    {
	$requested_function = $this->catalog_functions[$requested] ;
	if( !$requested_function )
	{
	    print( "Request $requested on CEDAR Catalog does not exist<BR>\n" ) ;
	    exit( 1 ) ;
	}

	$this->$requested_function() ;
    }

    protected function dbconnect( $host, $username, $passwd, $database )
    {
	$this->dbh = mysql_connect( $host, $username, $passwd ) ;
	if( !$this->dbh )
	{
	    return "Unable to connect to database: " . mysql_error() ;
	    return false ;
	}

	if( !mysql_select_db( $database ) )
	{
	    $this->dbclose() ;
	    return "Unable to select database $database: " . mysql_error() ;
	}

	return "good" ;
    }

    protected function dbclose( $result )
    {
	if( $result ) mysql_free_result( $result ) ;
	if( $this->dbh ) mysql_close( $this->dbh ) ;
    }

    protected function dbquery( $query )
    {
	$result = mysql_query( $query ) ;
	if( !$result )
	{
	    print( "Query failed:\n " ) ;
	    print( "$query\n" ) ;
	    print( mysql_error() . "\n" ) ;
	    $this->dbclose( $result ) ;
	    exit( 0 ) ;
	}

	return $result ;
    }
} ;
?>
