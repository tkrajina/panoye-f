<?php

/**
 * Klasa za kreiranje jednostavnih RSS datoteka.
 *
 * TODO: Datum i timestamp
 */
class RSSBuilder {

	private $header = '';
	private $items = '';

    public function __construct( $title, $link, $description, $generator = '' ) {
    	$this->header = $this->tag( 'title', $title );
    	$this->header .= $this->tag( 'link', $link );
    	$this->header .= $this->tag( 'description', $description );
    	$this->header .= $this->tag( 'generator', $generator );
    }

	public function addItem( $title, $link, $description, $date = null, $custom = '' ) {
    	$result = $this->tag( 'title', $title );
    	$result .= $this->tag( 'link', $link );
    	$result .= $this->tag( 'description', $description );
    	$result .= $custom;
    	$result = '<item>' . $result . '</item>';
    	$this->items .= $result;
	}

	public function getRss() {
		return '<?xml version="1.0"?>' . "\n" . '<rss version="2.0"><channel>'
			. $this->header
			. $this->items
			. '</channel></rss>';
	}

	private function tag( $tag, $content ) {
		return '<' . $tag . '>' . htmlspecialchars( $content ) . '</' . $tag . '>' . "\n";
	}

}