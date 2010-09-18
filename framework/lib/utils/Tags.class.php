<?php

/** Utility methods for html (and xml) tags */
class Tags {

	private static $opened = array();

	public static function open( $tag, $params = null, $empty = false ) {
		if( ! $empty ) {
			self::$opened[] = $tag;
		}
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
		if( $tag === null ) {
			$tag = @array_pop( self::$opened );
		}
		if( $tag ) {
			echo '</' . $tag . '>';
		}
	}

	public static function closeAll() {
		while( sizeof( self::$opened ) > 0 ) {
			self::close();
		}
	}

	public static function reset() {
		self::$opened = array();
	}

}