<?

class Curl {

	public static function copy( $url, $fileName ) {
		$curl = curl_init();
		$fp = fopen( $fileName, 'w' );
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_FILE, $fp );

		if( ! @curl_exec( $curl ) ) {
			@curl_close( $curl );
			return false;
		}
		@curl_close( $curl );

		fclose( $fp );

		return true;
	}

	public static function retrieve( $url ) {
		$curl = curl_init();

		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_BINARYTRANSFER, true );

		$result = @curl_exec ($curl);
		@curl_close( $curl );

		return $result;
	}

}