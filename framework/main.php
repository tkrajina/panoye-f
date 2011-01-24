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

Logs::info( 'REMOTEADDR:', $_SERVER[ 'REMOTE_ADDR' ] );
Logs::info( 'AGENT:', $_SERVER[ 'HTTP_USER_AGENT' ] );
Logs::info( 'GET:', $_GET );
if( $_POST ) {
	// Only keys for POST, because otherwise the password would be 
	// visible in the log (TODO):
	Logs::info( 'POST:', array_keys( $_POST ) );
}

///////////////////////////////////////////////////////////////////////////////

// 1. provjera da nije u cache-u, ako jest onda prikazujemo tu stranicu
// bez ikakvih drugih icludova:

$queryString = $_SERVER[ 'QUERY_STRING' ];

$fileName = Cache::getFileName( 'pages_expressions', $queryString );
// Važno ne micati ovo s POST jer inače se ne može imati forme na keširanim stranicama!
if( sizeof( $_POST ) == 0 && $fileName && is_file( $fileName ) ) {

	/** $gzipped should be gzipped with gzcompress() ! */
	function printGzippedPage( $gzipped ) {
		$httpAcceptEncoding = $_SERVER[ 'HTTP_ACCEPT_ENCODING' ];
		if( headers_sent() ) {
			$encoding = false;
		}
		elseif( strpos( $httpAcceptEncoding, 'x-gzip' ) !== false ) {
			$encoding = 'x-gzip';
		}
		elseif( strpos( $httpAcceptEncoding, 'gzip' ) !== false ) {
			$encoding = 'gzip';
		}
		else{
			$encoding = false;
		}

		if( $encoding ) {
			header( 'Content-Encoding: ' . $encoding );
	        print( "\x1f\x8b\x08\x00\x00\x00\x00\x00" );
			echo $gzipped;
		}
		else {
			echo gzuncompress( $gzipped );
		}
	}
	@include $fileName;

	$pageFileName = Cache::getFileName( 'pages_cache', $queryString );
	if( is_file( $pageFileName ) ) {

		$seconds = (int) ( time() - filemtime( $pageFileName ) );
		$logged = is_object( Session::getUser() );

		if( is_callable( '___cache_content_type' ) ) {
			$contentType = @___cache_content_type();
			if( ( (int) @strlen( $contentType ) ) > 0 ) {
				$put->setHeader( 'Content-Type', $contentType );
			}
		}

		// Ako je stranica prestara: brišemo ju:
		if( $seconds > 60 * 60 * 48 ) {
			@unlink( $fileName );
			@unlink( $pageFileName );
		}
		else if( is_callable( '___cache_function' ) && ___cache_function( $seconds, $logged ) ) {
			printGzippedPage( file_get_contents( $pageFileName ) );

			// Treba li nastaviti nakon cache-a:
			if( is_callable( '___cache_continue' ) && ___cache_continue( $seconds, $logged ) ) {
				define( 'CONTINUE_AFTER_CACHE', true );
			}
			else {
				die();
			}
		}
	}
	else {
			@unlink( $fileName );
	}
}

///////////////////////////////////////////////////////////////////////////////

import( FRAMEWORK, 'FrameworkApplicationObject.class.php' );
import( APP, 'Application.class.php' );
import( FRAMEWORK . 'lib/db/Db.class.php' );

function customErrorReporting( $errno, $errstr, $errfile, $errline, $errcontext ) {
	Logs::error( 'Error: [' . $errno . '] ' . $errstr . ' in ' . $errfile . ' (line:' . $errline . ') ' . $errcontext );
}

set_error_handler( 'customErrorReporting', E_ALL );

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

import( FRAMEWORK, 'execute_page.php' );

// Save logs:
if( Logs::isSave() ) {
	$logs = Logs::getLogs();
	if( sizeof( $logs ) > 0 ) {
		$level = Logs::getLevel();
		$logs = implode( "\n", $logs );
		$sql = new Sql( 'insert delayed into log (level, log, created) values (:level, :log, now())' );
		$sql->setInt( 'level', $level );
		$sql->setString( 'log', $logs );
		$sql->execute();
		if( $rand( 1, 1000 ) ) {
			// Once in a while -- delete logs older than 24h
			$deleteSql = new Sql( 'delete from log where created < adddate( now(), - 1 )' );
		}
	}

}

Db::close();

$time = ( microtime( true ) - STARTED );
$application->onEnd();
// Pripaziti jer ovo ispisuje i kad je u pitanju javascript!
// echo '<!--' . $time . '-->';
