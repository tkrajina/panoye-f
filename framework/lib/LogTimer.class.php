<?

/**
 * Utility clas for debugging.
 */
class LogTimer {

	static $timer = array();

	public static function reset() {
		self::$timer = array();
	}

	public static function time( $title ) {
		self::$timer[] = array( microtime( true ), $title );
	}

	public static function logTimes() {
		$result = '';
		$startTime = null;
		$previousTime = null;
		foreach( self::$timer as $timerItem ) {
			$time = $timerItem[ 0 ];
			$title = $timerItem[ 1 ];

			if( ! $startTime ) {
				$startTime = $time;
				$previousTime = $time;
			}

			Logs::debug( ( $time - $previousTime ), 's ', ( $time - $startTime ), 's - ', $title );

			$previousTime = $time;
		}

	}

}
