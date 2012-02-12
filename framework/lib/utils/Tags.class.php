<?php

/** Utility methods for html (and xml) tags */
class Tags {

	private static $opened = array();

	public static function open( $tag, $params = null, $empty = false ) {
		if( ! $empty ) {
			self::$opened[] = $tag;
		}
		Logs::debug( 'Nakon open:', self::$opened );
		echo '<' . $tag;
		if( is_array( $params ) ) {
			foreach( $params as $key => $value ) {
				echo ' ' . $key . '="' . addcslashes( $value, '"' ) . '"';
			}
		}
		else if( is_string( $params ) ) {
				echo ' ' . $params;
		}
		if( $empty ) {
			echo '/>';
		}
		else {
			echo '>';
		}
	}

	public static function tag( $tag, $params = null, $contents = null ) {
		if( $contents == null ) {
			self::open( $tag, $params, true );
		}
		else {
			self::open( $tag, $params );
			echo $contents;
			self::close();
		}
	}

	public static function close( $tag = null ) {
		$tagToClose = @array_pop( self::$opened );
		if( $tag ) {
			if( $tagToClose != $tag ) {
				Log::error( 'Close wrong tag:', $tag );
			}
		} else {
			$tag = $tagToClose;
		}
		Logs::debug( 'Nakon closed:', self::$opened );
		if( $tag ) {
			echo '</' . $tag . '>';
		}
	}

	public static function closeAll() {
		while( sizeof( self::$opened ) > 0 ) {
			self::close();
		}
	}

	public static function getUnclosed() {
		return self::$opened;
	}

	// TODO: Similar to this method but for all instances of HtmlHelper
	public static function checkAllClosed() {
		global $application;
		if( self::$opened ) {
			Logs::error( 'Unclosed tags:', self::$opened );
			if( Application::DEBUG ) {
				throw new AppException( 'Unclosed tags!' );
			}
		}
	}

	public static function reset() {
		self::$opened = array();
	}

	public static function __callStatic( $method, $arguments ) {
		if( substr( $method, 0, 4 ) == 'open' ) {
			$tag = strtolower( substr( $method, 4 ) );
			if( sizeof( $arguments ) >= 2 ) {
				return self::open( $tag, $arguments[ 0 ], $arguments[ 1 ] );
			}
			if( sizeof( $arguments ) >= 1 ) {
				return self::open( $tag, $arguments[ 0 ] );
			}
			return self::open( $tag );
		}
		if( substr( $method, 0, 5 ) == 'close' ) {
			$tag = strtolower( substr( $method, 5 ) );
			return self::close( $tag );
		}
		if( substr( $method, 0, 3 ) == 'tag' ) {
			$tag = strtolower( substr( $method, 3 ) );
			if( sizeof( $arguments ) >= 2 ) {
				return self::tag( $tag, $arguments[ 0 ], $arguments[ 1 ] );
			}
			if( sizeof( $arguments ) >= 1 ) {
				return self::tag( $tag, $arguments[ 0 ] );
			}
			return self::tag( $tag );
		}
		throw new AppException( 'Unknown method:', $method );
	}

}

