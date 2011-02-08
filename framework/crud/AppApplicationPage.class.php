<?php

if( ! Application::DEBUG || $_SERVER[ 'SERVER_ADDR' ] != '127.0.0.1' || ! is_dir( 'app_tools' ) ) {
	die( 'DEBUG must be on, requests must be from localhost and the app_tools should be here...' );
}

function doTemplate( $name, $replacements = array() ) {
	$contents = file_get_contents( 'app_tools/templates/' . $name . '.template' );
	foreach( $replacements as $key => $value ) {
		$contents = str_replace( '{{' . $key . '}}', $value, $contents );
	}
	return $contents;
}

class AppApplicationPage extends FormPage {

	private $submit;

	private $fields = array();

	public function __construct() {
		parent::__construct();
	}

	public function execute() {
		if( ! is_dir( 'app_tools' ) ) {
			return $this->redirect( url( 'error' ) );
		}

		$this->setObject( new Object() );

		$table = $this->getParam( 'arg' );
		if( $table ) {
			$this->getObject()->setTable( $table );
			$this->getObject()->setClass( ucfirst( DbNames::dbToFramework( $table ) ) );
			$this->getObject()->setDirectory( strtolower( DbNames::dbToFramework( $table ) ) );
		}
	}

	protected function validate() {
		$this->addErrorIf( strlen( $this->getObject()->getTable() ) == 0, 'Table?' );
		$this->addErrorIf( strlen( $this->getObject()->getClass() ) == 0, 'Class name?' );
		$this->addErrorIf( strlen( $this->getObject()->getDirectory() ) == 0, 'Directory?' );
	}

	public function submit() {
		$this->submit = true;
	}

	public function printTitle() {
		echo 'Create CRUD for table';
	}

	public function printMain() {
		if( $this->submit ) {
			$table = $this->getObject()->getTable();
			$sefFrom = $this->getObject()->getSefFrom();
			$toString = $this->getObject()->getToString();
			$className = $this->getObject()->getClass();
			$directory = $this->getObject()->getDirectory();
			$this->createObjects( $table, $className, $directory, $sefFrom, $toString );

			echo 'Done! Now clear the contents of cache/ directory and ' . htmlLink( 'go to index', str_replace( '_', '', $table ) . '-index' );
			return;
		}
		$table = $this->getParam( 'arg' );
		if( ! $table ) {
			$sql = 'show tables';
			$tables = new DbIterator( $sql );
			while( $table = $tables->next() ) {
				$method = 'tablesIn' . ucfirst( DbNames::dbToFramework( Application::DB_DATABASE ) );
				$table = $table->get( $method );
				echo htmlLink( $table, 'application', $table ) . ' &nbsp; ';
			}
		}
		else {
			echo htmlLink( 'Enter table manually', 'application' );
		}
		print BR . BR;

		$sefUrlParams = array();
		if( $table ) {
			$tables = new DbIterator( 'show columns from ' . $table . ' where field not in (\'id\', \'sef_url\', \'created\', \'updated\')' );
			while( $t = $tables->next() ) {
				$sefUrlParams[ $t->getField() ] = $t->getField();
			}
			$sefUrlParams[ '' ] = '[No sef url]';
		}

		$toStringParams = array();
		if( $table ) {
			$tables = new DbIterator( 'show columns from ' . $table . ' where field not in (\'id\', \'sef_url\')' );
			while( $t = $tables->next() ) {
				$toStringParams[ DbNames::dbToFramework( $t->getField() ) ] = DbNames::dbToFramework( $t->getField() );
			}
		}

?>
<? $this->formErrors(); ?>
<? $this->formStart(); ?>

Table name:<br/>
<? if( $table ) {
	print $table;
	print BR . BR;
}
else {
	$this->formText( 'table' );
	print BR . BR;
}
?>

Create SEF from column:<br/>
<? if( $table ) {
	$this->formSelect( 'sefFrom', $sefUrlParams );
	print BR . BR;
}
else {
	$this->formText( 'sefFrom' );
	print BR . BR;
}
?>

To String from:<br/>
<? if( $table ) {
	$this->formSelect( 'toString', $toStringParams );
	print BR . BR;
}
else {
	$this->formText( 'toString' );
	print BR . BR;
}
?>

Page directory name:<br/>
<? $this->formText( 'directory' ) ?><br/><br/>

Class name:<br/>
<? $this->formText( 'class' ) ?><br/><br/>

<? $this->formSubmit( 'Create!' ); ?><br/>
<? $this->formEnd(); ?>
<?php
	}

	///////////////////////////////////////////////////////////////////////////

	private function createObjects( $tableName, $className, $directory, $sefFrom = null, $toString = null ) {
		try {
			$columns = new DbIterator( 'desc ' . $tableName );
		}
		catch( Exception $e ) {
			echo 'Table not found';
			return;
		}
		$data = array();
		$data[ 'class_name' ] = $className;
		$data[ 'table_name' ] = $tableName;
		$data[ 'directory_name' ] = strtolower( $directory );
		$columnsDefinitions = '';
		$showCode = '';
		$methods = '';
		while( $column = $columns->next() ) {
			$field = $column->getField();
			$type = strtolower( preg_replace( '/[^a-z]/', '', $column->getType() ) );
			if( $field == 'id' || $field == 'created' || $field == 'updated' ) {
				continue;
			}
			$name = DbNames::dbToFramework( $field );
			if( $type == 'varchar' || $type == 'text' ) {
				$columnsDefinitions .= '			\'' . $field . '\' => self::STRING,' . "\n";
				$this->fields[] = $name;
				$methods .= '	public function validate' . ucfirst( $name ) . '() {
		if( preg_match( \'/^.+$/\', \'\' . $this->get' . ucfirst( $name ) . '() ) == 0 ) {
			return \'' . $name . ' not valid\';
		}
	}

';
			}
			else if( $type == 'int' || $type == 'bigint' || $type == 'tinyint' ) {
				$columnsDefinitions .= '			\'' . $field . '\' => self::INTEGER,' . "\n";
				$this->fields[] = $name;
			}
			else if( $type == 'timestamp' ) {
				$columnsDefinitions .= '			\'' . $field . '\' => self::TIMESTAMP,' . "\n";
				$this->fields[] = $name;
			}
			else if( $type == 'date' ) {
				$columnsDefinitions .= '			\'' . $field . '\' => self::DATE,' . "\n";
				$this->fields[] = $name;
			}
			else if( $type == 'double' || $type == 'float' ) {
				$columnsDefinitions .= '			\'' . $field . '\' => self::DECIMAL,' . "\n";
				$this->fields[] = $name;
			}
			else {
				echo 'Nepoznati tip:' . $type . BR;
				continue;
			}
			$showCode .= $name . ':<br/>' . "\n" . ' &middot; <?= $this->getObject()->get' . ucfirst( $name ) . '(); ?><br/><br/>' . "\n\n";
		}
		$data[ 'columns' ] = $columnsDefinitions;
		$data[ 'methods' ] = '';
		$data[ 'show_object' ] = $showCode;

		if( $sefFrom ) {
			$methods .= '	public function getUrlFrom() { return \'' . $sefFrom . '\'; }' . "\n\n";
		}

		if( $toString ) {
			$methods .= '	public function __toString() { return \'\' . $this->get( \'' . $toString . '\' ); }' . "\n\n";
		}

		$data[ 'methods' ] = $methods;

		$this->writeIfNotExists( 'app/objects/' . $className . '.class.php', doTemplate( 'Object.class', $data ) );

		$this->createDirectory( $directory );

		$this->createIndex( $data );
		$this->createShow( $data );
		$this->createDelete( $data );
		$this->createEdit( $data );
	}

	private function createDirectory( $dir ) {
		$parts = explode( '/', $dir );
		$startWith = 'app/pages';
		foreach( $parts as $part ) {
			$startWith .= '/' . $part;
			@mkdir( $startWith );
		}
	}

	private function createIndex( $data ) {
		$dirName = $data[ 'directory_name' ];
		$this->writeIfNotExists( 'app/pages/' . $dirName . '/IndexPage.class.php', doTemplate( 'IndexPage.class', $data ) );
	}

	private function createShow( $data ) {
		$dirName = $data[ 'directory_name' ];



		$this->writeIfNotExists( 'app/pages/' . $dirName . '/ShowPage.class.php', doTemplate( 'ShowPage.class', $data ) );
	}

	private function createDelete( $data ) {
		$dirName = $data[ 'directory_name' ];
		$this->writeIfNotExists( 'app/pages/' . $dirName . '/DeletePage.class.php', doTemplate( 'DeletePage.class', $data ) );
	}

	private function createEdit( $data ) {
		$dirName = $data[ 'directory_name' ];

		$editFields = '...';
		reset( $this->fields );
		foreach( $this->fields as $field ) {
			if( $field == 'sefUrl' ) {
				continue;
			}
			$editFields .= $field . ':<br/>' . "\n";
			$editFields .= '<? $this->formText( \'' . $field . '\' ) ?><br/><br/>' . "\n\n";
		}

		$data[ 'edit_fields' ] = $editFields;

		$this->writeIfNotExists( 'app/pages/' . $dirName . '/EditPage.class.php', doTemplate( 'EditPage.class', $data ) );
	}

	private function writeIfNotExists( $file, $contents ) {
		if( ! is_file( $file ) ) {
			file_put_contents( $file, $contents );
			echo $file . ' <b>created</b>!' . BR;
		}
		else {
			echo $file . ' <b>already exists</b>!' . BR;
		}
	}

}