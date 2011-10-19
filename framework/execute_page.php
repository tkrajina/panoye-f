<?php

defined( 'APP' ) or die( '!' );

global $queryString, $page;
global $pageClassFile;

try {

	$parser = new PageParser();
	$page = $parser->getPage();

	if( defined( 'CONTINUE_AFTER_CACHE' ) ) {
		// The page requested to continue the execution of the script even after the cached
		// contents were printed. We must now call onCache method:

		$page->onCache();
	}
	else {
		// Normal page execution:
		$page->init();

		// If there are any POST data - they may have changed this page, so we'll delete
		// the cached version:
		if( sizeof( $_POST ) > 0 ) {
			$page->deleteCache();
		}

		global $application, $applicationEvents;

		// Call Application::onPage:
		if( is_callable( array( $applicationEvents, 'onPage' ) ) ) 
			$applicationEvents->onPage( $page );

		// Execute the page:
		$page->executePage();

		$contentType = $page->getContentType();
		if( ! $contentType || $contentType != 'text/html' ) {
			header( 'Content-Type: ' . $contentType );
		}

		if( $page->isRedirect() ) {
			$redirectUrl = $page->getRedirect();

			// Provjera da nije redirekcija na potpuno istu stranicu:
//			$parts = explode( $redirectUrl, $_SERVER[ 'REQUEST_URI' ] );
//			if( sizeof( $parts ) == 2 && strlen( $parts[ 1 ] ) == 0 ) {
//				$redirectUrl = url( 'error' );
//			}

			// Provjera je li adresa relativna ili apsolutna:
			if( strpos( $redirectUrl, '://' ) === false ) {
				$redirectUrl = Application::SITE_URL . $redirectUrl;
			}

			if( Application::DEBUG ) {
				echo '<html><body>';
				echo 'Redirect: <a href="' . $redirectUrl . '">' . $redirectUrl . '</a>';
				echo '</body></html>';
			}
			else {
				// Pogledati http://edoceo.com/creo/php-redirect.php za ostale kodove
				// kod redirektanja...
				// Bez ovoga jer firefox javlja nekakvu glupu gresku kad se redirecta nakon forme:
				if( sizeof( $_POST ) == 0 ) {
					header( 'HTTP/1.0 307 Temorary Redirect' );
					header( 'Location: ' . $redirectUrl );
				}

				else {
					// IE 6 bug when redirecting after POST request:
					echo '<html>
<head>
<style type="text/css">
body {
	margin:10px;
	font-family: arial, helvetica, sans-serif;
	font-size: 11px;
	background-color: white;
	color: black;
}
</style>
<meta http-equiv="refresh" content="0.3;url=' . $redirectUrl . '" />
<title>Redirect</title>
</head>
<body>
Redirecting to <a href="' . $redirectUrl . '">' . str_replace( 'http://', '', $redirectUrl ) . '</a>...
</body>
</html>';
				}
			}
		}
		else {
			$code = (int) $page->getHttpCode();
			if( $code > 0 && ! headers_sent() ) {
				header( 'HTTP/1.0 ' . $code . ' ' . $page->getHttpCodeMessage() );
			}

			// Ispis:
			$template = $page->getTemplate();
			ob_start();
			if( $template == 'empty' ) {
				echo Page::get();
			}
			else {
				import( APP, 'templates/' . $template . '.php' );
			}
			$contents = @ob_get_contents();
			ob_clean();

			echo $contents;

			if( $page->needCache() ) {
				Page::saveCache( $contents );
			}
		}
	}
}
catch( Exception $e ) {
	Logs::fatal( $e );
	header('HTTP/1.0 404 Not Found');
	Tags::open( 'html' );
	Tags::open( 'body' );
	echo 'Page not found';
	Tags::closeAll();
}
