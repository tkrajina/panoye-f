<?php

class DateTest extends AppTest {

	public function testConstructor() {
		$d = new Date( 2009, 02, 03 );
		p( 'dan:' . $d->getDay() );
		p( 'mj:' . $d->getMonth() );
		p( 'godina:' . $d->getYear() );
		$this->assertTrue( $d->getDay() == 3 );
		$this->assertTrue( $d->getMonth() == 2 );
		$this->assertTrue( $d->getYear() == 2009 );
	}

	public function testCurrentDate() {
		$d = new Date();
    	$year = date( 'Y', microtime( true ) );
    	$month = date( 'n', microtime( true ) );
    	$day = date( 'j', microtime( true ) );
    	d( $day . '.' . $month . '.' . $year );
    	d( $d->toString() );
    	$this->assertTrue( $d->getYear() == $year );
    	$this->assertTrue( $d->getMonth() == $month );
    	$this->assertTrue( $d->getDay() == $day );
	}

	public function testSqlString() {
		$d = new Date( 2009, 02, 03 );
		$this->assertTrue( $d->toSqlDate() == '2009-02-03' );
	}

	public function testBeforeAfter() {
		$d1 = new Date( 2009, 02, 03 );
		$d2 = new Date( 2009, 03, 03 );
		$this->assertTrue( $d1->before( $d2 ) );
		$this->assertTrue( ! $d1->after( $d2 ) );
	}

	public function testEquals() {
		$d1 = new Date();
		$d2 = new Date( 2009, 03, 03 );
		$this->assertTrue( ! $d1->equals( $d2 ) );
		$this->assertTrue( $d1->equals( new Timestamp() ) );
	}

}