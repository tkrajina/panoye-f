<?php

require_once 'app/test/objects/TestObject.class.php';

class ApplicationObjectTest extends AppTest {

	public function onStart() {
		include APP . 'test/scripts/create_table.php';
	}

	public function onFinished() {
		include APP . 'test/scripts/drop_table.php';
	}

	public function testApplicationObjectSave() {
		$test = new TestObject();

		$this->assertTrue( $test->insert() );
		$this->assertTrue( $test->getId() > 0 );
	}

	public function testApplicationObjectExists() {
		$test = new TestObject();

		$this->assertTrue( $test->insert() );
		$id = $test->getId();

		$t = new TestObject( $id );
		$this->assertTrue( $t->exist() );

		$t = new TestObject( $id + 1 );
		$this->assertTrue( ! $t->exist() );
	}

	public function testPartialUpdate() {
		$test = new TestObject();
		$test->setStr( 'panoye 1 2 3' );
		$test->setJkl( 'jkljkl' );
		$test->insert();
		$id = $test->getId();

		$test->setJkl( 'new value' );
		$test->update( array( 'jkl' ) );

		$new = new TestObject( $id );
		$this->assertTrue( $new->load() );

		d( $new->getStr() );
		d( $new->getJkl() );

		$this->assertTrue( $new->getStr() == 'panoye 1 2 3' );
		$this->assertTrue( $new->getJkl() == 'new value' );
	}

	public function testSefUrl() {
		$t = new TestObject();
		$n = rand( 1, 100000 );
		$t->setStr( 'test 1 2 3' . $n );
		$t->insert();
		$this->assertTrue( $t->getSefUrl() == 'test-1-2-3' . $n );
	}

	public function testEmptySefUrl() {
		$t = new TestObject();
		$t->setStr();
		$t->insert();
		d( $t->getSefUrl() );
		$this->assertTrue( strlen( $t->getSefUrl() ) > 0 );
	}

	public function testNumericSefUrl() {
		$t = new TestObject();
		$t->setStr( 'test 1 2 3' );
		$t->insert();
		d( $t->getSefUrl() );
		$this->assertTrue( ! is_numeric( $t->getSefUrl() ) );
	}

	public function testUpdateSefUrl() {
		$t = new TestObject();
		$t->setStr( 'test 1 2 3' );
		$t->insert();
		$oldSefUrl = $t->getSefUrl();
		$this->assertTrue( strlen( $oldSefUrl ) > 0 );
		d( $oldSefUrl );

		$t->setSefUrl( '' );
		$t->setStr( 'abc' );
		$t->update( array( 'sef_url' ) );
		$newSefUrl = $t->getSefUrl();
		d( $newSefUrl );

		$this->assertTrue( strlen( $newSefUrl ) > 0 );
		$this->assertTrue( preg_match( '/^abc.*$/', $newSefUrl ) );
	}

	public function testTimestampField() {
		$sql = new Sql( 'select * from panoye_app_test order by id desc' );
		$t = $sql->first( 'TestObject' );

		$this->assertTrue( is_object( $t->getCreated() ) );
		$this->assertTrue( get_class( $t->getCreated() ) == 'Timestamp' );
	}

	public function testDelete() {
		$t = new TestObject();
		$t->setStr( 'test 1 2 3' );
		$t->insert();

		$this->assertTrue( $t->exist() );

		$id = $t->getId();

		$t->delete();

		$t2 = new TestObject( $id );

		$this->assertTrue( ! $t2->exist() );
	}

}