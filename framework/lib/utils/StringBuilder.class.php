<?

class StringBuilder {

	private $string;

	public function __construct( $string = '' ) {
		$this->string = $string;
	}

	public function append( $string ) {
		$args = func_get_args();
		foreach( $args as $arg ) {
			$this->string .= '' . $arg;
		}
	}

	/** Variable number of args! */
	public function appendLine() {
		$args = func_get_args();
		foreach( $args as $arg ) {
			$this->string .= '' . $arg;
		}
		$this->string .= "\n";
	}

	public function newLine() {
		$this->string .= "\n";
	}

	public function toString() {
		return $this->string;
	}

	public function __toString() {
		return $this->string;
	}

}
