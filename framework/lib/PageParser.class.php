<?php

/*

panorama-image.p_12.html -> panorama/image argument p => 12
panorama-image.p_12.a_1.html -> panorama/image argument p => 12, a => 1
panorama-image.12.html -> panorama/image argument string je 12
panorama-image.html -> panorama/image nema argumenata

Stranica je do prve tocke
ako je nakon toga ekstenzija => nema argumenata
Inace su argumenti podijeljeni stockom.

 */

global $queryStringPath;
global $queryStringParameters;
global $queryStringExtension;
global $pageClassFile;
global $queryString;

class PageParser {

	/** Npr. User/Logout */
	private $queryString;

	private $pageClass;
	private $arguments = array();
	private $page;

	public function __construct() {
		$this->parse();
	}

	private function parse() {
		$paths = $this->pagePaths();

		$pageClassFile = @$paths[ @strtolower( @$_GET[ 'page' ] ) ];
//		ddie( $paths );
		if( ! $pageClassFile ) {
			throw new AppException( 'Page not found: ' . $_GET[ 'page' ] . '(' . $pageClassFile . ')' );
		}
		$class = str_replace( '.class.php', '', Files::getFileName( $pageClassFile ) );
		if( ! $class ) {
			throw new AppException( 'Nema stranice 2' );
		}

		// Ako postoji include.php u istom direktoriju onda ce ga includati
		$includePath = Files::getPath( $pageClassFile );
		$this->checkIncludePhpFiles( $includePath );

		require_once $pageClassFile;
		$this->page = new $class();
		$this->page->getParameters = $_GET;
		$this->page->postParameters = $this->getPostParameters();
		$this->page->pageClassFile = $pageClassFile;

		return;
	}

	/**
	 * Checks the existence of files include.php in parent directories
	 * and includes them.
	 *
	 * For example, if your page is app/pages/admin/article/EditPage.class.php
	 * and there is a file app/pages/admin/include.php it will be
	 * autimatically included when executing the EditPage.class.php
	 */
	private function checkIncludePhpFiles( $path ) {
		$parts = explode( '/', $path );
		$currentPath = '';
		foreach( $parts as $part ) {
			$currentPath .= $part . '/';
			$includeFile = $currentPath . 'include.php';
			if( is_file( $includeFile ) ) {
				require_once( $includeFile );
			}
		}
	}

	function getPostParameters() {
		$result = array();
		// 1. Koja sve polja:
		$fields = @$_POST[ '_fields' ];
		if( ! $fields ) {
			return $result;
		}
		$fields = explode( ',', $fields );
		foreach( $fields as $key ) {
			$parts = split( ':', $key );
			if( sizeof( $parts ) != 2 ) {
				error( 'Neispravno post polje: ' . $key );
			}
			else {
				global $queryString;
				$hash = md5( Application::SECRET_APP_KEY . $queryString . @$parts[ 1 ] );
				if( $hash == @$parts[ 0 ] ) {
					$result[ $parts[ 1 ] ] = $_POST[ $parts[ 1 ] ];
				}
				else {
					error( 'Ne valja hash od: ' . $key );
				}
			}
		}
		return $result;
	}

	private function pagePaths() {
		$pagePaths = @unserialize( Cache::load( 'application', 'pages' ) );
		if( ! is_array( $pagePaths ) ) {
			$pagePaths = $this->_pagePaths();
			Cache::save( 'application', 'pages', serialize( $pagePaths ) );
		}
		return $pagePaths;
	}

	private function _pagePaths( $path = null ) {
		$result = array();
		if( $path === null ) {
			$path = APP . 'pages/';
		}
		$files = Files::getAll( $path );
		foreach( $files as $file ) {
			$file = $path . $file;
			if( is_file( $file ) ) {
				if( preg_match( '/^.*Page.class.php$/', $file ) ) {
					$key = str_replace( APP . 'pages/', '', $file );
					$key = str_replace( 'Page.class.php', '', $key );
					$key = str_replace( '/', '-', $key );
					$result[ strtolower( $key ) ] = $file;
				}
			}
			else if( is_dir( $file ) ) {
				$result = array_merge( $result, $this->_pagePaths( $file . '/' ) );
			}
		}
		return $result;
	}

	public function getPage() {
		return $this->page;
	}

}
