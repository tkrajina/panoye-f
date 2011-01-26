<?php

define( 'STARTED', microtime( true ) );

define( 'FRAMEWORK_VERSION', 'v0.7' );

if( ! defined( 'APP' ) ) { // Tako da se može includati samo zbog verzije
	return;
}

////////////////////////////////////////////////

$_GET[ 'page' ] = @preg_replace( '/[^\w\d\-\.]/',  '', @$_GET[ 'page' ] );
$_GET[ 'arg' ] = @preg_replace( '/[^\w\d\-]/',  '', @$_GET[ 'arg' ] );

////////////////////////////////////////////////

function import( $path1, $path2 = '' ) {
	require_once( $path1 . $path2 );
}

//////////////////////////////////////////////////////

import( FRAMEWORK, 'FrameworkApplicationObject.class.php' );
import( APP, 'Application.class.php' );
import( FRAMEWORK . 'lib/db/Db.class.php' );

function customErrorReporting( $errno, $errstr, $errfile, $errline, $errcontext ) {
	Logs::error( 'Error: [' . $errno . '] ' . $errstr . ' in ' . $errfile . ' (line:' . $errline . ') ' . $errcontext );
}

set_error_handler( 'customErrorReporting', E_ALL & ~ E_NOTICE );

///////////////////////////////////////////////////////////////////////////////


// Ovo je potrebno rucno inkludati

import( FRAMEWORK, 'init.php' );
import( FRAMEWORK, 'autoload.php' );
import( FRAMEWORK . 'lib/Cache.class.php' );
import( FRAMEWORK . 'lib/Logs.class.php' );

// Ne pomicati ovo prije gornjih importova, jer inače odserijalizira
// klasu iz sessiona koja još nije učitana:
@ini_set( "session.save_handler", "files" );

// Bez ovog vraća neku glupu gresku:
@session_name('sid');

session_start();

///////////////////////////////////////////////////////////////////////////////

Logs::info( 'REMOTEADDR:', @$_SERVER[ 'REMOTE_ADDR' ] );
Logs::info( 'REFERER:', @$_SERVER[ 'HTTP_REFERER' ] );
Logs::info( 'AGENT:', @$_SERVER[ 'HTTP_USER_AGENT' ] );
Logs::info( 'GET:', @$_GET );
if( $_POST ) {
	// Only keys for POST, because otherwise the password would be 
	// visible in the log (TODO):
	Logs::info( 'POST:', array_keys( $_POST ) );
}

///////////////////////////////////////////////////////////////////////////////

// 1. provjera da nije u cache-u, ako jest onda prikazujemo tu stranicu
// bez ikakvih drugih icludova:

$queryString = $_SERVER[ 'QUERY_STRING' ];

$executedCachedPage = false;

$fileName = Cache::getFileName( 'pages_expressions', $queryString );
// Važno ne micati ovo s POST jer inače se ne može imati forme na keširanim stranicama!
if( sizeof( $_POST ) == 0 && $fileName && is_file( $fileName ) ) {

	import( FRAMEWORK . 'execute_cached_page.php' );

	$executedCachedPage = executeCachedPage( $fileName, $queryString );

}

///////////////////////////////////////////////////////////////////////////////

//Db::open();

$application = new Application();
$pageAliases = $application->getPageAliases();
if( ! is_array( $pageAliases ) ) {
	$pageAliases = array();
}
global $application, $pageAliases;

foreach( $pageAliases as $page => $alias ) {
	if( $alias == $_GET[ 'page' ] ) {
		$_GET[ 'page' ] = $page;
	}
}

//////////////////////////////////////////////////////

$application->onStart();

//////////////////////////////////////////////////////

if( strlen( $_GET[ 'page' ] ) == 0 ) {
	$_GET[ 'page' ] = Application::MAIN_PAGE;
}

if( ! $executedCachedPage ) {
	import( FRAMEWORK, 'execute_page.php' );
}

$time = ( microtime( true ) - STARTED );
$application->onEnd();
Logs::info( 'Page execution time:', $time );

if( Logs::isSave() ) {
	$logs = Logs::getLogs();
	if( sizeof( $logs ) > 0 ) {
		$level = Logs::getLevel();
		$logs = implode( "\n", $logs );
		$sql = new Sql( 'insert delayed into log (level, log, created) values (:level, :log, now())' );
		$sql->setInt( 'level', $level );
		$sql->setString( 'log', $logs );
		$sql->execute();
		if( rand( 1, 1000 ) ) {
			// Once in a while -- delete logs older than 24h
			$deleteSql = new Sql( 'delete from log where created < adddate( now(), - 1 )' );
		}
	}

}

Db::close();

