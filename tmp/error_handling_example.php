<?

function customError( $errno, $errstr, $errfile, $errline, $errcontext ) {
	echo '<b>Error:</b> [' . $errno . '] ' . $errstr . '<br />';
	echo $errfile . '<br/>';
	echo $errline . '<br/>';
	echo $errcontext . '<br/>';
	echo "Ending Script";
	die();
}

set_error_handler( 'customError', E_ALL );
