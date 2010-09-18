<?php

class Visit extends AppObject {

    public function __construct( $propertiesOrObject = null ) {
    	parent::__construct( $propertiesOrObject );
    }

	public function getTableName() {
		return 'visit';
	}

	public function getTableColumns() {
		return array(
		    'login' => self::STRING,
		    'ip' => self::STRING,
		    'browser' => self::STRING,
		    'referer' => self::STRING,
		    'url' => self::STRING,
		    'post' => self::STRING,
		    'time' => self::STRING,
		    'form_response' => self::STRING,
		);
	}

	public function prepareArray( $array = array() ) {
		$result = '';
		foreach( $array as $k => $v ) {
			if( strlen( $v ) > 70 ) {
				$v = substr( $v, 0, 70 ) . '...';
			}
			$v = str_replace( "\n", "\\n", $v );
			$result .= $k . ':' . $v . "\n";
		}
		return trim( $result );
	}

}