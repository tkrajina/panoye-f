<?

class Logs {

	const DEBUG = 1;
	const INFO = 2;
	const WARN = 3;
	const ERROR = 4;
	const FATAL = 5;

	private static $logs = array();

	private static $maxLevel = 1;

	private static $save = true;

	public static function debug( $log ) {
		self::$logs[] = '[debug] ' . $log;
	}

	public static function info( $log ) {
		if( self::$maxLevel < self::INFO) {
			self::$maxLevel = self::INFO;
		}
		self::$logs[] = '[info] ' . $log;
	}

	public static function warn( $log ) {
		if( self::$maxLevel < self::WARN) {
			self::$maxLevel = self::WARN;
		}
		self::$logs[] = '[warn] ' . $log;
	}

	public static function error( $log ) {
		if( self::$maxLevel < self::ERROR) {
			self::$maxLevel = self::ERROR;
		}
		self::$logs[] = '[error] ' . $log;
	}

	public static function fatal( $log ) {
		if( self::$maxLevel < self::FATAL) {
			self::$maxLevel = self::FATAL;
		}
		self::$logs[] = '[FATAL] ' . $log;
	}

	public static function getLogs() {
		return self::$logs;
	}

	public static function getLevel() {
		return self::$maxLevel;
	}

	public static function setSave( $save ) {
		self::$save = $save;
	}

	public static function isSave() {
		return self::$save;
	}

}
