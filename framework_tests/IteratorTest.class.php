<?php

class IteratorTest extends AppTest {

	public function onStart() {
		include APP . 'test/scripts/create_table.php';
		include APP . 'test/scripts/populate_table.php';
	}

	public function onFinished() {
		include APP . 'test/scripts/drop_table.php';
	}

	public function testObjectsLoad() {
		$i = new DbIterator( 'select * from panoye_app_test', 'TestObject' );

		$this->assertTrue( $i->size() == 200 );

		while( $o = $i->next() ) {
			$this->assertTrue( is_object( $o ) );

			p( $o->getCreated() );
			p( get_class( $o->getCreated() ) );
			$this->assertTrue( get_class( $o->getCreated() ) == 'Timestamp' );
		}
	}

	public function testIndex() {
		$i = new DbIterator( 'select * from panoye_app_test', 'TestObject' );
		$i->paginate();

		ob_start();
		$i->printPageIndex( 'Ostalo:' );
		$index = ob_get_contents();
		ob_clean();

		d( htmlspecialchars( $index ) );

		$this->assertTrue( strpos( $index, 'test?no=1' ) > 0 );
		$this->assertTrue( strpos( $index, 'Ostalo:' ) === 0 );
	}

	public function testLoadObjects() {
	}

}