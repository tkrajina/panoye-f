<?php //+

defined( 'APP' ) or die( '!' );

// ----------------------------------------------------------------------------
// Pomoćne konstante
// ----------------------------------------------------------------------------

define( 'BR', '<br/>' );
define( 'NL', "\n" );

// ----------------------------------------------------------------------------
// Pomocne funkcije (samo za debug)
// ----------------------------------------------------------------------------

function e( $message = '' ) { throw new Exception( $message ); }
function p( $a ) { echo '<pre>' . $a . '</pre>'; }
function pdie( $a ) { echo '<pre>' . $a . '</pre>'; die(); }
function d( $a ) { echo '<pre>'; var_dump( $a ); echo '</pre>'; }
function ddie( $a ) { echo '<pre>'; var_dump( $a ); echo '</pre>'; die(); }

function url( $page, $argOrObject = null, $args = array() ) {

	$result = preg_replace( '/[^\w\d]+/', ' ', strtolower( $page ) );
	$result = str_replace( ' ', '-', trim( $result ) );

	// Mozda ta stranica ima neki alias:
	global $pageAliases;
	if( isset( $pageAliases[ $result ] ) ) {
		$result = $pageAliases[ $result ];
	}

	$sefUrl = '';
	if( $argOrObject ) {
		if( is_object( $argOrObject ) ) {
			$sefUrl = $argOrObject->getSefUrl();
			if( strlen( $sefUrl ) == 0 ) {
				$sefUrl = (int) $argOrObject->getId();
			}
		}
		else {
			$sefUrl = $argOrObject;
		}
	}

	if( ! Application::SEF_LINKS ) {
		$args[ 'page' ] = $result;
		if( $argOrObject ) {
			$args[ 'arg' ] = $sefUrl;
		}
		$r = array();
		foreach( $args as $key => $value ) {
			$r[] = $key . '=' . urlencode( $value );
		}
		return '?' . implode( '&', $r );
	}

	if( strlen( $sefUrl ) > 0 ) {
		$result .= '/' . $sefUrl;
	}

	$_args = array();
	if( is_array( $args ) && sizeof( $args ) > 0 ) {
		foreach( $args as $key => $value ) {
			$_args[] = urlencode( $key ) . '=' . urlencode( $value );
		}
		$result .= '?' . implode( '&', $_args );
	}

	if( $result == Application::MAIN_PAGE ) {
		return './';
	}

	return $result;
}

function htmlLink( $html, $page, $argOrObject = null, $args = array(), $params = '' ) {
	return '<a href="' . url( $page, $argOrObject, $args ). '" ' . $params . '>' . $html . '</a>';
}

function nofollowLink( $html, $page, $argOrObject = null, $args = array() ) {
	return '<a rel="nofollow" href="' . url( $page, $argOrObject, $args ) . '">' . $html . '</a>';
}

function confirmLink( $html, $question, $page, $argOrObject, $args = array() ) {
	return '<a href="#" onclick="if(confirm(\'' . $question . '\')){document.location=\'' . url( $page, $argOrObject, $args ) . '\';};return false;">' . $html . '</a>';
}

function printTimer( $text = '' ) {
	global $__timer;
	if( ! $__timer ) {
		$__timer = 1000 * microtime( true );
	}
	$time = 1000 * microtime( true );
	d( $text . ':' . ( (int) ( $time - $__timer ) ) . '...' . ( (int) ( ( $time - 1000 * STARTED ) ) ) . '...' . microtime( true ) );
	$__timer = 1000 * microtime( true );
}

$logMessages = array();
global $logMessages;

/** Applikacija moze samostalno odluciti da zeli posebno logirati greske. */
function onError( $msg ) {
	global $application;
	if( method_exists( $application, 'saveError' ) ) {
		$application->saveError( $msg );
	}
}

function debug( $msg ) {
	global $logMessages;
	$logMessages[] = '[debug] ' . $msg;
}

function info( $msg ) {
	global $logMessages;
	$logMessages[] = '[info] ' . $msg;
}

function error( $msg ) {
	global $logMessages;
	onError( $msg );
	$logMessages[] = '[error] <span style="font-weight: bold">' . $msg . '</span>';
}

function fatal( $msg ) {
	global $logMessages;
	onError( $msg );
	$logMessages[] = '[FATAL] <span style="font-weight: bold">' . $msg . '</span>';
}

function printErrors() {
	global $logMessages;
	if( ! is_array( $logMessages ) ) {
		return;
	}
	foreach( $logMessages as $message ) {
		echo '<div style="background: #dddddd; margin: 1px; padding: 1px;">' . $message . '</div>';
		}
	}

function printErrorFrame() {
	if( Application::DEBUG ) {
?>
<script language='javascript'>
function toggleDebugFrame() {
	frame = document.getElementById( 'debugFrame' )
	smallFrame = document.getElementById( 'smallDebugFrame' )
	current = frame.style.visibility
	if( current == 'hidden' ) {
		frame.style.visibility = 'visible'
		smallFrame.style.visibility = 'hidden'
	}
	else {
		frame.style.visibility = 'hidden'
		smallFrame.style.visibility = 'visible'
	}
}
</script>
<div id='debugFrame' style='position: absolute; margin: 0px; padding: 2px; right: 5px; top: 5px; visibility: hidden; border: solid 1px black; background: white; width: 50%;'>
<span style='font-weight: bold'> <a href='#' onclick='toggleDebugFrame(); return false;'>Debug info</a> </span>
<?php printErrors(); ?>
</div>
<div id='smallDebugFrame' style='position: absolute; margin: 0px; padding: 2px; right: 5px; top: 5px; visibility: visible; border: solid 1px black; background: white;'>
<span style='font-weight: bold'> <a href='#' onclick='toggleDebugFrame(); return false;'>Debug info</a> </span>
</div>
<?php
	}
}

function assertTrue( $boolean, $error ) {
	if( ! $boolean ) {
		throw new AppException( 'Assertion failed:' . $error );
	}
}