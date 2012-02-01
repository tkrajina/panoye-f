<?

class Page {

	// ako se mijenja onda promijeniti i u main.php
	const CACHE = 'pages_cache';
	const CACHE_EXPRESSIONS = 'pages_expressions';

	/**
	 * Kod redirectanja stranica moze sljedecoj stranici poslati neki objekt u sessionu, on se cuva
	 * pod ovim kljucem.
	 */
	const REDIRECT_PARAMS_SESSION = 'sdajlkcxuiouioreioreuirewo';

	/** Staza do stranice. */
	public $path = null;

	public $pageClassFile;

	public $getString = null;
	public $getParameters = null;
	public $postParameters = null;
	private $redirectParameters = array();

	private $template = 'default';
	private $redirect = null;

	private $httpCode = -1;
	private $httpCodeMessage;

	private $contentType = 'text/html';

	// Ako se ne postavlja onda se računa kao da je drugi dio od contentType
	private $extension = 'html';

	/** Koja će se print*() metoda izvršiti po defaultu. */
	private $mainPrintMethod = 'Main';

	/** Objekt koji ce biti snimljen u formi. */
	protected $object = null;

	/* name => content */
	protected $htmlHead = array();

	protected $needCache = null;

	public function __construct() {
		$this->redirectParameters = unserialize( Session::get( self::REDIRECT_PARAMS_SESSION ) );

		// Odmah brišemo te iste podatke:
		Session::set( self::REDIRECT_PARAMS_SESSION, null );
	}

	/**
	 * Poziva se odmah nakon konstruktora neovisno o tome je li cashe-irano ili ne.
	 */
	public function init() {}

	/**
	 * Ovo je metoda koja se poziva od strane frameworka. Korisnik koristi samo
	 * {@link #execute()} i {@link #submit()}.
	 */
	public function executePage() {
		$this->beforeExecute();
		$this->execute();
		$this->afterExecute();
	}

	public function execute() {
	}

	protected function beforeExecute() {}

	protected function afterExecute() {}

	/** Alias za stranicu, treba overloadati ovu metodu i vratiti string koji ce biti alias. */
	public function getAlias() {
		return null;
	}

	/**
	 * Glavna echo funkcija. Osim nje, mogu se koristiti print*() funkcije
	 * za prikaz pojedinih dijelova... Ako zelimo da sve stranice imaju
	 * neki zajednicki dio onda napraviti svoj "CustomPage extends Page"
	 * koji ima svoju printTajDio() funkciju.
	 */
	public function printMain() {
	}

	/**
	 * Pokušava učitati objekt na osnovu defaultnog argumenta.
	 */
	protected function loadObjectById( $class ) {
		$arg = $this->getParam( 'arg' );
		$object = new $class();
		if( ! is_numeric( $arg ) ) {
			return false;
		}
		$id = (int) $arg;
		$object->setId( $id );
		if( ! $object->load() ) {
			return false;
		}
		$this->setObject( $object );
		return $object;
	}

	protected function loadObject( $class ) {
		$obj = $this->loadObjectBySef( $class );
		if( $obj ) {
			return $obj;
		}
		$obj = $this->loadObjectById( $class );
		return $obj;
	}

	protected function loadObjectBySef( $class ) {
		$arg = $this->getParam( 'arg' );
		$object = new $class();

		// Ako nema sef url onda ne mozemo ucitati po tome:
		@ $columns =& $object->getTableColumns();
		if( ! isset( $columns[ 'sef_url' ] ) ) {
			return false;
		}

		$sefUrl = $arg;

		if( ! $sefUrl )
			return false;

		$tableName = preg_replace( '/[^\w\d_]/', '', $object->getTableName() );
		$sql = new Sql( 'select * from ' . $tableName . ' where sef_url=:url' );
		$sql->setString( 'url', $sefUrl );

		$object = $sql->first( $class );
		if( ! $object ) {
			return false;
		}
		$this->setObject( $object );

		return $object;
	}

	/**
	 * Ako nađe sef učita po sefu i sve OK. Ako je id učita po id-u, ali
	 * postavi redirect.
	 * Propaziti da neki sef ne bi imao numeric vrijednost!
	 */
	protected function loadObjectBySefOrRedirect( $class ) {
		$arg = $this->getParam( 'arg' );
		if( is_numeric( $arg ) ) {
			$object = $this->loadObjectById( $class );
			if( $object ) {
				$this->setObject( $object );
				$this->redirect( url( $_GET[ 'page' ], $object ) );
				return $object;
			}
			else {
				$this->redirect( url( 'error' ) );
			}
		}
		return $this->loadObjectBySef( $class );
	}

	protected function loadObjectBySefOrCanonicalLink( $class ) {
		$arg = $this->getParam( 'arg' );
		if( is_numeric( $arg ) ) {
			$object = $this->loadObjectById( $class );
			if( $object ) {
				$params = $_GET;
				unset( $params[ 'page' ] );
				unset( $params[ 'arg' ] );
				$this->setObject( $object );
				$url = Application::SITE_URL . url( $_GET[ 'page' ], $object, $params );
				$this->setCanonicalUrl( $url );
				return $object;
			}
			else {
				$this->redirect( url( 'error' ) );
			}
		}
		return $this->loadObjectBySef( $class );
	}

	/** @see #setObject() */
	public function getObject() {
		// Ako nema objekta, a stranica je wizzard, onda treba uzeti iz wizardObjekta:
		if( ! $this->object && $this->isWizard() ) {
			$this->object = $this->getWizardObject();
		}
//		if( ! is_object( $this->object ) ) {
//			throw new AppException( 'Objekt nije postavljen a pokušava mu se pristupiti!' );
//		}
		return $this->object;
	}

	/**
	 * Objekt s kojim se radi u formi. Svaka stranica treba imati svoj objekt
	 * i svoj action.
	 *
	 * @see #setAction()
	 * @see #setObject()
	 */
	public function setObject( $object, $action = -1 ) {
		if( ! $object ) {
			throw new AppException( $object );
		}
		$this->object = $object;
		if( $action > -1 ) {
			$this->action = $action;
		}
		if( $this->isWizard() ) {
			$this->setWizardObject( $object );
		}
	}

	protected function getRedirectParam( $paramName ) {
		return $this->redirectParameters[ $paramName ];
	}

	protected function getGetString() {
		return $this->getString;
	}

	protected function getIntParam( $paramName ) {
		return (int) @$this->getParameters[ $paramName ];
	}

	protected function getBoolParam( $paramName ) {
		$value = @$this->getParameters[ $paramName ];
		return ( (bool) $value ) || @strtolower( $value ) == 'true';
	}

	protected function getParam( $paramName ) {
		return @$this->getParameters[ $paramName ];
	}

	protected function isParam( $paramName ) {
		return array_key_exists( $paramName, $this->getParameters );
	}

	/** Template u kojem ce se prikazivati. */
	public function setTemplate( $template ) {
		$this->template = $template;
	}

	public function getTemplate() {
		return $this->template;
	}

	protected function setMainPrint( $method ) {
		$this->mainPrintMethod = $method;
	}

	public function getMainPrint() {
		return $this->mainPrintMethod;
	}

	/**
	 * Ova metoda se moze pozivati i u execute* i u print* metodama. Jedino sto
	 * ce, ako su headeri vec poslani -- redirekcija ici preko javascripta.
	 *
	 * Preko $redirectParams se sljedecoj stranici mogu proslijediti neki
	 * serijalizirani parametri.
	 */
	protected function redirect( $url, $redirectParams = array() ) {
		if( ! is_array( $redirectParams ) ) {
			$redirectParams = array( $redirectParams );
		}
		Session::set( self::REDIRECT_PARAMS_SESSION, serialize( $redirectParams ) );
		if( ! headers_sent() ) {
			$this->redirect = $url;
		}
		else if( Application::DEBUG ) {
			echo '<h1>Redirect: <a href="' . $url. '">' . $url . '</a></h1>';
		}
		else {
?>
<script language='JavaScript'>
<!--
 setTimeout("window.location=\"<?= $url ?>\"",<?= (int) 1000 ?>);
 -->
</script><noscript><span style='font-weight: bold;'>Click <a href='<?= $url ?>'>here</a> to continue</span><br/>Click <a href='./'>here</a> to continue.</noscript>
<?php
		}
	}

	protected function setHttpCode( $c, $message = '' ) {
		$this->httpCode = $c;
		$this->httpCodeMessage = $message;
	}

	public function getHttpCode() {
		return (int) $this->httpCode;
	}

	public function getHttpCodeMessage() {
		return $this->httpCodeMessage;
	}

	/**
	 * @see #setRedirect()
	 * @see #isRedirect()
	 */
	public function getRedirect() {
		return $this->redirect;
	}

	/** Je li u do*() metodi postavljeno da stranica bude redirect ili ne? */
	public function isRedirect() {
		return $this->redirect != null;
	}

	/** Je li to submit nekeforme. */
	public function isSubmit() {
		return @sizeof( $this->postParameters ) > 0;
	}

	/**
	 * <b>VAŽNO</b> Ovo za sada mora biti pozvano već u konstruktoru!
	 */
	protected function setContentType( $type, $extension = null ) {
		$this->contentType = $type;
		if( $extension ) {
			$this->extension = $extension;
		}
		else {
			$temp = @explode( '/', $type );
			$this->extension = @$temp[ 1 ];
		}
	}

	public function getContentType() {
		return $this->contentType;
	}

	public function getExtension() {
		return $this->extension;
	}

	/**
	 * Čim je došlo do ove funkcije - treba keširati. $expressionString mora biti
	 * izraz o kojem ovisi treba li prikazati iz keša ili ne. Taj izraz ne može
	 * sadržavati nikakve pozive na standardne klase jer se izvršava prije bilo
	 * čega drugoga (tako da bi bilo što brže).
	 *
	 * Ako je $continue true ili neki izraz (kao string) onda će nakon ispisane
	 * stranice iz keša nastaviti izvršavati skriptu Izvršiti će samo onCache iz
	 * stranice i sve u Application.
	 *
	 * $expressionString je ujedno i kriterij o tome treba li snimati u cache ili ne.
	 *
	 * $seconds je broj sekndi koliko će se stranica maksimalno držati u cache-u.
	 *
	 * $expressionString može sdržavati sljedeće varijable:
	 * $seconds = kad je keš kreiran
	 * $loggedOn = je li korisnik logiran
	 */
	public function cache( $expressionString = '$seconds < 60 * 60 * 24', $continue = false ) {
		$cacheFunction = 'function ___cache_function($seconds,$logged){return ' . $expressionString . ';}';

		// Prvo idemo provjeriti treba li cache-irati:
		$time = 0;
		$loggedOn = $this->getSessionUser();
		$needCache = true;
		@eval( '$needCache = ' . $expressionString . ';' );
		if( ! $needCache ) {
			return;
		}

		$cacheFunction = '<? ' . $cacheFunction;

		if( is_bool( $continue ) ) {
			$continue = $continue ? 'true' : 'false';
		}
		$cacheFunction .= "\n" . 'function ___cache_continue($seconds,$logged){return ' . $continue . ';}';


		$contentType = $this->getContentType();
		if( @strlen( $contentType ) > 0 ) {
			$cacheFunction .= "\n" . 'function ___cache_content_type($seconds,$logged){return "' . $contentType . '";}';
		}

		Cache::save( self::CACHE_EXPRESSIONS, $_SERVER[ 'QUERY_STRING' ], $cacheFunction );

		$this->needCache = true;
	}

	/**
	 * Briše cache za stranicu. Ukoliko nema argumenta - briše cache za ovu stranicu,
	 * inače za bilo koju drugu.
	 */
	public function deleteCache( $link = null ) {
		if( ! $link ) {
			$link = $_SERVER[ 'QUERY_STRING' ];
		}
		Cache::delete( self::CACHE_EXPRESSIONS, $link );
		Cache::delete( self::CACHE, $link );
	}

	/**
	 * Metoda koja se poziva nakon prikazivanja keša AKO je to kod keširanja specifiricrano
	 *
	 * Tj. drugi argument od cache() na true...
	 */
	public function onCache() {}

	/** Vraca info o tome treba li cache-irati. */
	public function needCache() {
		return sizeof( $_POST ) == 0 && $this->needCache;
	}

	/** Proverava je li u cache-u. */
	public function isCached() {
		global $queryString;
		return Cache::isCached( self::CACHE, $_SERVER[ 'QUERY_STRING' ] );
	}

	public static function saveCache( $content ) {
		Cache::save(
			self::CACHE, $_SERVER[ 'QUERY_STRING' ],
			gzcompress( $content )
		);
	}

	public function getSession( $var ) {
		return Session::get( $var );
	}

	public function setSession( $var, $value ) {
		Session::set( $var, $value );
	}

	public function getSessionUser() {
		return Session::getUser();
	}

	public function setSessionUser( $user ) {
		Session::setUser( $user );
	}

	public function getCookie( $var ) {
		return Cookies::get( $var );
	}

	public function setCookie( $var, $content, $expire = -1 ) {
		Cookies::set( $var, $content, $expire );
	}

	public function addMetaTag( $name, $content ) {
		$content = str_replace( '"', ' ', $content );
		$content = str_replace( "'", ' ', $content );
		$this->htmlHead[ 'meta-' . $name ] = '<meta name="' . $name . '" content="' . $content . '">';
	}

	public function setCanonicalUrl( $url )	{
		if( strpos( $url, '://' ) === false ) {
			$url = Application::SITE_URL . $url;
		}
		$this->htmlHead[ 'canonical' ] = '<link href="' . $url . '" rel="canonical"/>';
	}

	///////////////////////////////////////////////////////
	// Statične metode za korištenje u template-u:

	/** Koristi se u template-u */
	private static $contents = array();

	public static function printJavascript( $js = 'utils.js' ) {
		$file = FRAMEWORK . 'js/' . $js . '.php';
		if( is_file( $file ) ) {
			include( $file );
		}
	}

	public static function printHtmlHead() {
		echo '<base href="' . Application::SITE_URL . '" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
';
		global $page;
		if( is_array( $page->htmlHead ) ) {
			foreach( $page->htmlHead as $key => $value ) {
				echo $value;
				echo "\n";
			}
		}
	}

	/** Ovu metodu izbaciti! */
	public static function printMetaTags() {
		self::printHtmlHead();
	}

	public static function has( $method ) {
		$result = self::getContent( $method );
		if( strlen( $result ) > 0 ) {
			self::$contents[ $method ] = $result;
			return true;
		}
		return false;
	}

	public static function get( $method = '' ) {
		global $page;
		if( strlen( $method ) == 0 ) {
			$method = $page->getMainPrint();
		}
		if( isset( self::$contents[ $method ] ) ) {
			return self::$contents[ $method ];
		}
		return self::getContent( $method );
	}

	public static function show( $method = '' ) {
		global $page;
		if( strlen( $method ) == 0 ) {
			$method = $page->getMainPrint();
		}
		echo self::get( $method );
	}

	private static function getContent( $method = '' ) {
		global $page;
		if( strlen( $method ) == 0 ) {
			$method = $page->getMainPrint();
		}
		ob_start();
		$exception = false;
		try {
			if( strlen( $method ) == 0 ) {
				// Ako je stranica sama zatražila da glavni bude neki drugi method:
				$method = $page->getPrint();
				$methodName = 'print' . $method;
			}
			else {
				// Defaultni view:
				$methodName = 'print' . $method;
			}
			if( method_exists( $page, $methodName ) ) {
				$page->$methodName();
			}
			else {
				// TODO
//				// Ukoliko ne postoji ta metoda, onda se mozda radi o zasebnom fajlu:
//				$fileName = str_replace( '.class.php', '', $this->pageClassFile ) . $method . '.php';
//				if( file_exists( $fileName ) ) {
//					include( $fileName );
//				}
			}
		}
		catch( Exception $e ) {
			$exception = $e;
		}
		$contents = ob_get_contents();
		ob_end_clean();

		if( $exception ) {
			throw $exception;
		}
		return $contents;
	}

	// Par funkcija za slučaj da se radi o wizardu:

	/**
	 * Sljedeća stranica. Ako je $step > 1, onda se ide toliko stranica unaprijed.
	 */
	public function getNextLink( $step = 1 ) {
		$page = $_GET[ 'page' ];
		$parts = explode( 'wizard', $page );
		$n = (int) @$parts[ 1 ];
		if( $n == 0 ) {
			$n = '';
		}
		$page = str_replace( 'wizard' . $n, 'wizard' . ( $n + $step ), strtolower( $page ) );
		return url( $page );
	}

	public function redirectToNext( $step = 1 ) {
		return $this->redirect( $this->getNextLink( $step ) );
	}

	/**
	 * Postavlja objekt za trenutni wizzard.
	 * U slucaju da stranice nije dio wizarda, ignorirati ovu metodu.
	 */
	private function setWizardObject( $object ) {
		// TODO: Brisati stare wizarde:
		Session::set( 'wizzard-' . $this->currentWizardKey(), $object );
	}

	private function getWizardObject() {
		return Session::get( 'wizzard-' . $this->currentWizardKey() );
	}

	private function currentWizardKey() {
		return preg_replace( '/wizard\d+/', 'wizard', '' . $this->path );
	}

	protected function isWizard() {
		return ! ( false === strpos( get_class( $this ), 'Wizard' ) );
	}

}

