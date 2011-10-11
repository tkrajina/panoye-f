<?

class Orm {

	const NOT_NULL = 1;

	const INTEGER 			= 10;
	const STRING 			= 11;
	const DECIMAL 			= 12;
	const TIMESTAMP			= 13;
	const DATE				= 14;

	const SEF_URL_GENERATOR = 20;

	private static $classesTables = array();

	private static $classesColumnsMetadata = array();

	public static function registerClass( $className, $tableName, $columns ) {
		self::$classesTables[ $className ] = $tableName;
		self::$classesColumnsMetadata[ $className ] = $columns;
	}

	public static function exists( $object ) {
		// TODO
	}

	public static function save( $object ) {
		if( $object->id ) {
			return self::update( $object );
		} else {
			return self::insert( $object );
		}
	}

	public static function insert( $object ) {
		$className = get_class( $object );
		$tableName = self::$classesTables[ $className ];
		$columnsMetadata = self::$classesColumnsMetadata[ $className ];

		$sqlString = 'insert into ' . $tableName . ' ';

		foreach( $columnsMetadata as $columnName => $metadata ) {
			$sqlString .= 'columnName = :columnName, ';
		}

		$sqlString .= 'updated = now() ';

		$sql = new Sql( $sqlString );

		// Ovo u posebnu metodu:
		$objectAttributes = get_object_vars( $object );
		foreach( $columnsMetadata as $columnName => $metadata ) {
			$attributeName = self::toObjectName( $columnName );
			$attributeValue = @$objectAttributes[ $attributeName ];
			if( $attributeValue === null ) {
				if( in_array( self::NOT_NULL, $metadata ) ) {
					Logs::error( 'Null column: ', $attributeName, ' (defined as NOT NULL!)' );
					return false;
				}
				if( in_array( self::INTEGER ) ) {
					$sql->setInt( $columnName, $attributeValue );
				} else if( in_array( self::STRING ) ) {
					$sql->setString( $columnName, $attributeValue );
				} else if( in_array( self::DECIMAL ) ) {
					$sql->setDecimal( $columnName, $attributeValue );
				} else if( in_array( self::TIMESTAMP ) ) {
					// TODO
				} else if( in_array( self::DATE ) ) {
					// TODO
				}
			}

			// TODO sef_url
		}

		return $sql->execute();
	}

	public static function update( $object ) {
		// TODO
	}

	public static function load( $object ) {
		// TODO
	}

	public static function query( $className, $whereQuery ) {
		// TODO
	}

	private static function toDbName( $name ) {
		// TODO
	}

	private static function toObjectName( $name ) {
		// TODO
	}

}

$columns = array( 
	'title' => array( Orm::NOT_NULL, Orm::STRING, Orm::SEF_URL_GENERATOR ),
	'body' => array( Orm::NOT_NULL, Orm::STRING ),
	'send_time' => array( Orm::NOT_NULL, Orm::TIMESTAMP ),
);

Orm::registerClass( 'Newsletter', 'newsletter', $columns );
