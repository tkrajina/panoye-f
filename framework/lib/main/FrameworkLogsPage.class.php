<?php

class FrameworkLogsPage extends Page {

	private $iterator;

	function __construct() {
		parent::__construct();
	}

	public function execute() {
		$this->setTemplate( 'empty' );

		Logs::setSave( false );

		$level = $this->getIntParam( 'level' );
		if( $level ) {
			$sql = new Sql( 'select * from log where level >= :level order by id desc' );
			$sql->setInt( 'level', $level );
		}
		else {
			$sql = new Sql( 'select * from log order by id desc' );
		}

		$sessionId = $this->getParam( 'session' );
		if( $sessionId ) {
			$sql = new Sql( 'select * from log where session_id = :session order by id desc' );
			$sql->setString( 'session', $sessionId );
		}

		$delete = $this->getIntParam( 'delete' );
		if( $delete ) {
			$deleteSql = new Sql( 'delete from log where created < adddate( now(), - :delete )' );
			$deleteSql->setInt( 'delete', $delete );
			$deleteSql->execute();
		}

		$this->iterator = $sql->select();
		$this->iterator->paginate();
	}

	public function printTitle() {
		echo 'Logs';
	}

	public function printMain() {
		echo htmlLink( 'Debug', '/logs', null, array( 'level' => 1 ) );
		echo ' &nbsp; ';
		echo htmlLink( 'Info', '/logs', null, array( 'level' => 2 ) );
		echo ' &nbsp; ';
		echo htmlLink( 'Warn', '/logs', null, array( 'level' => 3 ) );
		echo ' &nbsp; ';
		echo htmlLink( 'Error', '/logs', null, array( 'level' => 4 ) );
		echo ' &nbsp; ';
		echo htmlLink( 'Fatal', '/logs', null, array( 'level' => 5 ) );
		echo ' &nbsp; ';
		echo htmlLink( 'Delete older than 24h?', '/logs', null, array( 'delete' => 1 ) );
		echo BR;
		$this->iterator->printPageIndex( 'More logs:' );
		echo '<ul>';
		while( $log = $this->iterator->next() ) {
			echo '<li>';
			echo 'Time: ', $log->getCreated(), ' ';
			echo htmlLink( '[only this session]', '/logs', null, array( 'session' => $log->getSessionId() ) );
			echo BR;
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
