var Utils = new Object();
Utils.byId = function( id ) {
	if( document.all ) {
		return document.all[ id ];
	}
	return document.getElementById( id );
}
Utils.addListener = function( element, event, _function ) {
	if( element && element.addEventListener ) {
		element.addEventListener( event, _function, false )
	}
	else if( element.attachEvent ) {
		element.attachEvent( "on" + event, _function )
	}
}
Utils.removeListener = function( element, event, _function ) {
	if( element && element.removeEventListener ) {
		element.removeEventListener( event, _function, false )
	}
	else if( element.detachEvent ) {
		element.detachEvent( "on" + event, _function )
	}
}
Utils.getInnerHTML = function( id ) {
	var element = this.byId( id );
	if( element ) {
		return element.innerHTML;
	}
	return false;
}
Utils.setInnerHTML = function( id, content ) {
	var element = this.byId( id );
	if( element ) {
		element.innerHTML = content;
	}
}
Utils.position = function( element ) {
	var tmp = element;
	var x = 0;
	var y = 0;
	while( tmp ) {
		if( tmp && tmp.offsetLeft ) {
			x += tmp.offsetLeft;
			y += tmp.offsetTop;
		}
		tmp = tmp.parentNode;
	}
	return [ x, y ]
}
Utils.addChild = function( element, tag, content, attributes ) {
	var child = document.createElement( tag );
	child.innerHTML = content;
	if( attributes ) {
		for( i in attributes ) {
			child.setAttribute( i, attributes[ i ] );
		}
	}
	element.appendChild( child );
}

Popup = new Object();
Popup.hideMenuTimeouts = new Array();
Popup.registeredMenus = new Array();
Popup.maxZIndex = 9999;

Popup.register = function( menu ) {
	if( "string" == typeof menu ) {
		menu = Utils.byId( menu );
	}
	if( menu ) {
		Popup.registeredMenus.push( menu );
	}
}

Popup.show = function( menu ) {
	if( "string" == typeof menu ) {
		menu = Utils.byId( menu );
	}
	try {
		// Ako je u toku postupak zatvaranja: brisemo to:
		clearTimeout( Popup.hideMenuTimeouts[ menu.id ] );
	}
	catch( e ) {}
	// Brisemo sve ostale menije koji su eventualno zatvoreni:
	for( m in Popup.registeredMenus ) {
		m = Popup.registeredMenus[ m ];
		if( m && m.style ) {
			m.style.visibility = "hidden";
		}
	}
	if( menu ) {
		Popup.register( menu );
		menu.style.visibility = "visible";
		menu.style.zIndex = Popup.maxZIndex;
		++ Popup.maxZIndex;
	}
}

Popup.hide = function( menu ) {
	if( "string" == typeof menu ) {
		menu = Utils.byId( menu );
	}
	var str = "Utils.byId(\"" + menu.id + "\").style.visibility=\"hidden\"";
	Popup.hideMenuTimeouts[ menu.id ] = setTimeout( str, 750 );
}