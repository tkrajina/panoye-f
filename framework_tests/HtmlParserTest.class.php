<?php

// TODO
class HtmlParserTest extends AppTest {

    public function test1() {
		$s = 'jkljkljkl<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">dsjkfljdslkf<head><meta http-equiv="content-type" content="text/html; charset=utf-8" /><link rel="alternate" type="application/rss+xml" title="Opera Press Releases RSS feed" href="http://www.opera.com/press/rss/" /><meta name="description" content="Opera Mini gives you the full Web experience on your mobile phone." /><meta name="keywords" content="Opera, mobile internet, opera mini, mini opera, mobile web, opera browser, opera mobile, mobile browser, operamini, mobile sites, mobile sites, opera mini download, download opera mini, mobile phone internet, internet for mobile, browser for mobile, cell phone browser" /><link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" /><script src="/js/jquery/jquery.js" type="text/javascript"></script><script src="/js/plugins/lightbox/jquery.lightbox-0.5.pack.js" type="text/javascript"></script><script src="/js/plugins/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script><script src="/js/newsticker.js" type="text/javascript"></script><script type="text/javascript">dsjkfdskjlf		<li><a href="/business/contact/">Contact us</a></li>';

		$p = new HtmlLexer( $s );

		$p = $p->tokenize();

		d( $p );

		$this->assertTrue( sizeof( $p ) == 22 );
    }

    public function test2() {
		$s = 'jkljkljkl<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">dsjkfljdslkf<head><meta http-equiv="content-type" content="text/html; charset=utf-8" /><link rel="alternate" type="application/rss+xml" title="Opera Press Releases RSS feed" href="http://www.opera.com/press/rss/" /><meta name="description" content="Opera Mini gives you the full Web experience on your mobile phone." /><meta name="keywords" content="Opera, mobile internet, opera mini, mini opera, mobile web, opera browser, opera mobile, mobile browser, operamini, mobile sites, mobile sites, opera mini download, download opera mini, mobile phone internet, internet for mobile, browser for mobile, cell phone browser" /><link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" /><script src="/js/jquery/jquery.js" type="text/javascript"></script><script src="/js/plugins/lightbox/jquery.lightbox-0.5.pack.js" type="text/javascript"></script><script src="/js/plugins/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script><script src="/js/newsticker.js" type="text/javascript"></script><script type="text/javascript">dsjkfdskjlf		<li><a href="/business/contact/">Contact us</a></li>';

		$p = new HtmlLexer( $s );

		$p = $p->tokenize();

		d( get_class( @$p[ 1 ] ) );

		$this->assertTrue( get_class( @$p[ 1 ] ) == 'HtmlTag' );
		$this->assertTrue( is_string( @$p[ 2 ] ) );
		$this->assertTrue( @$p[ 2 ] == 'dsjkfljdslkf' );
		$this->assertTrue( @$p[ 1 ]->getProperty( 'xmlns' ) == 'http://www.w3.org/1999/xhtml' );
		$this->assertTrue( @$p[ 1 ]->getProperty( 'lang' ) == 'en' );
    }

}