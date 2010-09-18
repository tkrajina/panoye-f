<?php

/**
 * TODO: Prebaciti u Session.class.php
 */
class SessionSecurity {

	private function __construct() {
		throw new Exception();
	}

	/**
	 * Npr. Ako se zeli provjeriti je li korisnik admin onda kao prvo
	 * korisnik snimljen u sessionu mora imati metodu isAdmin(). Provjerava
	 * se sa <pre>SessionSecurity::is( 'Admin' )</pre>
	 *
	 * Jedini izuzetak je parametar 'Logged', ona je true ako postoji
	 * user u sessionu (tj ako Session::getUser() vraca objekt, a ne null)
	 *
	 * Ako zelimo neku stranicu ograniciti na koristenje samo administratoru,
	 * treba ju imenovati na sljedeci nacin:
	 * Admin:TaStranicaPage.class.php umjesto TaStranicaPage.class.php
	 *
	 * TODO
	 */
	public static function is( $param ) {
		$user = Session::getUser();
		if( $param == 'Logged' ) {
			return $user != null;
		}
		if( is_object( $user ) ) {
			$method = 'is' . $param;
			if( method_exists( $user, $method ) ) {
				return $user->$method();
			}
			return false;
		}
		return false;
	}

	public static function isLogged() {
		return self::is( 'Logged' );
	}

}