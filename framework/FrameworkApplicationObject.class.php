<?php

class FrameworkApplicationObject {

	public function __construct() {
	}

	public function saveError( $error ) {}

	public function onPage() {}

	public function onStart() {}

	public function onEnd() {}

	public function getPageAliases() {
		return array();
	}

}