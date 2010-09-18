<?php

class ErrorPage extends Page {

	public function __construct() {
		parent::__construct();
	}

	public function printMain() {
		echo '<h1>Error: Page not found!</h1>';
	}

}
