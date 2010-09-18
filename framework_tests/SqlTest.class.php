<?php

class SqlTest extends AppTest {

	public function onStart() {
		include APP . 'test/scripts/create_table.php';
		include APP . 'test/scripts/populate_table.php';
	}

	public function onFinished() {
		include APP . 'test/scripts/drop_table.php';
	}

	public function testSql() {
		$sql = new Sql( 'select * from panoye_app_test' );
		$i = $sql->select();
		p( get_class( $i ) );

		$this->assertTrue( get_class( $i ) == 'DbIterator' );
		$this->assertTrue( $i->size() == 200 );
	}

	public function testLoadFirstObject() {
		$sql = new Sql( 'select * from panoye_app_test' );
		$o = $sql->first( 'TestObject' );

		$this->assertTrue( is_object( $o ) );
		$this->assertTrue( get_class( $o ) == 'TestObject' );
		$this->assertTrue( get_class( $o->getCreated() ) == 'Timestamp' );
	}

	public function testLoadSize() {
		$sql = new Sql( 'select count(*) count from panoye_app_test' );
		$size = $sql->first();

		$this->assertTrue( is_object( $size ) );
		$this->assertTrue( $size->getCount() == 200 );
	}

}