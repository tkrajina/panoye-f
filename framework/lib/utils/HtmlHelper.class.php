<?php

/** Utility methods for html (and xml) tags */
class HtmlHelper {

	private $opened;

	public function __construct() {
		$this->opened = array();
	}

	public function open( $tag, $params = null, $empty = false ) {
		if( ! $empty ) {
			$this->opened[] = $tag;
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

	public function tag( $tag, $params = null, $contents = null ) {
		if( $contents == null ) {
			$this->open( $tag, $params, true );
		}
		else {
			$this->open( $tag, $params );
			echo $contents;
			$this->close();
		}
	}

	public function close( $tag = null ) {
		if( $tag === null ) {
			$tag = @array_pop( $this->opened );
		}
		if( $tag ) {
			echo '</' . $tag . '>';
		}
	}

	public function closeAll() {
		while( sizeof( $this->opened ) > 0 ) {
			$this->close();
		}
	}

	public function getUnclosed() {
		return $this->opened;
	}

	// TODO: Similar to this method but for all instances of HtmlHelper
	public function checkAllClosed() {
		global $application;
		if( $this->opened ) {
			if( Application::DEBUG ) {
				throw new AppException( 'Unclosed tags!' );
			}
			else {
				Logs::error( 'Unclosed tags:', $this->opened );
			}
		}
	}

	public function reset() {
		$this->opened = array();
	}

	function __call( $method, $arguments ) {
		if( substr( $method, 0, 4 ) == 'open' ) {
			$tag = strtolower( substr( $method, 4 ) );
			if( sizeof( $arguments ) >= 2 ) {
				return $this->open( $tag, $arguments[ 0 ], $arguments[ 1 ] );
			}
			if( sizeof( $arguments ) >= 1 ) {
				return $this->open( $tag, $arguments[ 0 ] );
			}
			return $this->open( $tag );
		}
		if( substr( $method, 0, 5 ) == 'close' ) {
			$tag = strtolower( substr( $method, 5 ) );
			return $this->close( $tag );
		}
		if( substr( $method, 0, 3 ) == 'tag' ) {
			$tag = strtolower( substr( $method, 3 ) );
			if( sizeof( $arguments ) >= 2 ) {
				return $this->tag( $tag, $arguments[ 0 ], $arguments[ 1 ] );
			}
			if( sizeof( $arguments ) >= 1 ) {
				return $this->tag( $tag, $arguments[ 0 ] );
			}
			return $this->tag( $tag );
		}
		throw new AppException( 'Unknown method:', $method );
	}

}
