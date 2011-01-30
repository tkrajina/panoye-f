<?php

class Cache {

	/** Provjerava i kreira direktorij ukoliko jos ne postoji. */
	private static function checkDirectory( $directoryName ) {
		if( ! is_dir( CACHE . "$directoryName" ) ) {
			// TODO: Provjera ispravnosti imena direktorija?
			mkdir( CACHE . "$directoryName" );
			// Odmah i index.html da se ne bi vidio sadrzaj:
			file_put_contents( CACHE . $directoryName . '/index.html', '' );
		}
	}

	public static function getFileName( $cache, $fileName ) {
		$fileName = strtolower( preg_replace( "/[^\w\d_\-\.]+/", "-", @substr( $fileName, 0, 20 ) ) . '_' . substr( md5( '' . $fileName ), 0, 5 ) );
		return CACHE . "$cache/$fileName";
	}

	public static function save( $cache, $fileName, $content ) {
		self::checkDirectory( $cache );
		$f = fopen( self::getFileName( $cache, $fileName ), "w" );
		fwrite( $f, $content );
		fclose( $f );
	}

	/**
	 * Učitava u memoriju, ali ako je potrebno ispisivati u output => uvijek koristiti output()
	 */
	public static function load( $cache, $fileName ) {
		self::checkDirectory( $cache );
		$fn = self::getFileName( $cache, $fileName );
		if( is_file( $fn ) ) {
			return file_get_contents( $fn );
		}
		else {
			$result = null;
		}
		return $result;
	}

	public static function output( $cache, $file ) {
		self::checkDirectory( $cache );
		$fn = self::getFileName( $cache, $file );
		$handle = @fopen( $fn, 'r' );
		if( $handle ) {
			while( ! feof( $handle ) ) {
				$buffer = fgets( $handle, 4096 );
				flush();
				echo $buffer;
				unset( $buffer );
			}
			fclose($handle);
		}
	}

	/** Vraca starost datoteke u sekundama ili -1 ako ne postoji. */
	public static function getSeconds( $cache, $fileName ) {
		$fileName = self::getFileName( $cache, $fileName );
		$result = (int) ( time() - @filemtime( $fileName ) );
		if( $result <= 0 ) {
			return -1;
		}
		return $result;
	}

	public static function delete( $cache, $fileName ) {
		$file = self::getFileName( $cache, $fileName );
		if( is_file( $file ) ) {
			@unlink( $file );
		}
	}

	public static function isCached( $cache, $fileName ) {
		return is_file( self::getFileName( $cache, $fileName ) );
	}

	public static function cleanAll() {
		if( $handle = opendir( CACHE ) ) {
			while ( false !== ( $file = readdir( $handle ) ) ) {
				if( ( is_link( $file ) || is_file( $file ) ) && $file != "index.html" ) {
					unlink( CACHE . "$file" );
				}
				else if( is_dir( CACHE . "$cache" ) ) {
					Files::recursiveRemove( CACHE . "$cache" );
				}
			}
			closedir($handle);
		}
	}

}
