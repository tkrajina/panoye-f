<?php

class AppTest {

	private $testMethods = array();

	private $currentMethod;

	private $tests = 0;
	private $errors = 0;

	function __construct() {
	}

	function execute( $method = null ) {
		$this->readTestMethods();
		$this->onStart();
		foreach( $this->testMethods as $testMethod ) {
			$this->currentMethod = $testMethod;
			if( $method == null || $method == $testMethod ) {
				++ $this->tests;
				try {
					ob_start();
					$this->beforeTest();
					$this->$testMethod();
					$this->afterTest();
					ob_clean();
					echo 'OK: ' . get_class( $this ) . '->' . $testMethod . '()<br/>';
				}
				catch( TestException $e ) {
					++ $this->errors;
					$contents = ob_get_contents();
					ob_clean();
					echo '<span style="color:red;font-weight:bold;">ERROR:</span>: ' . get_class( $this ) . '->' . $testMethod . '() <br/>';
					echo '<div style="border:1px solid black;font-size:0.8em;">';
					echo '<pre>' . $e . '</pre>';
					echo '<div style="border:1px solid black;border-width:1px 0px 0px 0px;">' . $contents . '</div>';
					echo '</div>';
				}
			}
		}
		$this->onFinished();
	}

	private function readTestMethods() {
		$methods = get_class_methods( $this );
		foreach( $methods as $method ) {
			if( preg_match( '/^test[\d\w]+$/', $method ) ) {
				$this->testMethods[] = $method;
			}
		}
	}

	protected function assertTrue( $expression ) {
		if( ! $expression ) {
			throw new TestException( 'Assertion failed in ' . get_class( $this ) . '->' . $this->currentMethod );
		}
	}

	public function getTestsNo() {
		return $this->tests;
	}

	public function getErrorsNo() {
		return $this->errors;
	}

	/** Method called before each test in this class. */
	public function beforeTest() {}

	/** Method called after each test in this class. */
	public function afterTest() {}

	/** Method called once before all the tests. */
	public function onStart() {}

	/** Method called once after all the tests. */
	public function onFinished() {}

}