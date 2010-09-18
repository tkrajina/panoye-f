<?php

class TagsTest extends AppTest {

	public function testOpenCloseTag() {
		ob_start();
		Tags::open( 'div' );
		Tags::close();
		$contents = ob_get_contents();
		ob_clean();

		$this->assertTrue( $contents === '<div></div>' );
	}

	public function testOpenCloseTagWithParams() {
		ob_start();
		Tags::open( 'div', array( 'class' => 'test', 'a' => 'dsa"jd' ) );
		Tags::close();
		$contents = ob_get_contents();
		ob_clean();

		d( htmlspecialchars( $contents ) );

		$this->assertTrue( $contents === '<div class="test" a="dsa\"jd"></div>' );
	}

	public function testCloseAll() {
		ob_start();
		Tags::open( 'div', array( 'class' => 'test' ) );
		Tags::open( 'b' );
		echo 1;
		Tags::tag( 'hr' );
		Tags::closeAll();
		$contents = ob_get_contents();
		ob_clean();

		d( htmlspecialchars( $contents ) );

		$this->assertTrue( $contents === '<div class="test"><b>1<hr/></b></div>' );
	}

	public function testClose() {
		ob_start();
		Tags::open( 'div', array( 'class' => 'test' ) );
		Tags::open( 'b' );
		echo 1;
		Tags::tag( 'hr' );
		Tags::close();
		Tags::closeAll();
		$contents = ob_get_contents();
		ob_clean();

		d( htmlspecialchars( $contents ) );

		$this->assertTrue( $contents === '<div class="test"><b>1<hr/></b></div>' );
	}

	public function testClose2() {
		ob_start();
		Tags::open( 'div', array( 'class' => 'test' ) );
		Tags::open( 'b' );
		echo 1;
		Tags::tag( 'hr' );
		Tags::close( 'b' );
		Tags::close( 'div' );
		$contents = ob_get_contents();
		ob_clean();

		d( htmlspecialchars( $contents ) );

		$this->assertTrue( $contents === '<div class="test"><b>1<hr/></b></div>' );
	}

	public function testStringParams() {
		ob_start();
		Tags::open( 'div', 'class="test"' );
		$contents = ob_get_contents();
		ob_clean();

		d( htmlspecialchars( $contents ) );

		$this->assertTrue( $contents === '<div class="test">' );
	}

	public function testEmptyTag() {
		ob_start();
		Tags::tag( 'div', null, 'aaa' );
		$contents = ob_get_contents();
		ob_clean();

		d( htmlspecialchars( $contents ) );

		$this->assertTrue( $contents === '<div>aaa</div>' );
	}

	public function testEmptyTag2() {
		ob_start();
		Tags::tag( 'div', 'class="test"', 'aaa' );
		$contents = ob_get_contents();
		ob_clean();

		d( htmlspecialchars( $contents ) );

		$this->assertTrue( $contents === '<div class="test">aaa</div>' );
	}

}