<?php

/**
 * Utility class for common jpeg transformations.
 */
class JpegImage {

	private $fileName;
	private $width;
	private $height;
	private $valid = false;

	/** Jpeg quality. */
	private $quality = 90;

	public function __construct( $fileName, $transformationsQuality = null ) {
		if( is_numeric( $transformationsQuality ) ) {
			$this->quality = $transformationsQuality;
		}
		$this->fileName = $fileName;
		$this->reload();
	}

	/** Reloads image size. */
	private function reload() {
//		if( ! preg_match( '/^.*.jpg$/', strtolower( $this->fileName ) ) || ! preg_match( '/^.*.jpeg$/', strtolower( $this->fileName ) ) ) {
//			$this->valid = false;
//			return;
//		}
		if( ! file_exists( $this->fileName ) ) {
			$this->valid = false;
			return;
		}
		$size = @getimagesize( $this->fileName );
		if( ! $size ) {
			$this->valid = false;
			return;
		}
		list( $this->width, $this->height ) = $size;
		$this->valid = true;
	}

	public function getWidth() {
		return $this->width;
	}

	public function getHeight() {
		return $this->height;
	}

	public function isValid() {
		return (boolean) $this->valid;
	}

	public function getFileName() {
		return $this->fileName;
	}

	/** Image crop. If there is no $dest -- the crop will affect $this image. */
	public function crop( $left1, $top1, $left2, $top2, $dest = null ) {
		$left1 = (int) $left1;
		$left2 = (int) $left2;
		$top1 = (int) $top1;
		$top2 = (int) $top2;
		if( ! $dest ) {
			$dest = $this->fileName;
		}

		$left1 = (int) $this->inInterval( $left1, 0, $this->width );
		$left2 = (int) $this->inInterval( $left2, 0, $this->width );
		$top1 = (int) $this->inInterval( $top1, 0, $this->height );
		$top2 = (int) $this->inInterval( $top2, 0, $this->height );

		$img = imagecreatefromjpeg( $this->fileName );

/*
resource dst_image, resource src_image,
int dst_x, int dst_y,
int src_x, int src_y,
int dst_w, int dst_h, int src_w, int src_h )
 */
		$dimg = imagecreatetruecolor( $left2 - $left1, $top2 - $top1 );
		imagecopyresampled( $dimg, $img,
			0, 0, // Početak prvog
			$left1, $top1, // Početak drugog
			$left2 - $left1, $top2 - $top1,
			$left2 - $left1, $top2 - $top1 );

		if( $dest == $this->fileName ) {
			unlink( $dest );
		}
		$result = (boolean) @imagejpeg( $dimg, $dest, $this->quality );
		$this->reload();
//		echo "Dakle: $left1, $top1, $left2, $top2<br/>";
		return $result;
	}

	/** Scale image. */
	public function scale( $coefX, $coefY, $dest = null ) {
		if( ! $dest ) {
			$dest = $this->fileName;
		}

		if( $coefX <= 0 || $coefY <= 0 ) {
			return false;
		}

		$img = @imagecreatefromjpeg( $this->fileName );
		if( ! $img ) {
			return false;
		}

		$newWidth = $this->width * $coefX;
		$newHeight = $this->height * $coefY;
		$dimg = imagecreatetruecolor( $newWidth, $newHeight );
		imagecopyresampled( $dimg, $img,
			0, 0, // Početak prvog
			0, 0, // Početak drugog
			$newWidth, $newHeight, // kraj prvog
			$this->width, $this->height ); // kraj drugog

		if( $dest == $this->fileName ) {
			unlink( $dest );
		}
		$result = (boolean) @imagejpeg( $dimg, $dest, $this->quality );
		$this->reload();
//		echo $this->fileName . BR;
		return $result;
	}

	/** Scale only for dimension x (width). */
	public function scaleX( $coef, $dest = null ) {
		return $this->scale( $coef, 1, $dest );
	}

	/** Scale only for dimension y (height). */
	public function scaleY( $coef, $dest = null ) {
		return $this->scale( 1, $coef, $dest );
	}

	/** Resize the image in $maxWidth x $maxHeight. */
	public function resize( $maxWidth, $maxHeight, $dest = null ) {
		$coef = min( $maxWidth / $this->width, $maxHeight / $this->height );
		return $this->scale( $coef, $coef, $dest );
	}

	public function resizeToWidth( $width, $dest = null ) {
		$coef = $width / $this->width;
		return $this->scale( $coef, $coef, $dest );
	}

	public function resizeToHeight( $height, $dest = null ) {
		$coef = $height / $this->height;
		return $this->scale( $coef, $coef, $dest );
	}

	/** Quality for transformed files. */
	public function setTransformationQuality( $quality ) {
		if( $quality < 0 ) {
			$quality = 0;
		}
		if( $quality > 100 ) {
			$quality = 100;
		}
		$this->quality = $quality;
	}

	private function inInterval( $x, $min, $max ) {
		if( $x < $min ) {
			return $min;
		}
		if( $x > $max ) {
			return $max;
		}
		return $x;
	}

}