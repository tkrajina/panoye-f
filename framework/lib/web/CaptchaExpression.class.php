<?

class CaptchaExpression {

	const SESSION_VAR = '~captcha_result';

	private $expression;
	private $result;

	private $operations = array(
		'+' => ' plus ',
		'-' => ' minus ',
		'x' => ' multiply by ',
		'*' => ' multiply by ',
		'&middot;' => ' multiply by ',
		'/' => ' divided by ',
		':' => ' divided by ',
	);

	public function __construct( $words = false ) {
		$_methods = get_class_methods( $this );
		$methods = array();
		foreach( $_methods as $m ) {
			if( preg_match( '/^expression\d+$/', $m ) ) {
				$methods[] = $m;
			}
		}
		$method = $methods[ rand( 0, sizeof( $methods ) - 1 ) ];
		$this->$method();
		if( $words ) {
			$this->expression = $this->toWords( $this->expression );
		}
		Session::set( self::SESSION_VAR, $this->result );
	}

	public static function isValid( $result ) {
		$r = Session::get( self::SESSION_VAR );
		return $r == $result;
	}

	public function getExpression() {
		return $this->expression;
	}

	private function toWords( $expression ) {
		reset( $this->operations );
		foreach( $this->operations as $k => $v ) {
			$expression = str_replace( $k, $v, $expression );
		}
		return $expression;
	}

	public function getResult() {
		return $this->result;
	}

	private function expression1() {
		$a = rand( 1, 10 );
		$b = rand( 1, 10 );
		$c = rand( 1, 10 );
		$this->expression = "$a+$b+$c=";
		$this->result = $a + $b + $c;
	}

	private function expression2() {
		$a = rand( 1, 5 );
		$b = rand( 1, 5 );
		$c = rand( 1, 10 );
		$this->expression = "{$a}x{$b}+{$c}=";
		$this->result = $a * $b + $c;
	}

	private function expression3() {
		$a = rand( 1, 10 );
		$b = rand( 1, 5 );
		$c = rand( 1, 5 );
		$this->expression = "{$a}+{$b}x{$c}=";
		$this->result = $a + $b * $c;
	}

	private function expression4() {
		$a = rand( 1, 3 );
		$b = rand( 1, 5 );
		$c = rand( 1, 3 );
		$this->expression = "{$a}x{$b}x{$c}=";
		$this->result = $a * $b * $c;
	}

	private function expression5() {
		$a = rand( 1, 5 );
		$b = rand( 1, 3 );
		$c = rand( 1, 3 );
		$this->expression = ( $a * $b ) . "/{$b}+{$c}=";
		$this->result = $a + $c;
	}

	private function expression6() {
		$a = rand( 10, 15 );
		$b = rand( 1, 3 );
		$c = rand( 1, 3 );
		$this->expression = "{$a}-{$b}-{$c}=";
		$this->result = $a - $b - $c;
	}

}

/*
$c = new CaptchaExpression();
echo $c->getExpression() . '...' . $c->getResult();
*/