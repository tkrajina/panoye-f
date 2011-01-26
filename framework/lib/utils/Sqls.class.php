<?php

/**
 * Pomoćne funkcije za rad s sql-ovima.
 *
 * U SQL template-ovima:
 * {{:propertyName}} -- za običnu izmjenu
 * {{s:imePropertyja}} -- string iz propertyija
 * {{d:property}} -- double iz property-ija
 * {{i:property}} -- int iz property-ija
 */
class Sqls {

	private function __construct() {}

	public static function replace( $sqlTemplate, $arrayOrObject1, $object2 = null, $object3 = null, $object4 = null ) {
		if( Application::DEBUG ) {
			Logs::warn( 'Deprecated, use Sql instead' );
		}
		$result = $sqlTemplate;
		if( is_array( $arrayOrObject1 ) ) {
			$array = $arrayOrObject1;
		}
		else {
			$array = array( $arrayOrObject1, $object2, $object3, $object4 );
		}
		foreach( $array as $k => $v ) {
			// TODO: quote!!!
			$result = str_replace( '{{s:' . $k . '}}', self::prepareString( $v ), $result );
			$result = str_replace( '{{i:' . $k . '}}', self::prepareInt( $v ), $result );
			$result = str_replace( '{{d:' . $k . '}}', self::prepareDecimal( $v ), $result );
			$result = str_replace( '{{:' . $k . '}}', $v, $result );
		}
		return $result;
	}

	public static function prepareInt( $value ) {
		if( Application::DEBUG ) {
			Logs::warn( 'Deprecated, use Db:: instead' );
		}
		return Db::prepareInt( $value );
	}

	public static function prepareDecimal( $value ) {
		if( Application::DEBUG ) {
			Logs::warn( 'Deprecated, use Db:: instead' );
		}
		return Db::prepareDecimal( $value );
	}

	public static function prepareString( $value ) {
		if( Application::DEBUG ) {
			Logs::warn( 'Deprecated, use Db:: instead' );
		}
		return Db::prepareString( $value );
	}

}
