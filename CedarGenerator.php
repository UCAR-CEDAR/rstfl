<?php
global $catalog_systems ;
$catalog_systems['cedar_gen'] = "CedarGenerator" ;

class CedarGenerator extends AbstractCatalog
{
    public function CedarGenerator()
    {
	$this->catalog_functions['instruments'] = 'genInstruments' ;
	$this->catalog_functions['parameters'] = 'genParameters' ;
	$this->catalog_functions['opmodes'] = 'genOpModes' ;
	$this->catalog_functions['opmodeparams'] = 'genOpModeParams' ;
    }

    private function cedarconnect()
    {
	return parent::dbconnect( "db.hao.ucar.edu:3306","root","c@shit","CEDARCATALOG" );
    }

    /*
    */
    protected function genInstruments()
    {
	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "Failed to connect to the database: $isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$query = "SELECT DISTINCT tbl_instrument.KINST as kinst, tbl_instrument.INST_NAME as inst_name, tbl_instrument.PREFIX as prefix FROM tbl_instrument";
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
    */
    protected function genParameters()
    {
	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "Failed to connect to the database: $isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$query = "SELECT DISTINCT tbl_parameter_code.PARAMETER_ID as parameter_id, tbl_parameter_code.LONG_NAME as long_name, tbl_parameter_code.SHORT_NAME as short_name, tbl_parameter_code.MADRIGAL_NAME as madrigal_name FROM tbl_parameter_code";
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
    */
    protected function genOpModes()
    {
	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "Failed to connect to the database: $isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$query = "SELECT DISTINCT tbl_record_type.RECORD_TYPE_ID as record_type_id, tbl_record_type.KINST as kinst, tbl_record_type.KINDAT as kindat, tbl_record_type.DESCRIPTION as description FROM tbl_record_type";
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
    */
    protected function genOpModeParams()
    {
	// need to connect to the database here in order to run the
	// function mysql_real_escape_string
	$isconnected = $this->cedarconnect() ;
	if( $isconnected != "good" )
	{
	    print( "Failed to connect to the database: $isconnected\n" ) ;
	    exit( 0 ) ;
	}

	$query = "SELECT DISTINCT tbl_record_type.KINST as kinst, tbl_record_type.KINDAT as kindat, tbl_record_info.PARAMETER_ID as parameter_id FROM tbl_record_type, tbl_record_info, tbl_parameter_code WHERE tbl_record_type.RECORD_TYPE_ID=tbl_record_info.RECORD_TYPE_ID AND tbl_parameter_code.PARAMETER_ID=tbl_record_info.PARAMETER_ID AND NOT (tbl_parameter_code.LONG_NAME='UNDEFINED');";
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
