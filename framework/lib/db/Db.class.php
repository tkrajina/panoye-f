<?php

/**
 * Db Utility methods.
 *
 * Nije potrebno eksplicitno pozivati open() metodu, nego samo close() !
 */
class Db {

	private static $opened = false;

	private static $dbLink = null;

	/**
	 * U principu nije potrebno eksplicitno pozvati ovu funkciju osim
	 * ako se negdje bas ne pozivaju mysql_*() funkcije.
	 */
	public static function open() {
		if( self::$opened ) {
			return;
		}
		self::$dbLink = @mysql_connect( Application::DB_HOST, Application::DB_USER, Application::DB_PASSWORD );
		if( ! self::$dbLink ) {
			throw new AppException( mysql_error() );
		}
		if( ! @mysql_select_db( Application::DB_DATABASE ) ) {
			throw new AppException( mysql_error() );
		}
		self::$opened = true;
		debug( 'Konekcija otvorena' );
	}

	public static function close() {
		if( self::$opened ) {
			self::$opened = false;
			@mysql_close();
		}
	}

	public static function prepareInt( $value ) {
		return (int) $value;
	}

	public static function prepareDecimal( $value ) {
		return (double) $value;
	}

	public static function prepareString( $value ) {
		self::open(); // Jer inace ne bi radile ove sljedece funkcije:
	    if( get_magic_quotes_gpc() ) {
	        $value = stripslashes( $value );
	    }
		if( ! is_numeric( $value ) ) {
	        $value = mysql_real_escape_string($value);
	    }
		return '\'' . $value . '\'';
	}

	/** Nigdje ne pozivati mysql_query nego ovu metodu. */
	public static function query( $sql ) {
		if( ! self::$opened ) {
			self::open();
		}

		if( Application::DEBUG ) {
			$start = microtime( true );
		}

		$result = @mysql_query( $sql );

		if( Application::DEBUG ) {
			$time = microtime( true ) - $start;
			info( $sql . '(' . $time . ')' );
		}

		return $result;
	}

	public static function loadIterator( $sql, $className ) {
		return new DbIterator( $sql, $className );
	}

	public static function loadObjects( $sql, $className, $limit = 10000 ) {
		$result = array();
		$query = @self::query( $sql );
		if( ! $query ) {
			throw new AppException( $sql . ': ' . @mysql_error() );
		}
		$n = 0;
		while( $n <= $limit && $array = @mysql_fetch_array( $query ) ) {
			++ $n;
			$object = new $className();
			$object->addSqlProperties( $array );
			$result[] = $object;
		}
		return $result;
	}

	public static function loadObject( $sql, $className ) {
		$result = self::loadObjects( $sql, $className, 1 );
		return @$result[ 0 ];
	}

	public static function executeUpdate( $sql ) {
		$query = @self::query( $sql );
		if( ! $query ) {
			$err = @mysql_error();
			error( $err );
			throw new AppException( $err );
		}
		$result = (int) @mysql_affected_rows( $query );
//		return $result;
		return true;
	}

	public static function execute( $sql ) {
		$query = @self::query( $sql );
		if( ! $query ) {
			throw new AppException( @mysql_error() );
		}
		return (int) $query;
	}

	/** Samo vraca koliko je redaka u rezultatu. */
	public static function size( $sql ) {
		$query = @self::query( $sql );
		return (int) @mysql_num_rows( $query );
	}

	/** Vraca id ili baca Exception */
	public static function executeInsert( $sql ) {
		$query = @self::query( $sql );
		if( ! $query ) {
			throw new AppException( @mysql_error() );
		}
		return (int) @mysql_insert_id();
	}

	/** Vraca null ako nista nije nadjeno. */
	public static function loadArray( $sql ) {
		$result = array();
		$query = @self::query( $sql );
		$err = @mysql_error();
		if( ! $query ) {
			throw new AppException( $sql . ': ' . $err );
		}
		$array = @mysql_fetch_array( $query );
		if( ! is_array( $array ) ) {
			return null;
		}
		return $array;
	}

}
