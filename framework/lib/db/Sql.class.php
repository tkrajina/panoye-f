<?php

class Sql {

	private $template;

	private $generatedKey;

	public function __construct( $template = null ) {
		$this->template = $template;
		return $this;
	}

	public function append( $sqlChunk ) {
		$this->template .= ' ' . $sqlChunk;
	}

	/** Be careful when using this method because of SQL injection. */
	public function set( $parameter, $value ) {
		$this->template = str_replace( ':' . $parameter, $value, $this->template );
		return $this;
	}

	public function setAlfanumeric( $parameter, $value ) {
		$this->template = str_replace( ':' . $parameter, preg_replace( '/[^\w\d_]*/', '', '' . $value ), $this->template );
		return $this;
	}

	public function setInt( $parameter, $value ) {
		$this->template = str_replace( ':' . $parameter, (int) $value, $this->template );
		return $this;
	}

	public function setDecimal( $parameter, $value ) {
		$this->template = str_replace( ':' . $parameter, (double) $value, $this->template );
		return $this;
	}

	public function setString( $parameter, $value ) {
		Db::open();
		if( get_magic_quotes_gpc() ) {
			$value = stripslashes( $value );
		}
		else {
			$value = $value;
		}
		if( ! is_numeric( $value ) ) {
			$value = mysql_real_escape_string( $value );
		}
		$this->template = str_replace( ':' . $parameter, '\'' . $value . '\'', $this->template );
		return $this;
	}

	public function setTimestamp( $parameter, $value ) {
		$this->setString( $parameter, $value->toSqlTime() );
	}

	public function setDate( $parameter, $value ) {
		$this->setString( $parameter, $value->toSqlDate() );
	}

	public function getSql() {
		return $this->template;
	}

	public function select( $className = 'Object' ) {
		Db::open();
		Logs::debug( $this->template );
		return new DbIterator( $this->template, $className );
	}

	/**
	 * Returns an array with ids as keys and objects as values.
	 */
	public function idArray( $className = 'Object' ) {
		$iterator = $this->select( $className );
		$result = array();
		while( $object = $iterator->next() ) {
			$result[ $object->getId() ] = $object;
		}
		return $result;
	}

	/** Only the first row of the query. */
	public function first( $className = 'Object' ) {
		Db::open();
		Logs::debug( $this->template );
		$result = @mysql_query( $this->template );
		if( $array = @mysql_fetch_array( $result ) ) {
			$object = new $className();
			if( method_exists( $object, 'addSqlProperties' ) ) {
				$object->addSqlProperties( $array );
			}
			else {
				$object->addProperties( DbNames::dbToFramework( $array ) );
			}
			return $object;
		}
		return false;
	}

	public function update() {
		Db::open();
		Logs::debug( $this->template );
		$result = @mysql_query( $this->template );
		return @mysql_affected_rows( $result );
	}

	public function insert() {
		Db::open();
		Logs::debug( $this->template );
		$inserted = mysql_query( $this->template );
		if( $inserted ) {
			$this->generatedKey = @mysql_insert_id();
		}
		return $inserted;
	}

	public function execute() {
		Db::open();
		Logs::debug( $this->template );
		return @mysql_query( $this->template );
	}

	public function generatedKey() {
		return $this->generatedKey;
	}

}
