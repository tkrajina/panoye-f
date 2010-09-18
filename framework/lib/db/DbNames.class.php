<?php

class DbNames {

	private static $dbToFramework = array();

	/**
	 * Ime iz baze podataka pretvara u ime koje se koristi u frameworku.
	 * Npr. comment_number -> CommentNumber
	 */
	public static function dbToFramework( $stringOrArray ) {
		if( is_array( $stringOrArray ) ) {
			$result = array();
			reset( $stringOrArray );
			foreach( $stringOrArray as $key => $value ) {
				$result[ self::dbToFramework( '' . $key ) ] = $value;
			}
			return $result;
		}
		else {
			$string = strtolower( '' . $stringOrArray );
			return substr( str_replace( ' ', '', ucwords( '_' . str_replace( '_', ' ', $string ) ) ), 1 );
		}
	}

	/**
	 * Ime koristeno u frameworku pretvara u ime kakvo se koristi u bazi.
	 * Npr. CommentNumber -> comment_number
	 */
	public static function frameworkToDb( $stringOrArray ) {
		if( is_array( $stringOrArray ) ) {
			$result = array();
			reset( $stringOrArray );
			foreach( $stringOrArray as $key => $value ) {
				$result[ self::frameworkToDb( '' . $key ) ] = $value;
			}
			return $result;
		}
		else {
			$string = '' . $stringOrArray;
			return strtolower( preg_replace( '/(.)([A-Z])/', '$1_$2', $string ) );
		}
	}

}
