<?php

/**
 * Za rad s cookijima
 */
class Cookies {

	function Cookies() {
	}

	public static function set( $name, $value, $expire = -1 ) {
		if( $expire == -1 ) {
			$expire = 60 * 60 * 24 * 14;
		}
		$expire += time();
		setcookie( $name, $value , $expire, '/' );
	}

	public static function get( $name ) {
		return @$_COOKIE[ $name ];
	}

	public static function delete( $name ) {
		setcookie( $name );
	}

	public static function deleteAll() {
		$cookies = array_keys( $_COOKIE );
		foreach( $cookies as $c ) {
			setcookie( $c );
		}
	}

}