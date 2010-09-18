<?php

require_once APP . 'test/objects/TestObject.class.php';

for( $i = 0; $i < 200; $i ++ ) {
	$test = new TestObject();
	$test->setStr( 's:' . rand( 1, 1000 ) );
	$test->setJkl( 'jkl:' . rand( 1, 1000 ) );
	$test->settitle( 'title...:' . rand( 1, 1000 ) );
	$test->insert();
}
