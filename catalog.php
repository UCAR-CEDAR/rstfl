<?php
$mydocumentroot=$_SERVER["DOCUMENT_ROOT"];
set_include_path( $mydocumentroot );

global $catalog_systems ;
$catalog_systems = array() ;

require_once( "rstfl/AbstractCatalog.php" ) ;
require_once( "rstfl/CedarCatalog.php" ) ;
require_once( "rstfl/MLSOCatalog.php" ) ;
require_once( "rstfl/CedarAuth.php" ) ;
require_once( "rstfl/CedarGenerator.php" ) ;

$system_requested = $_REQUEST['system'] ;
if( !$system_requested )
{
    print( "No system requested<BR>\n" ) ;
    exit( 1 ) ;
}

$system_requested = strtolower( trim( $system_requested ) ) ;
$system_class = $catalog_systems[$system_requested] ;
if( !$system_class )
{
    print( "The catalog system $system_requested does not exist<BR>\n" ) ;
    exit( 1 ) ;
}

$system = new $system_class() ;
if( !$system )
{
    print( "Unable to load system $system_requested<BR>\n" ) ;
    exit( 1 ) ;
}

$requested = $_REQUEST['request'] ;
if( !$requested )
{
    print( "No catalog information has been requested<BR>\n" ) ;
    exit( 1 ) ;
}
$requested = strtolower( trim( $requested ) ) ;

$system->execute( $requested ) ;
?>
