<?php

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
		global $application;

		$paths = $this->pagePaths();

		$pageClassFile = @$paths[ @strtolower( @$_GET[ 'page' ] ) ];
		if( ! $pageClassFile ) {
			$catchAllPage = $application->getCatchAllPage();
			if( $catchAllPage ) {
				$pageClassFile = @$paths[ @strtolower( $catchAllPage ) ];
				Logs::debug( 'Catch all page ', $catchAllPage, ' -> ', $pageClassFile );
			}
		}

		if( ! $pageClassFile ) {
			Logs::error( 'No page class file found for ', @$_GET[ 'page' ] );
			throw new AppException( 'Page not found for ' . $_GET[ 'page' ] );
		}



		$class = str_replace( '.class.php', '', Files::getFileName( $pageClassFile ) );
		if( ! $class ) {
			Logs::error( 'No class file found for ', $pageClassFile );
			throw new AppException( 'Page not found' );
		}

		// Ako postoji include.php u istom direktoriju onda ce ga includati
		$includePath = Files::getPath( $pageClassFile );
		$this->checkIncludePhpFiles( $includePath );

		Logs::info( 'Page file:', $pageClassFile );

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
			$parts = explode( ':', $key );
			if( sizeof( $parts ) != 2 ) {
				Logs::error( 'Neispravno post polje: ' . $key );
			}
			else {
				global $queryString;
				$hash = md5( Application::SECRET_APP_KEY . $queryString . @$parts[ 1 ] );
				if( $hash == @$parts[ 0 ] ) {
					$result[ $parts[ 1 ] ] = @$_POST[ @$parts[ 1 ] ];
				}
				else {
					Logs::error( 'Ne valja hash od: ' . $key );
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
