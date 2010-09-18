<?php

class ObjectTest extends AppTest {

	public function testConstructorWithArray() {
		$o = new Object( array( 'test' => 1 ) );
		$this->assertTrue( $o->getTest() === 1 );
		$this->assertTrue( $o->isTest() );
		$this->assertTrue( ! $o->isRandom123() );
	}

	public function testConstructorWithInteger() {
		$o = new Object( 12 );
		$this->assertTrue( $o->getId() === 12 );
	}

	public function testGetMethod() {
		$o = new Object();
		$o->setTest( 123 );
		$this->assertTrue( $o->getTest() === $o->get( 'test' ) );
	}

	public function testProperties() {
		$o = new Object();
		$o->setTest( 123 );
		$this->assertTrue( sizeof( $o->getProperties() ) === 1 );
	}

	public function testSetProperties() {
		$o = new Object();
		$o->setTest( 123 );
		$o->addProperties( array( 'aaa' => 'a' ) );

		$this->assertTrue( sizeof( $o->getProperties() ) === 2 );
		$this->assertTrue( $o->getAaa() === 'a' );
	}

	public function testAddPropertiesFromObject() {
		$o = new Object();
		$o2 = new Object();
		$o->setTest( 123 );
		$o->addProperties( array( 'aaa' => 'a' ) );

		$o2->addProperties( $o );

		$this->assertTrue( sizeof( $o2->getProperties() ) === 2 );
		$this->assertTrue( $o2->getAaa() === 'a' );
	}



}