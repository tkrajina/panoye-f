///////////////////////////////////////////////
function AjaxCall( ajaxMethod, arguments ) {
	var xmlHttp = false;
	try {
		xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
	}
	catch( e ) {
		try {
			xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch( e2 ) {
			xmlHttp = false;
		}
	}
	if( ! xmlHttp && typeof XMLHttpRequest != 'undefined' ) {
		xmlHttp = new XMLHttpRequest();
	}

	onResult = function() {
		document.body.style.cursor = 'default';
		if( xmlHttp.readyState == 4 ) {
			var text = xmlHttp.responseText
			var pos = text.indexOf( "\n" )
			var header = text.substring( 0, pos )
			var body = text.substring( pos + 1 )

			var _function = false;
			try {
				_function = eval( "ajax" + ajaxMethod );
			}
			catch( e ) {}
			if( ! _function ) {
				alert( "Function ajax" + ajaxMethod + " not defined!" );
			}
			else {
				_function( header, body );
			}
		}
	}

	var url = null;
	var current = "" + window.location;
	var parts = current.split( "?" );
	url = "<?= Application::SITE_URL ?>";
	url += "?ajax=" + ajaxMethod;
	if( arguments ) {
		for( a in arguments ) {
			url += "&" + a + "=" + arguments[ a ];
		}
	}

	xmlHttp.open ( 'GET', url );
	xmlHttp.onreadystatechange = onResult;

	document.body.style.cursor = 'wait';
	xmlHttp.send( null );
}

/////////////////////////////////////////////////////////////
var Ajax = new Object();
Ajax.call = function( method, arguments ) {
	new AjaxCall( method, arguments );
}