<?php

class Strings {

	private static $accented = array('Á','Â','Ä','Ç','É','Ë','Í','Î','Ó','Ô','Ö','Ú','Ü','Ý','Ă','Ą','Ć','Č','Ď','Đ','Ę','Ě','Ĺ','Ľ','Ł','Ń','Ň','Ő','Ŕ','Ř','Ś','Ş','Š','Ţ','Ť','Ů','Ű','Ź','Ż','Ž','µ','ß','á','â','ä','ç','é','ë','í','î','ó','ô','ö','ú','ü','ý','ă','ą','ć','č','ď','đ','ę','ě','ĺ','ľ','ł','ń','ň','ő','ŕ','ř','ś','ş','š','ţ','ť','ů','ű','ź','ż','ž','ñ','Ñ');
	private static $nonAccented = array('A','A','A','C','E','E','I','I','O','O','O','U','U','Y','A','A','C','C','D','D','E','E','L','L','L','N','N','O','R','R','S','S','S','T','T','U','U','Z','Z','Z','u','s','a','a','a','c','e','e','i','i','o','o','o','u','u','y','a','a','c','c','d','d','e','e','l','l','l','n','n','o','r','r','s','s','s','t','t','u','u','z','z','z','n','N');

	public static function wrapTo( $text, $n = 150 ) {
		$delimiter = '' . rand( 100000, 10000000 );
		$wrapped = wordwrap( $text, $n, $delimiter );
		$pos = strpos( $wrapped, $delimiter );
		if( $pos <= 0 ) {
			return $text;
		}
		return substr( $wrapped, 0, $pos ) . '...';
	}

	public static function removeAccents( $str ) {
		if( ! is_string( $str ) ) {
			return $str;
		}
		return str_replace( self::$accented, self::$nonAccented, $str );
	}

	public static function seoFriendly( $string ) {
		$string = strip_tags( $string );
		$string = trim( self::wrapTo( '' . $string, 50 ) );
		$string = self::removeAccents( $string );
		$string = trim( preg_replace( '/[^\w\d]+/', ' ', $string ) );
		$string = trim( preg_replace( '/\s+/', '-', $string ) );

		// Ne smije biti broj jer se onda u urlu ne zna je li ID ili seo frendly link:
		if( is_numeric( $string ) ) {
			$string = 'n-' . $string;
		}

		if( strlen( $string ) <= 1 ) {
			// ako baš ne može => random string:
			$string = preg_replace( '/\d/', '', md5( '' . microtime( true ) ) );
		}

		return strtolower( $string );
	}

}