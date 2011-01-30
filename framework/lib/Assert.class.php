<?

class Assert {

	private function __construct() {}

	public static function assertTrue( $expression, $message ) {
		if( ! $expression ) {
			Logs::fatal( $message );
			throw new AppException( $message );
		}
	}

	public static function assertEquals( $var1, $var2, $message ) {
		if( $var1 != $var2 ) {
			Logs::fatal( $message );
			throw new AppException( $message );
		}
	}

	public static function assertNotNull( $var, $message ) {
		if( $var == null ) {
			Logs::fatal( $message );
			throw new AppException( $message );
		}
	}

	public static function assertNull( $var, $message ) {
		if( $var != null ) {
			Logs::fatal( $message );
			throw new AppException( $message );
		}
	}

}
