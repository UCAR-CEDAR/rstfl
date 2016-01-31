<?php
global $catalog_systems ;
$catalog_systems['cedar'] = "CedarCatalog" ;

class CedarCatalog extends AbstractCatalog
{
    public function CedarCatalog()
    {
	$this->catalog_functions['instruments'] = 'getInstruments' ;
	$this->catalog_functions['days'] = 'getDays' ;
	$this->catalog_functions['months'] = 'getMonths' ;
	$this->catalog_functions['years'] = 'getYears' ;
	$this->catalog_functions['params'] = 'getParams' ;
	$this->catalog_functions['dateid'] = 'getDateID' ;
	$this->catalog_functions['files'] = 'getFileList' ;
	$this->catalog_functions['plot'] = 'getPlotFunc' ;
	$this->catalog_functions['sites'] = 'getSites' ;

	$this->catalog_functions['paramlist'] = 'getParamList' ;
	$this->catalog_functions['instrlist'] = 'getInstrList' ;
	$this->catalog_functions['opmodelist'] = 'getOpModeList' ;
	$this->catalog_functions['opmodeparamslist'] = 'getOpModeParamsList' ;

	$this->catalog_functions['plottableparams'] = 'getPlottableParams' ;
    }

    private function cedarconnect()
    {
	return parent::dbconnect( "databases.hao.ucar.edu","madrigal","c@shit","CEDARCATALOG" );
    }

    /*
    getinstruments?startdateid=s&enddateid=e&parameter=p[&parameter=p2...n] - returns name, prefix, kinst
    getinstruments?startdateid=s&enddateid=e - returns name, prefix, kinst
    getinstruments?parameter=p[&parameter=p2...n] - returns name, prefix, kinst
    getinstruments - returns all the instruments name, prefix, kinst
    */
    protected function getInstruments()
    {
	// determine the request parameters. Could get startdateid,
	// enddateid, multiple parameters. Could get startdateid, enddateid.
	// Could get multiple parameters. Could just get all parameters.
	$startdateid = $_REQUEST['startdateid'] ;
	$enddateid = $_REQUEST['enddateid'] ;
	if( $startdateid && !$enddateid )
	{
	    print( "Instrument query failed, start date id specified but no end date id specified\n" ) ;
	    exit( 0 ) ;
	}
	if( $enddateid && !$startdateid )
	{
	    print( "Instrument query failed, end date id specified but no start date id specified\n" ) ;
	    exit( 0 ) ;
	}
	$params = $_REQUEST['params'] ; // comma separated list

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "Failed to connect to the database: $isconnected\n" ) ;
	    exit( 0 ) ;
	}

	if( $startdateid )
	{
	    $startdateid = mysql_real_escape_string( $startdateid ) ;
	}
	if( $enddateid )
	{
	    $enddateid = mysql_real_escape_string( $enddateid ) ;
	}
	if( $params )
	{
	    $params = mysql_real_escape_string( trim( $params ) ) ;
	}

	$query = "SELECT DISTINCT i.KINST, i.PREFIX, i.INST_NAME" ;
	$from = " FROM tbl_instrument i" ;
	$where = "" ;
	if( $startdateid )
	{
	    if( $params )
	    {
		$from .= ", tbl_record_info ri, tbl_record_type rt, tbl_file_info fi, tbl_date_in_file dif" ;
		$where .= " WHERE i.KINST=rt.KINST" ;
		$where .= " AND rt.RECORD_TYPE_ID=ri.RECORD_TYPE_ID" ;
		$where .= " AND rt.RECORD_TYPE_ID=fi.RECORD_TYPE_ID" ;
		$where .= " AND fi.RECORD_IN_FILE_ID=dif.RECORD_IN_FILE_ID" ;
		$where .= " AND dif.DATE_ID >= $startdateid" ;
		$where .= " AND dif.DATE_ID <= $enddateid" ;
		$where .= " AND ri.PARAMETER_ID in ($params)" ;
	    }
	    else
	    {
		$from .= ", tbl_record_type rt, tbl_file_info fi, tbl_date_in_file dif" ;
		$where .= " WHERE i.KINST=rt.KINST" ;
		$where .= " AND rt.RECORD_TYPE_ID=fi.RECORD_TYPE_ID" ;
		$where .= " AND fi.RECORD_IN_FILE_ID=dif.RECORD_IN_FILE_ID" ;
		$where .= " AND dif.DATE_ID >= $startdateid" ;
		$where .= " AND dif.DATE_ID <= $enddateid" ;
	    }
	}
	else
	{
	    if( $params )
	    {
		$from .= ", tbl_record_info ri, tbl_record_type rt" ;
		$where .= " WHERE i.KINST=rt.KINST" ;
		$where .= " AND rt.RECORD_TYPE_ID=ri.RECORD_TYPE_ID" ;
		$where .= " AND ri.PARAMETER_ID in ($params)" ;
	    }
	}
	$query .= $from . " " . $where . " ORDER BY i.KINST ASC" ;

	//print( "$query\n" ) ;

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

    /*
    getday?year=yyyy&month=mm&kinst=k - returns day
    getday?year=yyyy&month=mm&parameter=p - returns day
    getday?year=yyyy&month=mm&kinst=k&parameter=p - returns day
    getday?year=yyyy&month=mm - returns day
    */
    protected function getDays()
    {
	$year = $_REQUEST['year'] ;
	if( !$year )
	{
	    print( "Day query failed: no year has been specified\n" ) ;
	    exit( 0 ) ;
	}
	$year = trim( $year ) ;

	$month = $_REQUEST['month'] ;
	if( !$month )
	{
	    print( "Day query failed: no month has been specified\n" ) ;
	    exit( 0 ) ;
	}
	$month = trim( $month ) ;

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	if( $year )
	{
	    $year = mysql_real_escape_string( $year ) ;
	}

	if( $month )
	{
	    $month = mysql_real_escape_string( $month ) ;
	}

	$params = $_REQUEST['params'] ;
	if( $params )
	{
	    $params = trim( $params ) ;
	    $params = mysql_real_escape_string( $params ) ;
	}

	$kinst = $_REQUEST['kinst'] ;
	if( $kinst )
	{
	    $kinst = trim( $kinst ) ;
	    $kinst = mysql_real_escape_string( $kinst ) ;
	}

	$query = "SELECT DISTINCT d.DAY" ;
	if( $kinst && !$params )
	{
	    $query .= " FROM tbl_date d, tbl_date_in_file dif," ;
	    $query .= " tbl_file_info fi, tbl_record_type rt," ;
	    $query .= " tbl_record_info ri" ;
	    $query .= " WHERE d.DATE_ID=dif.DATE_ID" ;
	    $query .= " AND dif.RECORD_IN_FILE_ID=fi.RECORD_IN_FILE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=rt.RECORD_TYPE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=ri.RECORD_TYPE_ID" ;
	    $query .= " AND d.YEAR=$year" ;
	    $query .= " AND d.MONTH=$month" ;
	    $query .= " AND rt.KINST=$kinst" ;
	}
	else if( $params && !$kinst )
	{
	    $query .= " FROM tbl_date d, tbl_date_in_file dif," ;
	    $query .= " tbl_file_info fi, tbl_record_info ri" ;
	    $query .= " WHERE d.DATE_ID=dif.DATE_ID" ;
	    $query .= " AND dif.RECORD_IN_FILE_ID=fi.RECORD_IN_FILE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=ri.RECORD_TYPE_ID" ;
	    $query .= " AND d.YEAR=$year" ;
	    $query .= " AND d.MONTH=$month" ;
	    $query .= " AND ri.PARAMETER_ID in ($params)" ;
	}
	else if( $params && $kinst )
	{
	    $query .= " FROM tbl_date d, tbl_date_in_file dif," ;
	    $query .= " tbl_file_info fi, tbl_record_type rt," ;
	    $query .= " tbl_record_info ri" ;
	    $query .= " WHERE d.DATE_ID=dif.DATE_ID" ;
	    $query .= " AND dif.RECORD_IN_FILE_ID=fi.RECORD_IN_FILE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=rt.RECORD_TYPE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=ri.RECORD_TYPE_ID" ;
	    $query .= " AND d.YEAR=$year" ;
	    $query .= " AND d.MONTH=$month" ;
	    $query .= " AND rt.KINST=$kinst" ;
	    $query .= " AND ri.PARAMETER_ID in ($params)" ;
	}
	else if( !$params && !$kinst )
	{
	    $query .= " FROM tbl_date d, tbl_date_in_file dif" ;
	    $query .= " WHERE d.DATE_ID=dif.DATE_ID" ;
	    $query .= " AND d.YEAR=$year" ;
	    $query .= " AND d.MONTH=$month" ;
	}

	$query .= " ORDER BY d.DAY ASC";
	//print( "$query\n" ) ;

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

    /*
    getmonth?year=yyyy&kinst=k - returns month
    getmonth?year=yyyy&parameter=p - returns month
    getmonth?year=yyyy&kinst=k&parameter=p - returns month
    getmonth?year=yyyy - returns month
    */
    protected function getMonths()
    {
	$year = $_REQUEST['year'] ;
	if( !$year )
	{
	    print( "Month query failed: no year has been specified\n" ) ;
	    exit( 0 ) ;
	}
	$year = trim( $year ) ;

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	if( $year )
	{
	    $year = mysql_real_escape_string( $year ) ;
	}

	$params = $_REQUEST['params'] ;
	if( $params )
	{
	    $params = trim( $params ) ;
	    $params = mysql_real_escape_string( $params ) ;
	}

	$kinst = $_REQUEST['kinst'] ;
	if( $kinst )
	{
	    $kinst = trim( $kinst ) ;
	    $kinst = mysql_real_escape_string( $kinst ) ;
	}

	$query = "SELECT DISTINCT d.MONTH" ;
	if( $kinst && !$params )
	{
	    $query .= " FROM tbl_date d, tbl_date_in_file dif," ;
	    $query .= " tbl_file_info fi, tbl_record_type rt," ;
	    $query .= " tbl_record_info ri" ;
	    $query .= " WHERE d.DATE_ID=dif.DATE_ID" ;
	    $query .= " AND dif.RECORD_IN_FILE_ID=fi.RECORD_IN_FILE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=rt.RECORD_TYPE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=ri.RECORD_TYPE_ID" ;
	    $query .= " AND d.YEAR=$year" ;
	    $query .= " AND rt.KINST=$kinst" ;
	}
	else if( $params && !$kinst )
	{
	    $query .= " FROM tbl_date d, tbl_date_in_file dif," ;
	    $query .= " tbl_file_info fi, tbl_record_info ri" ;
	    $query .= " WHERE d.DATE_ID=dif.DATE_ID" ;
	    $query .= " AND dif.RECORD_IN_FILE_ID=fi.RECORD_IN_FILE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=ri.RECORD_TYPE_ID" ;
	    $query .= " AND d.YEAR=$year" ;
	    $query .= " AND ri.PARAMETER_ID in ($params)" ;
	}
	else if( $params && $kinst )
	{
	    $query .= " FROM tbl_date d, tbl_date_in_file dif," ;
	    $query .= " tbl_file_info fi, tbl_record_type rt," ;
	    $query .= " tbl_record_info ri" ;
	    $query .= " WHERE d.DATE_ID=dif.DATE_ID" ;
	    $query .= " AND dif.RECORD_IN_FILE_ID=fi.RECORD_IN_FILE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=rt.RECORD_TYPE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=ri.RECORD_TYPE_ID" ;
	    $query .= " AND d.YEAR=$year" ;
	    $query .= " AND rt.KINST=$kinst" ;
	    $query .= " AND ri.PARAMETER_ID in ($params)" ;
	}
	else if( !$params && !$kinst )
	{
	    $query .= " FROM tbl_date d, tbl_date_in_file dif" ;
	    $query .= " WHERE d.DATE_ID=dif.DATE_ID" ;
	    $query .= " AND d.YEAR=$year" ;
	}

	$query .= " ORDER BY d.MONTH ASC" ;
	//print( "$query\n" ) ;

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

    /*
    getyear?kinst=k - returns year
    getyear?parameter=p[&parameter=p2...n] - returns year
    getyear?kinst=k&parameter=p[&parameter=p2...n] - returns year
    getyear - returns all years
    */
    protected function getYears()
    {
	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$kinst = $_REQUEST['kinst'] ;
	if( $kinst )
	{
	    $kinst = mysql_real_escape_string( trim( $kinst ) ) ;
	}

	$params = $_REQUEST['params'] ;
	if( $params )
	{
	    $params = mysql_real_escape_string( trim( $params ) ) ;
	}

	$query = "SELECT DISTINCT d.YEAR" ;
	if( $kinst && !$params )
	{
	    $query .= " FROM tbl_date d, tbl_date_in_file dif," ;
	    $query .= " tbl_file_info fi, tbl_record_type rt," ;
	    $query .= " tbl_record_info ri" ;
	    $query .= " WHERE d.DATE_ID=dif.DATE_ID" ;
	    $query .= " AND dif.RECORD_IN_FILE_ID=fi.RECORD_IN_FILE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=rt.RECORD_TYPE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=ri.RECORD_TYPE_ID" ;
	    $query .= " AND rt.KINST=$kinst" ;
	}
	else if( $params && !$kinst )
	{
	    $query .= " FROM tbl_date d, tbl_date_in_file dif," ;
	    $query .= " tbl_file_info fi, tbl_record_info ri" ;
	    $query .= " WHERE d.DATE_ID=dif.DATE_ID" ;
	    $query .= " AND dif.RECORD_IN_FILE_ID=fi.RECORD_IN_FILE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=ri.RECORD_TYPE_ID" ;
	    $query .= " AND ri.PARAMETER_ID in ($params)" ;
	}
	else if( $params && $kinst )
	{
	    $query .= " FROM tbl_date d, tbl_date_in_file dif," ;
	    $query .= " tbl_file_info fi, tbl_record_type rt," ;
	    $query .= " tbl_record_info ri" ;
	    $query .= " WHERE d.DATE_ID=dif.DATE_ID" ;
	    $query .= " AND dif.RECORD_IN_FILE_ID=fi.RECORD_IN_FILE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=rt.RECORD_TYPE_ID" ;
	    $query .= " AND fi.RECORD_TYPE_ID=ri.RECORD_TYPE_ID" ;
	    $query .= " AND rt.KINST=$kinst" ;
	    $query .= " AND ri.PARAMETER_ID in ($params)" ;
	}
	else if( !$params && !$kinst )
	{
	    $query .= " FROM tbl_date d, tbl_date_in_file dif" ;
	    $query .= " WHERE d.DATE_ID=dif.DATE_ID" ;
	}

	$query .= " ORDER BY d.YEAR ASC" ;
	//print( "$query\n" ) ;

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

    /*
    getparameters?startdateid=s&enddateid=e&kinst=k - returns parameter id
    getparameters?startdateid=s&enddateid=e - returns parameter id
    getparameters?kinst=k - returns parameter id
    getparameters - returns all paramters id
    */
    protected function getParams()
    {
	$startdateid = $_REQUEST['startdateid'] ;
	$enddateid = $_REQUEST['enddateid'] ;
	if( $startdateid && !$enddateid )
	{
	    print( "Parameter query failed, start date id specified but no end date id\n" ) ;
	    exit( 0 ) ;
	}
	if( $enddateid && !$startdateid )
	{
	    print( "Parameter query failed, end date id specified but no start date id\n" ) ;
	    exit( 0 ) ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$kinst = $_REQUEST['kinst'] ;

	if( $startdateid )
	{
	    $startdateid = mysql_real_escape_string( trim( $startdateid ) ) ;
	}
	if( $enddateid )
	{
	    $enddateid = mysql_real_escape_string( trim( $enddateid ) ) ;
	}
	if( $kinst )
	{
	    $kinst = mysql_real_escape_string( trim( $kinst ) ) ;
	}

	$query = "SELECT DISTINCT pc.PARAMETER_ID" ;
	if( $startdateid )
	{
	    $query .= " FROM tbl_parameter_code pc, tbl_record_info ri," ;
	    $query .= " tbl_record_type rt, tbl_file_info fi," ;
	    $query .= " tbl_date_in_file dif" ;
	    $query .= " WHERE pc.PARAMETER_ID=ri.PARAMETER_ID" ;
	    $query .= " AND ri.RECORD_TYPE_ID=rt.RECORD_TYPE_ID" ;
	    $query .= " AND rt.RECORD_TYPE_ID=fi.RECORD_TYPE_ID" ;
	    $query .= " AND fi.RECORD_IN_FILE_ID=dif.RECORD_IN_FILE_ID" ;
	    $query .= " AND dif.DATE_ID >= $startdateid" ;
	    $query .= " AND dif.DATE_ID <= $enddateid" ;
	    if( $kinst )
	    {
		$query .= " AND rt.KINST = $kinst" ;
	    }
	}
	else if( $kinst && !$startdateid )
	{
	    $query .= " FROM tbl_parameter_code pc, tbl_record_info ri," ;
	    $query .= " tbl_record_type rt" ;
	    $query .= " WHERE pc.PARAMETER_ID=ri.PARAMETER_ID" ;
	    $query .= " AND ri.RECORD_TYPE_ID=rt.RECORD_TYPE_ID" ;
	    $query .= " AND rt.KINST = $kinst" ;
	}
	else if( !$kinst && !$startdateid )
	{
	    $query .= " FROM tbl_parameter_code pc" ;
	}
	$query .= " ORDER BY pc.PARAMETER_ID ASC" ;
	//print( "$query\n" ) ;

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

    /*
    getdateid?year=yyyy&month=mm&day=dd - returns the date id
    */
    protected function getDateID()
    {
	$year = $_REQUEST['year'] ;
	if( !$year )
	{
	    print( "Date ID query failed, must specify the year" ) ;
	    exit( 0 ) ;
	}

	$month = $_REQUEST['month'] ;
	if( !$month )
	{
	    print( "Date ID query failed, must specify the month" ) ;
	    exit( 0 ) ;
	}

	$day = $_REQUEST['day'] ;
	if( !$day )
	{
	    print( "Date ID query failed, must specify the day" ) ;
	    exit( 0 ) ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$year = mysql_real_escape_string( trim( $year ) ) ;
	$month = mysql_real_escape_string( trim( $month ) ) ;
	$day = mysql_real_escape_string( trim( $day ) ) ;

	$query = "SELECT tbl_date.DATE_ID as date_id" ;
	$query .= " FROM tbl_date" ;
	$query .= " WHERE tbl_date.YEAR=$year" ;
	$query .= " AND tbl_date.MONTH=$month" ;
	$query .= " AND tbl_date.DAY=$day" ;
	//print( "$query\n" ) ;

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

    /*
    getFileList?startdateid=s&enddateid=e&kinst=k
    */
    protected function getFileList()
    {
	$startdateid = $_REQUEST['startdateid'] ;
	if( !$startdateid )
	{
	    print( "File list query failed, must specify start date id\n" );
	    exit( 0 ) ;
	}
	$enddateid = $_REQUEST['enddateid'] ;
	if( !$enddateid )
	{
	    print( "File list query failed, must specify end date id\n" ) ;
	    exit( 0 ) ;
	}
	$kinst = $_REQUEST['kinst'] ;
	if( !$kinst )
	{
	    print( "File list query failed, must specify instrument\n" ) ;
	    exit( 0 ) ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$startdateid = mysql_real_escape_string( trim( $startdateid ) ) ;
	$enddateid = mysql_real_escape_string( trim( $enddateid ) ) ;
	$kinst = mysql_real_escape_string( trim( $kinst ) ) ;

	$query = "SELECT DISTINCT cf.FILE_NAME" ;
	$query .= " FROM tbl_date_in_file dif, tbl_cedar_file cf," ;
	$query .= " tbl_file_info fi, tbl_record_type rt" ;
	$query .= " WHERE dif.RECORD_IN_FILE_ID=fi.RECORD_IN_FILE_ID" ;
	$query .= " AND fi.FILE_ID=cf.FILE_ID" ;
	$query .= " AND fi.RECORD_TYPE_ID=rt.RECORD_TYPE_ID" ;
	$query .= " AND (dif.DATE_ID >= $startdateid )" ;
	$query .= " AND (dif.DATE_ID <= $enddateid )" ;
	$query .= " AND (rt.KINST = $kinst )";
	//print( "$query\n" ) ;

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

    /*
    getPlotFunc?kinst=k&parameter=p - return the plot function
    */
    protected function getPlotFunc()
    {
	$kinst = $_REQUEST['kinst'] ;
	if( !$kinst )
	{
	    print( "Plot query failed, must specify an instrument\n" ) ;
	    exit( 0 ) ;
	}
	$param = $_REQUEST['param'] ;
	if( !$param )
	{
	    print( "Plot query failed, must specify a parameter\n" ) ;
	    exit( 0 ) ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$kinst = mysql_real_escape_string( trim( $kinst ) ) ;
	$param = mysql_real_escape_string( trim( $param ) ) ;

	$query = "SELECT DISTINCT plot_func FROM tbl_plotting_params" ;
	$query .= " WHERE kinst=$kinst AND parameter_id=$param" ;
	//print( "$query\n" ) ;

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

    /*
    getSites?kinst=k - return the plot function
    */
    protected function getSites()
    {
	$kinst = $_REQUEST['kinst'] ;
	if( !$kinst )
	{
	    print( "Site query failed, must specify an instrument\n" ) ;
	    exit( 0 ) ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$kinst = mysql_real_escape_string( trim( $kinst ) ) ;

	$query = "SELECT DISTINCT id FROM tbl_site WHERE kinst=$kinst" ;
	//print( "$query\n" ) ;

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

    // get the list of all parameters for instance generation
    protected function getParamList()
    {
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$query = "SELECT DISTINCT PARAMETER_ID, LONG_NAME, SHORT_NAME, MADRIGAL_NAME FROM tbl_parameter_code" ;

	//print( "$query\n" ) ;

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

    // get the list of all instruments for instance generation
    protected function getInstrList()
    {
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$query = "SELECT DISTINCT KINST, INST_NAME, PREFIX from tbl_instrument" ;

	//print( "$query\n" ) ;

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

    // get the list of all operating modes for instance generation
    protected function getOpModeList()
    {
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$query = "SELECT DISTINCT RECORD_TYPE_ID, KINST, KINDAT, DESCRIPTION from tbl_record_type" ;

	//print( "$query\n" ) ;

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

    // get the list of all operating modes measures params for instance generation
    protected function getOpModeParamsList()
    {
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$query = "SELECT DISTINCT rt.KINST, rt.KINDAT, ri.PARAMETER_ID " ;
	$query .= "FROM tbl_record_type rt, tbl_record_info ri, " ;
	$query .= "tbl_parameter_code pc " ;
	$query .= "WHERE rt.RECORD_TYPE_ID=ri.RECORD_TYPE_ID " ;
	$query .= "AND pc.PARAMETER_ID=ri.PARAMETER_ID " ;
	$query .= "AND NOT (pc.LONG_NAME='UNDEFINED')" ;

	//print( "$query\n" ) ;

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

    // get the list of all plottable parameters for a given kinst
    protected function getPlottableParams()
    {
	$kinst = $_REQUEST['kinst'] ;
	if( !$kinst )
	{
	    print( "plottable params query failed, must specify kinst\n" );
	    exit( 0 ) ;
	}

	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "$isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$kinst = mysql_real_escape_string( trim( $kinst ) ) ;

	$query = "SELECT DISTINCT parameter_id, plot_func " ;
	$query .= "FROM tbl_plotting_params " ;
	$query .= "WHERE kinst='$kinst'" ;

	//print( "$query\n" ) ;

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
