<?php

if( ! Application::DEBUG || $_SERVER[ 'SERVER_ADDR' ] != '127.0.0.1' || ! is_dir( 'app_tools' ) ) {
	die( 'DEBUG must be on, requests must be from localhost and the app_tools should be here...' );
}

require_once FRAMEWORK . 'test/AppTest.class.php';
require_once FRAMEWORK . 'test/TestException.class.php';

class AppTestPage extends Page {

	private $testClasses = array();

	public function __construct() {
		parent::__construct();
	}

	public function execute() {
		$files = Files::getFiles( APP . 'test' );
		if( ! is_array( $files ) ) {
			$files = array();
			return;
		}
		foreach( $files as $file ) {
			if( preg_match( '/^[\w\d]+Test\.class\.php$/', $file ) ) {
				$this->testClasses[] = $file;
			}
		}
	}

	public function printTitle() {
		echo 'Unit testing...';
	}

	public function printMain() {
		$tests = 0;
		$errors = 0;
		if( sizeof( $this->testClasses ) == 0 ) {
			echo 'No tests for the moment ;(';
			return;
		}
		foreach( $this->testClasses as $testClass ) {
			$file = APP . 'test/' . $testClass;
			$class = str_replace( '.class.php', '', $testClass );
			require $file;
			$test = new $class();
			$test->execute();

			$tests += $test->getTestsNo();
			$errors += $test->getErrorsNo();
		}

		echo '<h1>errors/successes = <span style="color:red">' . $errors . '</span>/<span style="color:green">' . $tests . '</span></h1>';
	}

}