<?php

class Validations {

    private function __construct() {
    }

	public static function isDecimal( $string ) {
		return preg_match( '/^[+\-]{0,1}\d[\d\.]*$/', '' . $string );
	}

	public static function isInteger( $string ) {
		return preg_match( '/^[+\-]{0,1}\d+$/', '' . $string );
	}

	public static function between( $number, $min, $max ) {
		return $number >= $min && $number <= $max;
	}

	public static function isEmail( $email ) {
		return preg_match(
			'/^[\w\d][\w\d\.\-]*[\w\d]@[\w\d][\w\d\.\-]*[\w\d]$/',
			trim( '' . $email ) );
	}

	public static function isUrl( $url ) {
		$url = trim( '' . $url );
		return preg_match( '/^http.*:\/\/.*$/', $url ) || preg_match( '/^https.*:\/\/.*$/', $url ) ;
	}

	public static function strlenBetween( $string, $min, $max ) {
		$size = strlen( $string );
		return $size >= $min && $size <= $max;
	}

}