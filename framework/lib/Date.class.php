<?php

class Date extends Timestamp {

	private $valid = true;

    function __construct( $yearOrSqlString = null, $month = null, $day = null ) {
    	if( $month != null && $day != null ) {
	    	if( ! $yearOrSqlString ) {
	    		$year = date( 'Y', microtime( true ) );
	    	}
	    	if( ! $month ) {
	    		$month = date( 'n', microtime( true ) );
	    	}
	    	if( ! $day ) {
				$day = date( 'j', microtime( true ) );
	    	}
	    	$time = mktime( 12, 0, 0, $month, $day, $yearOrSqlString );
	    	if( ! $time || $time == -1 ) {
	    		$this->valid = false;
	    	}
    	}
    	else {
    		$time = strtotime( $yearOrSqlString );
    		if( ! $time ) {
    			$this->valid = false;
    		}
    	}
    	parent::__construct( $time );
    }

	public function after( $date ) {
		if( ! is_object( $date ) ) {
			return null;
		}
		$d1 = $this->getYear() * 1000 + $this->getMonth() * 100 + $this->getDay();
		$d2 = $date->getYear() * 1000 + $date->getMonth() * 100 + $date->getDay();
		return $d1 > $d2;
	}

	public function before( $date ) {
		if( ! is_object( $date ) ) {
			return null;
		}
		$d1 = $this->getYear() * 1000 + $this->getMonth() * 100 + $this->getDay();
		$d2 = $date->getYear() * 1000 + $date->getMonth() * 100 + $date->getDay();
		return $d1 < $d2;
	}

	public function equals( $date ) {
		if( ! is_object( $date ) ) {
			return null;
		}
		$d1 = $this->getYear() * 1000 + $this->getMonth() * 100 + $this->getDay();
		$d2 = $date->getYear() * 1000 + $date->getMonth() * 100 + $date->getDay();
		return $d1 == $d2;
	}

	public function __toString() {
		return $this->toSqlDate();
	}

	public function isValid() {
		return $this->valid;
	}

}