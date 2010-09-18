<?php

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

/**
 * Funkcije za rad s datotekama
 */
class Files {
	/**
	 * Vraca array s datotekama u odredjenom direktoriju
	 * vraca i direktorije!
	 */
	public static function getAll( $path = "." ) {
		// echo "path je $path<br/>";
      $handle = @opendir( $path );
		if( ! $handle ) {
			return false;
		}
		$ret = array();
      while( $f = readdir( $handle ) ) {
         if( $f == "." || $f == ".." ) {
            continue;
         }
			$ret[] = $f;
      }
      closedir( $handle );
		return $ret;
   }

	public static function getFiles( $path ) {
		$temp = self::getAll( $path );
		if( ! $temp ) {
			return false;
		}
		$ret = array();
		foreach( $temp as $f ) {
			if( is_file( "$path/$f" ) ) {
				$ret[] = $f;
			}
		}
		return $ret;
	}

	public static function getDirectories( $path ) {
		$temp = self::getAll( $path );
		if( ! $temp ) {
			return false;
		}
		$ret = array();
		foreach( $temp as $f ) {
			if( is_dir( "$path/$f" ) ) {
				$ret[] = $f;
			}
		}
		return $ret;
	}

	/**
	 * Iz patha vraca ime datoteke
	 */
	public static function getFileName( $path ) {
		$temp = explode( "/", $path );
		if( sizeof( $temp ) <= 1 ) {
			return $path;
		}
		else {
			return $temp[ sizeof( $temp ) - 1 ];
		}
	}

	/**
	 * Vraca ime datoteke bez ekstenzije
	 * TODO: neko krace ime smisliti
	 */
	public static function getNameWithoutExtension( $path ) {
		$fName = self::getFileName( $path );
		$temp = explode( ".", $fName );
		if( sizeof( $temp ) == 0 ) {
			return $fName;
		}
		array_pop( $temp );
		return implode( ".", $temp );
	}

	/**
	 * Vraca samo stazu do odredjene datoteke (ako nema
	 * datoteke onda vraca taj isti $path)
	 */
	public static function getPath( $path ) {
		$path = trim( $path );
		$end = $path[ strlen( $path ) - 1 ];
		if( ! strpos( $path, "/" ) ) {
			// dakle samo ime datoteke a direktorij je onaj trenutni:
			return ".";
		}
		if( $end == "/" || $end == "\\" ) {
			return $path;
		}
		else {
			$lastSlash = strrpos( $path, "/" );
			if( $lastSlash > 0 ) {
				return substr( $path, 0, $lastSlash );
			}
			else {
				return $path;
			}
		}
	}

	/**
	 * Vraca ekstenziju odredjene datoteke
	 */
	public static function getExtension( $path ) {
		$fName = self::getFileName( $path );
		$temp = explode( ".", $fName );
		if( sizeof( $temp ) <= 1 ) {
			return "";
		}
		return $temp[ sizeof( $temp ) - 1 ];
	}

	public static function isDir( $path ) {
		// echo "path je ... $path";
		if( is_dir( $path ) ) {
			// echo " je direktorij<br/>";
		}
		else {
			// echo " je file<br/>";
		}
		return is_dir( $path );
	}

// 2008-09-13
//	public static function isFile( $path ) {
//		return is_file( $path );
//	}

// 2008-09-13
//	/**
//	 * Provjerava da neki path ne pokusava preuzeti nesto izvan www
//	 * direktorija!
//	 * path je ok ako u sebi nema ".." ili "/" na pocetku (ili "\")!
//	 */
//	public static function pathOK( $path ) {
//		if( strpos( $path, "/" ) === 0 ||
//			strpos( $path, "\\" ) === 0 ||
//			strpos( $path, ".." ) > -1 )
//		{
//			return false;
//		}
//		else {
//			return true;
//		}
//	}

	public static function recursiveRemove( $dirname, $removeThis = true ) {
//		echo (int) is_dir( $dirname );
		if( ! is_dir( $dirname ) && ! is_file( $dirname ) && ! is_link( $dirname ) ) {
			return true;
		}
		echo (int) is_dir( $dirname );
		if( is_dir( $dirname ) ) {
			$dir_handle = opendir( $dirname );
		}
		while( $file = readdir( $dir_handle ) ) {
			if( $file != "." && $file != ".." ) {
				if( is_file( $dirname . "/" . $file ) || is_link( $dirname . "/" . $file ) ) {
					unlink( $dirname . "/" . $file );
				}
				else if( is_dir( $dirname . "/" . $file ) ) {
				 	self::recursiveRemove( $dirname . "/" . $file, true );
				}
			}
		}
		closedir( $dir_handle );
//		echo "Brisanje ... " . $dirname . "<br/>";
		if( $removeThis ) {
			rmdir( $dirname );
		}
		return true;
	}

}

?>
