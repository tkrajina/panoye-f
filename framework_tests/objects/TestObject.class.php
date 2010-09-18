<?php

class TestObject extends AppObject {

	public function __construct( $arg = null ) {
		parent::__construct( $arg );
	}

	public function getTableName() {
		return 'panoye_app_test';
	}

	public function getTableColumns() {
		return array(
			'str' => self::STRING,
			'jkl' => self::STRING,
			'sef_url' => self::STRING,
			'title' => self::STRING,

		);
	}

	public function validateStr() {
		if( preg_match( '/^.+$/', '' . $this->getStr() ) == 0 ) {
			return 'str not valid';
		}
	}

	public function validateJkl() {
		if( preg_match( '/^.+$/', '' . $this->getJkl() ) == 0 ) {
			return 'jkl not valid';
		}
	}

	public function validateSefUrl() {
		if( preg_match( '/^.+$/', '' . $this->getSefUrl() ) == 0 ) {
			return 'sefUrl not valid';
		}
	}

	public function validateTitle() {
		if( preg_match( '/^.+$/', '' . $this->getTitle() ) == 0 ) {
			return 'title not valid';
		}
	}

	public function getUrlFrom() { return 'str'; }

	public function __toString() { return $this->get( 'str' ); }

}