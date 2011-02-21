#!/usr/bin/php -q
<?php

define( 'CACHE', 'cache/' );

$handle = @opendir( CACHE );
if( ! $handle ) {
	return false;
}
$directories = array();
while( $f = readdir( $handle ) ) {
	if( $f == "." || $f == ".." ) {
		continue;
	}
	$directories[] = CACHE . $f;
}
closedir( $handle );

foreach( $directories as $dir ) {
	$handle = @opendir( $dir );
	if( ! $handle ) {
		return false;
	}
	$directories = array();
	while( $f = readdir( $handle ) ) {
		if( $f == "." || $f == ".." ) {
			continue;
		}
		$f = $dir . '/' . $f;
		if( is_file( $f ) ) {
			unlink( $f );
		}
	}
	closedir( $handle );
	rmdir( $dir );
}
