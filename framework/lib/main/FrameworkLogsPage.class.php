<?php

class FrameworkLogsPage extends Page {

	private $iterator;

	function __construct() {
		parent::__construct();
	}

	public function execute() {
		$this->setTemplate( 'empty' );

		Logs::setSave( false );

		$sql = new Sql( 'select * from log order by id desc' );
		$this->iterator = $sql->select();
		$this->iterator->paginate();
	}

	public function printTitle() {
		echo 'Logs';
	}

	public function printMain() {
		echo '<ul>';
		while( $log = $this->iterator->next() ) {
			echo '<li>';
			echo $log->getCreated() . ':<br>';
			$log = nl2br( $log->getLog() );
			$log = str_replace( '[debug]', '<span style="color:gray">[debug]</span>', $log );
			$log = str_replace( '[info]', '<span style="color:blue">[info]</span>', $log );
			$log = str_replace( '[warn]', '<span style="color:yellow">[warn]</span>', $log );
			$log = str_replace( '[error]', '<span style="color:orange">[error]</span>', $log );
			$log = str_replace( '[FATAL]', '<span style="color:red">[FATAL]</span>', $log );
			echo $log;
			echo '</li>';
		}
		echo '</ul>';
		$this->iterator->printPageIndex( 'More logs:' );
	}

}
