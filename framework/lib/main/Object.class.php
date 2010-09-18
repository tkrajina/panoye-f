<?

/**
 * Klasa koja se koristi za modele i bilo koje druge objekte u projektu. U njoj
 * se property-iji cuvaju u {@link #$properties} hashMapi, a <u>ne kao klasicni
 * property-iji (private)</u> !!!
 *
 * Imena property-ija u hashmapama morabiti oblika PropertyName (npr.
 * NazivObjekta).
 */
class Object {

	private $initial;

	private $properties = array();

	public function __construct( $arg = null ) {
		if( is_array( $this->initial ) ) {
			$this->setProperties( $this->initial );
		}

		if( is_array( $arg ) || is_object( $arg ) ) {
			$this->setProperties( $arg );
		}
		else if( (int) $arg ) {
			$this->setId( $arg );
		}
		else if( ! is_null( $arg ) ) {
			error( 'Tip??? -> ' . $arg );
		}
	}

	function __call( $method, $arguments ) {
		$type = @substr( $method, 0, 3 );
		$type2 = @substr( $method, 0, 2 );
		$property = @substr( $method, 3, strlen( $method ) );
		if( $type == 'get' ) {
			return $this->get( $property );
		}
		else if( $type2 == 'is' ) {
			return $this->get( @substr( $method, 2, strlen( $method ) ) );
		}
		else if( $type == 'set' ) {
			$this->set( $property, @$arguments[ 0 ] );
		}
		else {
			error( 'Method not found:' . $method );
			throw new AppException( 'Method not found:' . $method ); // TODO: Greska
		}
	}

	/** kao set */
	public function get( $propertyName ) {
		if( ucfirst( $propertyName ) == $propertyName ) {
			$propertyName = strtolower( substr( $propertyName, 0, 1 ) ) . substr( $propertyName, 1 );
		}
		return @$this->properties[ $propertyName ];
	}

	/** Postavljanje property-ija, oni se uvijek čuvaju s mali početnim slovom. */
	public function set( $propertyName, $propertyValue ) {
		if( ucfirst( $propertyName ) == $propertyName ) { // Malo početno slovo
			$propertyName = strtolower( substr( $propertyName, 0, 1 ) ) . substr( $propertyName, 1 );
		}
		$this->properties[ $propertyName ] = $propertyValue;
	}

	public function getProperties() {
		return $this->properties;
	}

	public function setProperties( $arg ) {
		if( ! $arg ) {
			$this->properties = array();
		}
		else if( is_object( $arg ) ) {
			$this->properties = $arg->getProperties();
		}
		else {
			$this->setProperties( null );
			reset( $arg );
			foreach( $arg as $key => $value ) {
				// Treba petlja zbog imenovanja (da ne bi došao property s dugim imenom)
				$this->set( $key, $value );
			}
		}
	}

	public function addProperties( $propertiesOrObject ) {
		if( ! $propertiesOrObject ) {
			$propertiesOrObject = array();
		}
		if( is_object( $propertiesOrObject ) ) {
			$this->properties = array_merge( $this->properties, $propertiesOrObject->getProperties() );
		}
		else {
			reset( $propertiesOrObject );
			foreach( $propertiesOrObject as $key => $value ) {
				// Treba petlja zbog imenovanja (da ne bi došao property s dugim imenom)
				$this->set( $key, $value );
			}
		}
	}

	public function copyProperty( $object, $property ) {
		if( ! is_object( $object ) ) {
			error( 'Object nije objekt!' );
			return;
		}
		$this->set( $property, $object->get( $property ) );
	}

}
