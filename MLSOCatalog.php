<?php
global $catalog_systems ;
$catalog_systems['mlso'] = "MLSOCatalog" ;

class MLSOCatalog extends AbstractCatalog
{
    public function MLSOCatalog()
    {
	$this->catalog_functions['products'] = 'getDataProducts' ;
	$this->catalog_functions['days'] = 'getDays' ;
	$this->catalog_functions['months'] = 'getMonths' ;
	$this->catalog_functions['years'] = 'getYears' ;
	$this->catalog_functions['instruments'] = 'getInstruments' ;
	$this->catalog_functions['parameters'] = 'getParameters' ;
	$this->catalog_functions['files'] = 'getFiles' ;
    }

    private function mlsoconnect()
    {
	return parent::dbconnect( "db.hao.ucar.edu","acos","generic","ACOS" ) ;
    }

    // Needs start year, start date, end date, and instrument id
    protected function getDataProducts()
    {
	$startyear = $_REQUEST['startyear'] ;
	if( !$startyear )
	{
	    print( "Data product query failed, start year not specified\n" ) ;
	    exit( 0 ) ;
	}
	$startdate = $_REQUEST['startdate'] ;
	if( !$startdate )
	{
	    print( "Data product query failed, start date not specified\n" ) ;
	    exit( 0 ) ;
	}
	$enddate = $_REQUEST['enddate'] ;
	if( !$enddate )
	{
	    print( "Data product query failed, end date not specified\n" ) ;
	    exit( 0 ) ;
	}
	$instrument = $_REQUEST['instrument'] ;
	if( !$instrument )
	{
	    print( "Data product query failed, instrument id not specified\n" ) ;
	    exit( 0 ) ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->mlsoconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$startyear = mysql_real_escape_string( trim( $startyear ) ) ;
	$startdate = mysql_real_escape_string( trim( $startdate ) ) ;
	$enddate = mysql_real_escape_string( trim( $enddate ) ) ;
	$instrument = mysql_real_escape_string( trim( $instrument ) ) ;

	$query = "SELECT DISTINCT WAVE_LENGTH as wave_length," ;
	$query .= " QUALITY as quality, PROCESSING as processing" ;
	$query .= " FROM tbl_$startyear" ;
	$query .= " WHERE (datetime_obs >= '$startdate')" ;
	$query .= " AND (datetime_obs < '$enddate')" ;
	$query .= " AND (INSTRUMENT = '$instrument')" ;
	$query .= " ORDER BY datetime_obs ASC, type DESC" ;

	//print( "$query<BR>\n" ) ;

	$result = parent::dbquery( $query ) ;
	$num_rows = mysql_num_rows( $result ) ;
	if( $num_rows != 0 )
	{
	    while( $line = mysql_fetch_row( $result ) )
	    {
		if( $line )
		{
		    $colnum = 0 ;
		    foreach( $line as $value )
		    {
			if( $colnum > 0 ) echo "," ;
			echo $value ;
			$colnum++ ;
		    }
		    echo "\n" ;
		}
	    }
	}

	parent::dbclose( $result ) ;
    }

    // Need year, month and max day of month
    // Optional parameters
    protected function getDays()
    {
	$year = $_REQUEST['year'] ;
	if( !$year )
	{
	    print( "Day query failed, year not specified\n" ) ;
	    exit( 0 ) ;
	}

	$month = $_REQUEST['month'] ;
	if( !$month )
	{
	    print( "Day query failed, month not specified\n" ) ;
	    exit( 0 ) ;
	}

	$maxday = $_REQUEST['maxday'] ;
	if( !$maxday )
	{
	    print( "Day query failed, max day of month not specified\n" ) ;
	    exit( 0 ) ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->mlsoconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$year = mysql_real_escape_string( trim( $year ) ) ;
	$month = mysql_real_escape_string( trim( $month ) ) ;

	$parameters = $_REQUEST['params'] ;
	if( $parameters )
	{
	    $parameters = mysql_real_escape_string( trim( $parameters ) ) ;
	}

	$query = "SELECT DISTINCT EXTRACT(day from NUM_ID) as day FROM tbl_numfiles WHERE" ;
	if( $parameters )
	{
	    $query .= " (" ;
	    $param_array = explode( ",", $parameters ) ;
	    $isfirst = true ;
	    foreach( $param_array as $param )
	    {
		$param = strtoupper( trim( $param ) ) ;
		if( !$isfirst )
		{
		    $query .= " OR" ;
		}
		$isfirst = false ;
		$query .= " $param > 0" ;
	    }
	    $query .= ") " ;
	}
	if( $parameters )
	{
	    $query .= " AND" ;
	}
	$query .= " (NUM_ID >= '$year-$month-01 00:00:00')" ;
	$query .= " AND (NUM_ID <= '$year-$month-$maxday 23:59:59')" ;
	$query .= " ORDER BY day ASC" ;

	//print( "$query<BR>\n" ) ;

	$result = parent::dbquery( $query ) ;
	$num_rows = mysql_num_rows( $result ) ;
	if( $num_rows != 0 )
	{
	    while( $line = mysql_fetch_row( $result ) )
	    {
		if( $line )
		{
		    $colnum = 0 ;
		    foreach( $line as $value )
		    {
			if( $colnum > 0 ) print( "," ) ;
			print( $value ) ;
			$colnum++ ;
		    }
		    print( "\n" ) ;
		}
	    }
	}

	parent::dbclose( $result ) ;
    }

    // Optional parameters
    protected function getMonths()
    {
	$year = $_REQUEST['year'] ;
	if( !$year )
	{
	    print( "Month query failed, year not specified\n" ) ;
	    exit( 0 ) ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->mlsoconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$year = mysql_real_escape_string( trim( $year ) ) ;

	$parameters = $_REQUEST['params'] ;
	if( $parameters )
	{
	    $parameters = mysql_real_escape_string( trim( $parameters ) ) ;
	}

	$query = "SELECT DISTINCT EXTRACT(month from NUM_ID) as month FROM tbl_numfiles WHERE" ;
	if( $parameters )
	{
	    $query .= " (" ;
	    $param_array = explode( ",", $parameters ) ;
	    $isfirst = true ;
	    foreach( $param_array as $param )
	    {
		$param = strtoupper( trim( $param ) ) ;
		if( !$isfirst )
		{
		    $query .= " OR" ;
		}
		$isfirst = false ;
		$query .= " $param > 0" ;
	    }
	    $query .= ") " ;
	}
	if( $parameters )
	{
	    $query .= " AND" ;
	}
	$query .= " (NUM_ID >= '$year-01-01 00:00:00')" ;
	$query .= " AND (NUM_ID <= '$year-12-31 23:59:59')" ;
	$query .= " ORDER BY month ASC" ;

	//print( "$query<BR>\n" ) ;

	$result = parent::dbquery( $query ) ;
	$num_rows = mysql_num_rows( $result ) ;
	if( $num_rows != 0 )
	{
	    while( $line = mysql_fetch_row( $result ) )
	    {
		if( $line )
		{
		    $colnum = 0 ;
		    foreach( $line as $value )
		    {
			if( $colnum > 0 ) print( "," ) ;
			print( $value ) ;
			$colnum++ ;
		    }
		    print( "\n" ) ;
		}
	    }
	}

	parent::dbclose( $result ) ;
    }

    // Optional parameters
    protected function getYears()
    {
	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->mlsoconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$parameters = $_REQUEST['params'] ;
	if( $parameters )
	{
	    $parameters = mysql_real_escape_string( trim( $parameters ) ) ;
	}

	$query = "SELECT DISTINCT EXTRACT(year from NUM_ID) as year FROM tbl_numfiles" ;
	if( $parameters )
	{
	    $query .= " WHERE (" ;
	    $param_array = explode( ",", $parameters ) ;
	    $isfirst = true ;
	    foreach( $param_array as $param )
	    {
		$param = strtoupper( trim( $param ) ) ;
		if( !$isfirst )
		{
		    $query .= " OR" ;
		}
		$isfirst = false ;
		$query .= " $param > 0" ;
	    }
	    $query .= ") " ;
	}
	$query .= " ORDER BY year ASC" ;

	//print( "$query<BR>\n" ) ;

	$result = parent::dbquery( $query ) ;
	$num_rows = mysql_num_rows( $result ) ;
	if( $num_rows != 0 )
	{
	    while( $line = mysql_fetch_row( $result ) )
	    {
		if( $line )
		{
		    $colnum = 0 ;
		    foreach( $line as $value )
		    {
			if( $colnum > 0 ) print( "," ) ;
			print( $value ) ;
			$colnum++ ;
		    }
		    print( "\n" ) ;
		}
	    }
	}

	parent::dbclose( $result ) ;
    }

    // Need startdate and enddate
    // optional parameters
    protected function getInstruments()
    {
	$startdate = $_REQUEST['startdate'] ;
	if( !$startdate )
	{
	    print( "Instrument query failed, start date not specified\n" ) ;
	    exit( 0 ) ;
	}
	$enddate = $_REQUEST['enddate'] ;
	if( !$enddate )
	{
	    print( "Instrument query failed, end date not specified\n" ) ;
	    exit( 0 ) ;
	}

	$params = $_REQUEST['params'] ;
	if( !$params )
	{
	    print( "Instrument query failed, no parameters specified\n" ) ;
	    exit( 0 ) ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->mlsoconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$startdate = mysql_real_escape_string( trim( $startdate ) ) ;
	$enddate = mysql_real_escape_string( trim( $enddate ) ) ;

	// iterate through each of the specified parameters and see if
	// there are entries for that parameter in the tbl_numfiles
	// table. Echo the count for each parameter.
	$params = mysql_real_escape_string( trim( $params ) ) ;
	$parama = explode( ",", $params ) ;
	$paramc = count( $parama ) ;
	for( $i = 0; $i < $paramc; $i++ )
	{
	    $param = $parama[$i] ;
	    $query = "SELECT count(*) as counts FROM tbl_numfiles WHERE" ;
	    $query .= " (NUM_ID >= '$startdate')" ;
	    $query .= " AND (NUM_ID <= '$enddate')" ;
	    $query .= " AND $param > 0" ;
	    $query .= " ORDER BY NUM_ID ASC" ;

	    $result = parent::dbquery( $query ) ;
	    $num_rows = mysql_num_rows( $result ) ;
	    if( $num_rows != 0 )
	    {
		$counta = mysql_fetch_row( $result ) ;
		$count = $counta[0] ;
		echo "$count\n" ;
	    }
	    else
	    {
		print( "Failed to retrieve instrument information" ) ;
		exit( 1 ) ;
	    }
	}
	parent::dbclose( $result ) ;
    }

    // Need startdate and enddate
    protected function getParameters()
    {
	$startdate = $_REQUEST['startdate'] ;
	if( !$startdate )
	{
	    print( "Instrument query failed, start date not specified\n" ) ;
	    exit( 0 ) ;
	}
	$enddate = $_REQUEST['enddate'] ;
	if( !$enddate )
	{
	    print( "Instrument query failed, end date not specified\n" ) ;
	    exit( 0 ) ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->mlsoconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$startdate = mysql_real_escape_string( trim( $startdate ) ) ;
	$enddate = mysql_real_escape_string( trim( $enddate ) ) ;

	$query = "SELECT * FROM tbl_numfiles WHERE" ;
	$query .= " (NUM_ID >= '$startdate') AND" ;
	$query .= " (NUM_ID < '$enddate') ORDER BY NUM_ID ASC" ;

	$result = parent::dbquery( $query ) ;
	if( $result )
	{
	    $columns = array() ;
	    $values = array() ;
	    $numfields = 0 ;
	    $rownum = 0 ;
	    while( $row = mysql_fetch_row( $result ) )
	    {
		if( $rownum == 0 )
		{
		    $numfields = count( $row ) ;
		    for( $field = 0; $field < $numfields; $field++ )
		    {
			$name = mysql_field_name($result, $field);
			$columns[$field] = $name ;
		    }
		}
		$rownum++ ;

		$colnum = 0 ;
		foreach( $row as $value )
		{
		    $values[$colnum] = $values[$colnum] + $value ;
		    $colnum++ ;
		}
	    }
	    for( $field = 0; $field < $numfields; $field++ )
	    {
		if( $columns[$field] != "NUM_ID" && $values[$field] > 0 )
		{
		    echo "$columns[$field]\n" ;
		}
	    }
	}

	parent::dbclose( $result ) ;
    }

    // Need startyear, startdate, enddate, instrument id, wave length,
    // and the limit of the number of files returned
    // Optional processing and quality flags
    protected function getFiles()
    {
	$startyear = $_REQUEST['startyear'] ;
	if( !$startyear )
	{
	    print( "MLSO file query failed, start year not specified\n" ) ;
	    exit( 0 ) ;
	}
	$startdate = $_REQUEST['startdate'] ;
	if( !$startdate )
	{
	    print( "MLSO file query failed, start date not specified\n" ) ;
	    exit( 0 ) ;
	}
	$enddate = $_REQUEST['enddate'] ;
	if( !$enddate )
	{
	    print( "MLSO file query failed, end date not specified\n" ) ;
	    exit( 0 ) ;
	}
	$instrument = $_REQUEST['instrument'] ;
	if( !$instrument )
	{
	    print( "MLSO file query failed, instrument id not specified\n" ) ;
	    exit( 0 ) ;
	}
	$wavelength = $_REQUEST['wavelength'] ;
	if( !$wavelength )
	{
	    print( "MLSO file query failed, wave length not specified\n" ) ;
	    exit( 0 ) ;
	}
	$limit = $_REQUEST['limit'] ;
	if( !$limit )
	{
	    print( "MLSO file query failed, return limit not specified\n" ) ;
	    exit( 0 ) ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->mlsoconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$startyear = mysql_real_escape_string( trim( $startyear ) ) ;
	$startdate = mysql_real_escape_string( trim( $startdate ) ) ;
	$enddate = mysql_real_escape_string( trim( $enddate ) ) ;
	$instrument = mysql_real_escape_string( trim( $instrument ) ) ;
	$wavelength = mysql_real_escape_string( trim( $wavelength ) ) ;

	$query = "SELECT FILE_NAME as filename, TYPE as type" ;
	$query .= " FROM tbl_$startyear" ;
	$query .= " WHERE (datetime_obs >= '$startdate')" ;
	$query .= " AND (datetime_obs < '$enddate')" ;
	$query .= " AND (INSTRUMENT = '$instrument')" ;
	$query .= " AND (WAVE_LENGTH = '$wavelength')" ;

	$quality = $_REQUEST['quality'] ;
	if( $quality )
	{
	    $quality = mysql_real_escape_string( trim( $quality ) ) ;
	    $query .= " AND (QUALITY = '$quality')" ;
	}

	$processing_s = $_REQUEST['processing'] ;
	if( $processing_s )
	{
	    $processing_s = mysql_real_escape_string( trim( $processing_s ) ) ;
	    $processing_a = explode( ",", $processing_s ) ;
	    $query .= " AND (" ;
	    $isfirst = true ;
	    foreach( $processing_a as $processing )
	    {
		if( !$isfirst )
		{
		    $query .= " OR" ;
		}
		$isfirst = false ;
		$query .= " PROCESSING = '$processing'" ;
	    }
	    $query .= ") " ;
	}

	$query .= " ORDER BY datetime_obs ASC, type DESC" ;
	$query .= " LIMIT 0,$limit" ;

	//print( "$query<BR>\n" ) ;

	$result = parent::dbquery( $query ) ;
	$num_rows = mysql_num_rows( $result ) ;
	if( $num_rows != 0 )
	{
	    while( $line = mysql_fetch_row( $result ) )
	    {
		if( $line )
		{
		    $colnum = 0 ;
		    foreach( $line as $value )
		    {
			if( $colnum > 0 ) echo "," ;
			echo $value ;
			$colnum++ ;
		    }
		    echo "\n" ;
		}
	    }
	}
	parent::dbclose( $result ) ;
    }
} ;
?>
