<?php

class DbTest extends AppTest {

	public function onStart() {
		include APP . 'test/scripts/create_table.php';
	}

	public function onFinished() {
		include APP . 'test/scripts/drop_table.php';
	}

	public function testDbPrepareValues() {
		$this->assertTrue( is_int( Db::prepareInt( '123x' ) ) );
		$this->assertTrue( Db::prepareInt( '123' ) === 123 );
		$this->assertTrue( is_double( Db::prepareDecimal( '12.3x' ) ) );
		$this->assertTrue( Db::prepareDecimal( '12.3x' ) === 12.3 );

	}

	public function testSqlReplacements() {
		$sql = new Sql( 'select * from panoye_app_test where id=:id and f=:f :x :a' );
		$sql->setInt( 'id', '123.45w67' );
		$sql->setDecimal( 'f', '123.45w67' );
		$sql->setInt( 'x', 'abc')->setAlfanumeric( 'a', 'ZIU/()UIO' );

		$string = $sql->getSql();
		d( $string );

		$this->assertTrue( 'select * from panoye_app_test where id=123 and f=123.45 0 ZIUUIO' === $string );
	}

}