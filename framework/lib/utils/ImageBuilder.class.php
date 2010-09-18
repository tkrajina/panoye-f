<?php

class ImageBuilder {

	private $width, $height;

	private $image;

	private $penColor = 255;

	public function __construct( $width, $height, $red = 0, $green = 0, $blue = 0 ) {
		$this->width = $width;
		$this->height = $height;
		$this->image = imagecreatetruecolor( $width, $height );
		if( $red != 0 || $green != null || $blue != null ) {
			$this->fill( $red, $green, $blue );
		}
	}

	private function getColor( $red = 0, $green = 0, $blue = 0 ) {
		if( $red != 0 || $green != null || $blue != null ) {
			return imagecolorallocate( $this->image, $red, $green, $blue );
		}
		return $this->penColor;
	}

	public function fill( $red = 0, $green = 0, $blue = 0 ) {
		imagefill( $this->image, 0, 0, $this->getColor( $red, $green, $blue ) );
	}

	public function text( $str, $x, $y, $fontSize = 2 ) {
		imagestring( $this->image, $fontSize, $x, $y, $str, $this->penColor );
	}

	public function textVertical( $str, $x, $y, $fontSize = 2 ) {
		imagestringup( $this->image, $fontSize, $x, $y, $str, $this->penColor );
	}

	public function line( $x1, $y1, $x2, $y2 ) {
		imageline( $this->image, $x1, $y1, $x2, $y2, $this->penColor );
	}

	public function rectangle( $x1, $y1, $x2, $y2 ) {
		imagefilledrectangle( $this->image, $x1, $y1, $x2, $y2, $this->penColor );
	}

	public function printImage( $headers = false ) {
		if( $headers ) {
			header( 'Content-type: image/png' );
		}
		imagepng( $this->image );
	}

	public function setPenColor( $red, $green, $blue ) {
		$this->penColor = imagecolorallocate( $this->image, $red, $green, $blue );
	}

	public function getWidth() {
		return $this->width;
	}

	public function getHeight() {
		return $this->height;
	}

}

//$i = new ImageBuilder( 100, 200, 13, 12, 255 );
//
//$i->setPenColor( 40, 40, 40 );
//$i->text( 'dsjkaldjksa', 10, 10 );
//
//$i->setPenColor( 255, 255, 155 );
//$i->line( 10, 10, 40, 40 );
//
//$i->setPenColor( 255, 0, 0 );
//$i->textVertical( 'vertikalno', 0, 100, 1 );
//
//$i->rectangle( 50, 50, 100, 100 );
//
//$i->printImage( true );