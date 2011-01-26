<?

/** $gzipped should be gzipped with gzcompress() ! */
function printGzippedPage( $gzipped ) {
	$httpAcceptEncoding = $_SERVER[ 'HTTP_ACCEPT_ENCODING' ];
	if( headers_sent() ) {
		$encoding = false;
	}
	elseif( strpos( $httpAcceptEncoding, 'x-gzip' ) !== false ) {
		$encoding = 'x-gzip';
	}
	elseif( strpos( $httpAcceptEncoding, 'gzip' ) !== false ) {
		$encoding = 'gzip';
	}
	else{
		$encoding = false;
	}

	if( $encoding ) {
		header( 'Content-Encoding: ' . $encoding );
		echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
		echo $gzipped;
	}
	else {
		echo gzuncompress( $gzipped );
	}
}

function executeCachedPage( $fileName, $queryString ) {

	@include $fileName;

	$pageFileName = Cache::getFileName( 'pages_cache', $queryString );
	if( is_file( $pageFileName ) ) {

		$seconds = (int) ( time() - filemtime( $pageFileName ) );
		$logged = is_object( Session::getUser() );

		if( is_callable( '___cache_content_type' ) ) {
			$contentType = ___cache_content_type( $seconds, $logged );
			if( ( (int) @strlen( $contentType ) ) > 0 ) {
				header( 'Content-Type', $contentType );
			}
		}

		// Ako je stranica prestara: brišemo ju:
		if( $seconds > 60 * 60 * 48 ) {
			@unlink( $fileName );
			@unlink( $pageFileName );
		}
		else if( is_callable( '___cache_function' ) && ___cache_function( $seconds, $logged ) ) {
			printGzippedPage( file_get_contents( $pageFileName ) );

			Logs::info( 'Served page from cache' );

			return true;
		}
	}
	else {
			@unlink( $fileName );
	}

	return false;
}

