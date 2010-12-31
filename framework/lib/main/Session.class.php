<?

/**
 *
 * --------------------------------------------------------------
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * --------------------------------------------------------------
 *
 * (c) by Tomo Krajina
 *     aaa@puzz.info
 *
 */

defined( "APP" ) or die( "Sorry..." );

/**
 * Samo wrapper klasa za funkcije za rad sa sessionima.
 * kod includa klasa automatski pokrece session pa ne treba
 * prije toga!
 */
class Session {

	// Ne mijenjati ove konstante jer se mogu koristiti kod keširanja:
	const SESSION_USER_PARAM = '~~~USER';

	/**
	 * Koristi se samo zato da u početku kod uzimanja iz cache ne treba
	 * includati cijeli User.
	 */
	const SESSION_LOGIN = '~~~LOGIN';
	const SESSION_ID = '~~~ID';
	const SESSION_LAST_PAGE = '~~~LAST_PAGE';
	const SESSION_LAST_PAGE_FILE = '~~~LAST_PAGE_FILE';

	static function stop() {
		session_unset();
		session_destroy();
	}

	static function clear() {
		$_SESSION = array();
	}

	static function set( $key, $value ) {
		$_SESSION[ $key ] = $value;
	}

	static function get( $key ) {
		return @$_SESSION[ $key ];
	}

	/**
	 * Session id kojeg ove metoda vraca <i>nije</i> session_id() od PHP-a nego vrijednost
	 * koja se kreira u ovoj klasi.
	 */
	static function getId() {
		$id = self::get( self::SESSION_ID );
		if( ! $id ) {
			$id = md5( time() . rand( 1, 10000 ) );
			self::set( self::SESSION_ID, $id );
		}
		return $id;
	}

	static function toString() {
		reset( $_SESSION );
		$result = array();
		foreach( $_SESSION as $key => $value ) {
			$result[] = "$key: $value";
		}
		return "Session: {" . implode( ",", $result ) . "}";
	}

	static function getUser() {
		return @$_SESSION[ self::SESSION_USER_PARAM ];
	}

	static function setUser( $user = null ) {
		return @$_SESSION[ self::SESSION_USER_PARAM ] = $user;
	}

}
