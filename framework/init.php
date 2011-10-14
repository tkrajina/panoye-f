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

