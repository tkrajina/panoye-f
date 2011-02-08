<?php

class String {

	   private $string;

	   public function __construct( $str = '' ) {
			   $this->string = $str;
	   }

	   public function length() {
			   return strlen( $this->string );
	   }

	   /** Ako je old regularni izraz => onda kao regularni izraz, inace obicno. */
	   public function replace( $old, $new ) {
			   if( @$old[ 0 ] == '/' ) {
					   $this->string = preg_replace( $old, $new, $this->string );
			   }
			   else {
					   $this->string = str_replace( $old, $new, $this->string );
			   }
	   }

	   public function indexOf( $str ) {
			   $result = strpos( $this->string, $str );
			   if( is_numeric( $result ) ) {
					   return $result;
			   }
			   return -1;
	   }

	   /** Vraća instance klase String. */
	   public function split( $expression ) {
			   $parts = split( $expression, $this->string );
			   $result = array();
			   foreach( $parts as $part ) {
					   $result[] = new String( $part );
			   }
			   return $result;
	   }

	   public function charAt( $n ) {
			   return @$this->string[ $n ];
	   }

	   public function append( $string ) {
			   $this->string .= '' . $string;
	   }

	   public function toString() {
			   return $this->string;
	   }

	   public function __toString() {
			   return $this->string;
	   }

}

/*
$s = new String( 'Ovo je samo test' );
echo $s->indexOf( 'je' ) . BR;
$s->replace( 'o', 'a' );
echo '' . $s . BR;
$s->replace( '/a(m)a/', '\1' );
echo '' . $s . BR;
echo $s->length() . BR;
$l = $s->length();
for( $i = 0; $i < $l; $i++ ) {
	   echo $s->charAt( $i ) . BR;
}
*/