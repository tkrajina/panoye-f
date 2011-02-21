#!/usr/bin/php -q
<?php

$data = array(
	'db_user' => readline( 'DB user:' ),
	'db_password' => readline( 'DB password:' ),
	'db_database' => readline( 'Database:' ),
	'site_url' => readline( 'Site url:' ),
	'site_title' => readline( 'Site title:' ),
	'secret_key' => md5( microtime( true ) . '' . rand( 1, 10000 ) . rand( 1, 10000 ) ),
);

if( ! preg_match( '/^.*\/$/', $data[ 'site_url' ] ) ) {
	$data[ 'site_url' ] = $data[ 'site_url' ] . '/';
}

if( ! preg_match( '/^http:\/\/.*$/', $data[ 'site_url' ] ) ) {
	$data[ 'site_url' ] = 'http://' . $data[ 'site_url' ] . '/';
}

@mkdir( 'app' );
@mkdir( 'app/lib' );
@mkdir( 'app/objects' );
@mkdir( 'app/pages' );
@mkdir( 'app/templates' );
@mkdir( 'cache' );
@chmod( 'cache', 0777 );
@chmod( 'app/pages', 0777 );
@chmod( 'app/objects', 0777 );

if( is_dir( 'framework_tests' ) ) {
	symlink( '../framework_tests', 'app/test' );
}
else {
	@mkdir( 'app/test', 0777 );
	@chmod( 'app/test', 0777 );
	save( 'app/test/ExampleTest.class.php', doTemplate( 'ExampleTest.class' ) );
}

function doTemplate( $name, $replacements = array() ) {
	$contents = file_get_contents( 'app_tools/templates/' . $name . '.template' );
	foreach( $replacements as $key => $value ) {
		$contents = str_replace( '{{' . $key . '}}', $value, $contents );
	}
	return $contents;
}

require 'framework/main.php';

$data[ 'version' ] = FRAMEWORK_VERSION;

function save( $file, $contents ) {
	if( is_file( $file ) ) {
		echo $file . ' exists' . "\n";
		return;
	}
	file_put_contents( $file, $contents );
}

save( 'app/Application.class.php', doTemplate( 'Application.class', $data ) );
save( 'index.php', doTemplate( 'index' ) );
save( 'app/templates/default.php', doTemplate( 'default' ) );
save( '.htaccess', doTemplate( 'htaccess' ) );
save( 'app/pages/ApplicationPage.class.php', doTemplate( 'ApplicationPage.class' ) );
save( 'app/pages/TestPage.class.php', doTemplate( 'TestPage.class' ) );
save( 'app/pages/ExamplePage.class.php', doTemplate( 'ExamplePage.class' ) );

echo 'Done, now go to ' . $data[ 'site_url' ] . 'application and create your table CRUDs';
