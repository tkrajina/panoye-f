<?php

/** Ova klasa se koristi i za date i za time tip iz baze. */
class Timestamp {

	private $time;

	/** $time treba biti broj ili string. */
	public function __construct( $time = null ) {
		if( is_numeric( $time ) ) {
			$this->time = $time;
		}
		else if( is_string( $time ) ) {
			$this->time = @strtotime( $time );
		}
		else if( $time == null ) {
			$this->time = microtime( true );
		}
	}

	public function getHours() {
		return (int) @date( 'G', $this->time );
	}

	public function getMinutes() {
		return (int) @date( 'i', $this->time );
	}

	public function getSeconds() {
		return (int) @date( 's', $this->time );
	}

	public function getDay() {
		return (int) @date( 'j', $this->time );
	}

	public function getWeekDay() {
		return (int) @date( 'N', $this->time );
	}

	public function getYearDay() {
		return (int) @date( 'z', $this->time );
	}

	public function getMonth() {
		return (int) @date( 'n', $this->time );
	}

	public function getYear() {
		return (int) @date( 'Y', $this->time );
	}

	/**
	 * Pogledati:
	 * http://www.php.net/manual/en/ref.datetime.php#datetime.constants
	 * za ostale formate.
	 */
	public function toString( $format = 'Y-m-d H:i:s.u' ) {
		// Za sluèaj da je starija verzija php5 - u su milisekunde:
		$format = str_replace( 'u', (int) ( 1000 * ( $this->time - (int) $this->time ) ), $format );
		$result = @date( $format, $this->time );
		return $result;
	}

	public function toSqlDate( $format = 'Y-m-d' ) {
		return $this->toString( $format );
	}

	public function toSqlTime( $format = 'Y-m-d H:i:s.u' ) {
		return $this->toString( $format );
	}

	/** Umjesto substract dodavati negativne vrijednosti. */
	public function add( $seconds ) {
		$this->time += $seconds;
	}

	public function addMinutes( $min ) {
		$this->time += 60 * $min;
	}

	public function addHours( $hours ) {
		$this->time += 60 * 60 * $hours;
	}

	public function addDays( $d ) {
		$this->time += 24 * 60 * 60 * $d;
	}

	public function after( $timeOrTimestamp ) {
		if( is_object( $timeOrTimestamp ) ) {
			return $this->time > $timeOrTimestamp->getTime();
		}
		return $this->time > (int) $timeOrTimestamp;
	}

	public function before( $timeOrTimestamp ) {
		if( is_object( $timeOrTimestamp ) ) {
			return $this->time < $timeOrTimestamp->getTime();
		}
		return $this->time < (int) $timeOrTimestamp;
	}

	public function getTime() {
		return $this->time;
	}

	public function __toString() {
		return $this->toSqlTime();
	}

}
