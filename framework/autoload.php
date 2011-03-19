<?php

defined( 'APP' ) or die( '!' );

/**
 * Automatski ucitava *.class.php datoteke koje se nalaze u
 * lib/ ili app/lib/ direktorijima i app/objects/
 */
function __autoload( $className ) {
	// TODO: Pogledati koliko se toga učitava i gdje se može smanjiti broj
	// učitavanja:
	$result = AutoloadUtils::getClassPaths();
	if( ! @$result[ $className ] ) {
		// TODO: Log greska
	}
	if( @$result[ $className ] ) {
		require_once @$result[ $className ];
	}
}

class AutoloadUtils {

	private static $paths = null;

	public static function getClassPaths() {

		if( is_array( self::$paths ) ) {
			return self::$paths;
		}

		$directories = array( FRAMEWORK . 'lib/', APP . 'lib/', APP . 'objects/', APP . 'dao/' );

		$result = unserialize( Cache::load( 'framework', 'paths' ) );

		if( ! is_array( $result ) ) {
			$result = array();
			foreach( $directories as $directory ) {
				$newPaths = AutoloadUtils::findClasses( $directory );
				$result = array_merge( $result, $newPaths );
			}
			Cache::save( 'framework', 'paths', serialize( $result ) );
		}
		self::$paths = $result;
		return self::$paths;
	}

	private static function findClasses( $directory ) {
		$result = array();
		if( ! is_dir( $directory ) ) {
			return $result;
		}
		if( $handle = opendir( $directory ) ) {
			while( false !== ( $file = readdir( $handle ) ) ) {
				$path = $directory . '/' . $file;
				if( preg_match( '/.*\.class\.php$/', $file ) ) {
					$result[ str_replace( '.class.php', '', $file ) ] = $path;
				}
				if( $file != '.' && $file != '..' && is_dir( $path ) ) {
					$result = array_merge( $result, self::findClasses( $path ) );
				}
			}

			closedir($handle);
		}
		return $result;
	}

}
