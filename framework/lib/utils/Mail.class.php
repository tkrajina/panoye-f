<?

/**
 *
 * --------------------------------------------------------------
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * --------------------------------------------------------------
 *
 * (c) by Tomo Krajina
 *     aaa@puzz.info
 *
 */

class Mail {

	var $to = "";
	var $from = "";
	var $reply = "";
	var $xmailer = "";
	var $subject = "";
	var $msg = "";
	var $contentType = 'text/plain';

	function __construct( $from = '', $to = '', $subject = '', $message = '' ) {
		$this->to = $to;
		$this->subject = $subject;
		$this->msg = $message;
		$this->from = $from;
	}

	function setSubject( $subject ) {
		$this->subject = $subject;
	}

	function setTo( $to ) {
		$this->to = $to;
	}

	function setFrom( $from ) {
		$this->from = $from;
	}

	function setMsg( $msg ) {
		$this->msg = $msg;
	}

	function setHtmlMsg( $msg ) {
		$this->msg = $msg;
		$this->setContentType( 'text/html' );
	}

	function setReplyTo( $reply ) {
		$this->reply = $reply;
	}

	function setXMailer( $xmailer ) {
		$this->xmailer = $xmailer;
	}

	function setContentType( $ct ) {
		$this->contentType = $ct;
	}

	function send( $headers = '' ) {
		$h = '';

		if( $this->from ) {
			$h .= "From: {$this->from}\r\n";
		}
		if( $this->reply ) {
			$h .= "Reply-To: {$this->reply}\r\n";
		}
		$h .= "MIME-VERSION: 1.0\r\n";
		if( $this->contentType ) {
			$h .= "Content-type: {$this->contentType}; charset=utf-8\r\n";
		}

		if( $this->xmailer ) {
			$h .= "X-Mailer: {$this->xmailer}\r\n";
		}
		else {
			$h .= 'X-Mailer: PHP v' . phpversion() . '\r\n';
		}

//		d( "@mail( {$this->to}, {$this->subject}, {$this->msg}, $h ) )<br/>" );
		if( ! mail( $this->to, $this->subject, $this->msg, $h ) ) {
			Logs::error( 'Mail ("' . $this->to . '", "' . $this->subject . '") not send!' );
			return false;
		}
		else {
			return true;
		}
	}

}
