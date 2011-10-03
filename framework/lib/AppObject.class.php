<?php

class AppObject extends Object {

	//////////////////////////////////////////////
	// Koristi se za definiranje polja tablice: //
	//////////////////////////////////////////////

	const INTEGER 			= 2;
	const STRING 			= 4;
	const DECIMAL 			= 6;
	const TIMESTAMP			= 8;
	const DATE				= 10;

	//////////////////////////////////////////////
	//////////////////////////////////////////////

	private $tableColumns;
	private $sqlProperties;

	public function __construct( $arg = null ) {
		parent::__construct( $arg );

		// Učitamo sada, tako da ne moramo kasnije
		if( ! $this->tableColumns ) { // da se osiguramo da ne poziva bespotrebno vise puta:
			$this->tableColumns = $this->getTableColumns();
			$this->tableColumns[ 'created' ] = self::TIMESTAMP;
			$this->tableColumns[ 'updated' ] = self::TIMESTAMP;
		}
	}

	public function getTableName() {
		return 'null';
	}

	public function getTableColumns() {
		return array();
	}

	public function getUrlFrom() {
		return null;
	}

	/**
	 * Svaka stranica određuje objekt s kojim radi i action (npr show, insert, update),
	 * a objekt kasnije određuje da li taj korisnik smije raditi akciju s njime. Svaki
	 * objekt bi trebao imati i ovu metodu.
	 */
	public function isAuthorized( $user, $action ) {
		return true;
	}

	/** Za slucaj kad su propertyiji zadani u sql formatu. */
	public function setSqlProperties( $properties ) {
		$this->setProperties( $this->prepareSqlProperties( $properties ) );
	}

	/**
	 * @see #setSqlProperties()
	 * @see #addProperties()
	 */
	public function addSqlProperties( $properties ) {
		$this->addProperties( $this->prepareSqlProperties( $properties ) );
	}

	/**
	 * Priprema property-ije iz SQL-a za objekt.
	 */
	private function prepareSqlProperties( $properties ) {
		// Ovdje treba pripaziti na timestamp-ove, jer ih treba zamijeniti s objektima:
		$columns = $this->tableColumns;

		if( is_array( $properties ) ) {
			foreach( $properties as $key => $value ) {
				$type = @$columns[ $key ];
				$value = @$properties[ $key ];
				if( $type == self::TIMESTAMP ) {
					$properties[ $key ] = new Timestamp( $value );
				}
				if( $type == self::DATE ) {
					$properties[ $key ] = new Date( $value );
				}
			}
		}

		return DbNames::dbToFramework( $properties );
	}

	/** Za slucaj kad su propertyiji zadan u sql formatu. */
	public function getSqlProperties() {
		return DbNames::frameworkToDb( $this->getProperties() );
	}

	public function load() {
		$this->beforeLoaded();
		if( ! $this->getId() ) {
			return false;
		}
		$sql = 'select * from ' . $this->getTableName()
			. ' where id=' . (int) $this->getId();
		try {
			$properties = Db::loadArray( $sql );
			if( ! $properties ) {
				return false;
			}
		}
		catch( Exception $e ) {
			if( Application::DEBUG ) {
				echo '<pre>' . $e . '</pre>';
			}
			return false;
		}
		$this->setSqlProperties( $properties );
		$this->afterLoaded();
		return true;
	}

	/** Provjerava da li objekt vec postoji u bazi. */
	public function exist() {
		if( ! $this->getId() ) {
			return false;
		}
		$sql = new Sql( 'select count(*) count from ' . $this->getTableName() . ' where id=:id' );
		$result = $sql->setInt( 'id', $this->getId() )->first();
		return is_object( $result ) && $result->getCount() > 0;
	}

	public function save() {
		try {
			if( $this->exist() ) {
				return $this->update();
			}
			else {
				return $this->insert();
			}
		}
		catch( Exception $e ) {
			if( Application::DEBUG ) {
				echo '<pre>' . $e . '</pre>';
			}
			return false;
		}
	}

	public function update( $updateColumns = null, $changeUpdated = true ) {
		$this->beforeUpdated();
		$sql = 'update ' . $this->getTableName()
			. ' set ' . $this->sqlSetData( $updateColumns );
		if( $changeUpdated ) {
			$sql .= ', updated = now()';
		}
		$sql .= ' where id=' . (int) $this->getId();
		$result = Db::executeUpdate( $sql );
		if( $result ) {
			$this->afterUpdated();
		}
		return $result;
	}

	public function insert() {
		$this->beforeInserted();
		$sql = new Sql( 'insert into :table set :set, created=now()' );
		$sql->setAlfanumeric( 'table', $this->getTableName() )->set( 'set', $this->sqlSetData( null, true ) );
		if( $sql->insert() ) {
			$this->setId( $sql->generatedKey() );
			$this->afterInserted();
			return true;
		}
		return false;
	}

	private function sqlSetData( $updateColumns = null, $createSefUrl = false ) {
		$result = array();

		$columns =& $this->tableColumns;
		@ $properties =& $this->getSqlProperties();

		reset( $columns );
		foreach( $columns as $key => $type ) {
			if( is_array( $updateColumns ) && ! in_array( $key, $updateColumns ) ) {
				continue;
			}
			if( $key == 'created' || $key == 'updated' ) {
				// ne mogu se mijenjati eksplicitno!
				continue;
			}
			if( $key == 'sef_url' ) {
				Logs::debug( 'Tu!' );
				// Treba li postavljati pocetni SEO url?
				// Samo ako je zadano iz cega ga treba kreirati i ako nije vec otprije kreiran:
				if( strlen( $this->getUrlFrom() ) > 0 && ( ! $this->getSefUrl() || $createSefUrl || ( is_array( $updateColumns ) && @in_array( 'sef_url', $updateColumns ) ) ) ) {
					$url = Strings::seoFriendly( $properties[ $this->getUrlFrom() ] );
					$sefUrl = $this->findSefUrl( $url );
					$this->setSefUrl( $sefUrl );
					$row = 'sef_url=' . Db::prepareString( $sefUrl );
					$result[] = $row;
				}
			}
			else {
				$row = '`' . $key . '`' . '=';
				$value = @$properties[ $key ];
				if( $type == self::INTEGER ) {
					$row .= Db::prepareInt( $value );
				}
				else if( $type == self::STRING ) {
					$row .= Db::prepareString( $value );
				}
				else if( $type == self::DECIMAL ) {
					$row .= Db::prepareDecimal( $value );
				}
				else if( $type == self::TIMESTAMP ) {
					if( ! isset( $properties[ $key ] ) ) {
						$row .= 'null';
					}
					if( strtolower( get_class( $value ) ) == 'timestamp' ) {
						$row .= "'" . $value->toSqlTime() . "'";
					}
				}
				else if( $type == self::DATE ) {
					if( ! isset( $properties[ $key ] ) ) {
						$row .= 'null';
					}
					$class = strtolower( get_class( $value ) );
					if( $class == 'date' || $class == 'date' ) {
						$row .= "'" . $value->toSqlDate() . "'";
					}
				}
				else {
					die( 'Greska' );
				}
				$result[] = $row;
			}
		}
		return ' ' . implode( ', ', $result ) . ' ';
	}

	private function findSefUrl( $string ) {
		$sql = new Sql( 'select count(*) count from :table where sef_url=:url' );
		$sql->setAlfanumeric( 'table', $this->getTableName() )->setString( 'url', $string );
		$result = $sql->first();
		$n = $result ? $result->getCount() : 0;
		if( $n > 0 ) {
			// Dakle, taj sef_url već postoji, treba kreirati novog:
			$parts = explode( '-', $string );
			$size = (int) @sizeof( $parts );
			$last = @$parts[ $size - 1 ];
			if( is_numeric( $last ) ) {
				$parts[ $size - 1 ] = ( (int) $last ) + 1;
				return $this->findSefUrl( implode( '-', $parts ) );
			}
			else {
				return $this->findSefUrl( $string . '-2' );
			}
		}
		return $string;
	}

	public function delete() {
		$this->beforeDeleted();
		if( ! $this->getId() ) {
			return false;
		}
		$sql = 'delete from ' . $this->getTableName()
			. ' where id=' . (int) $this->getId();
		$result = Db::executeUpdate( $sql );
		if( $result ) {
			$this->afterDeleted();
		}
		return $result;
	}

	protected function beforeInserted() {}

	protected function beforeUpdated() {}

	protected function beforeDeleted() {}

	protected function beforeLoaded() {}

	protected function afterInserted() {}

	protected function afterUpdated() {}

	protected function afterDeleted() {}

	protected function afterLoaded() {}

}
