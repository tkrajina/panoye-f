<?php

class DbIterator {

	const PAGES_INTERVAL = 4;

	private $sql;
	private $query;
	private $className;

	private $pageNo = -1;
	private $pageSize = -1;

	private $pagePropertyName = 'no';

	/** Koliko ima ukupno stranica. */
	private $pages = -1;

	/**
	 * Ne radi query odmah, nego tek kad se pozove bilo koja druga metoda.
	 */
	public function __construct( $sql, $className = 'AppObject' ) {
		$this->sql = $sql;
		$this->className = $className;
		$this->query();
	}

	/** Radi query, ako to jos nije napravio do sada. */
	private function query() {
		Db::open();
		$this->query = @mysql_query( $this->sql );
		if( ! $this->query ) {
			throw new AppException( $this->sql . ': ' . @mysql_error() );
		}
	}

	/**
	 * Vraća samo retke na $pageNo-toj stranici (ukoliko su stranice velicine $pageSize)
	 * <u>Napomena:</u> Mijenja SQL! Prva stranica je 0!
	 */
	public function paginate( $pageSize = -1 ) {
		$this->pageNo = (int) @$_GET[ $this->pagePropertyName ];
		if( $pageSize > 0 ) {
			$this->pageSize = (int) $pageSize;
		}
		else {
			$this->pageSize = Application::ITEMS_PER_PAGE;
		}

		$sql = $this->sql;
		$size = $this->size();

		$this->pages = 1 + ceil( $size / $this->pageSize );

		// Ako je query vec izvrsen -- resetira:
		if( $this->query ) {
			$this->query = null;
		}

		$parts = spliti( 'limit ', $sql );
		$this->sql = @$parts[ 0 ] . ' limit ' . ( $this->pageSize * $this->pageNo ) . ', ' . $this->pageSize;

		$this->query();
	}

	/**
	 * Ako je query {@link #paginate()} -- vraca spisak svih stranica s označenom onom na kojoj smo
	 * trenutno.
	 */
	public function printPageIndex( $prefix = '', $sufix = '' ) {
		global $queryStringPath;
		global $queryStringParameters;
		global $queryStringExtension;

		$pagePath = $queryStringPath;
		$params = $queryStringParameters;

		$start = $this->pageNo - self::PAGES_INTERVAL;
		$end = $this->pageNo + self::PAGES_INTERVAL + 1;
		if( $start < 0 ) {
			$start = 0;
		}
		if( $end >= $this->pages ) {
			$end = $this->pages - 1;
		}

		if( $end <= 1 ) {
			return;
		}

		$result = array();

		if( $start > 0 ) {
			$result[] = htmlLink( '&lt;&lt; &nbsp;', $_GET[ 'page' ], $_GET[ 'arg' ] );
		}

		for( $i = $start; $i < $end; $i++ ) {
			$pageNo = $i + 1;
			if( $i != $this->pageNo ) {
				if( $i > 0 ) {
					$params[ $this->pagePropertyName ] = $i;
				}
				else {
					unset( $params[ $this->pagePropertyName ] );
				}
				$args = array();
				if( $pageNo > 1 ) {
					$args[ $this->pagePropertyName ] = $pageNo - 1;
				}
				$link = url( $_GET[ 'page' ], $_GET[ 'arg' ], $args );
				$resultLink = '<a href="' . $link . '">' . $pageNo . '</a>';
			}
			else {
				$resultLink = $pageNo;
			}

			$size = ( 1 + 0.8 * ( 1 / ( 1 + abs( $i - $this->pageNo ) ) ) );
			$size = ( (int) ( $size * 100 ) ) / 100;
			$resultLink = '<span style="font-size: ' . $size . 'em">' . $resultLink . '</span>';

			$result[] = $resultLink;
		}

		if( $end < $this->pages - 1 ) {
			$result[] = htmlLink( '&nbsp; &gt;&gt;', $_GET[ 'page' ], $_GET[ 'arg' ], array( $this->pagePropertyName => $this->pages - 2 ) );
		}

		if( sizeof( $result ) > 0 ) {
			echo $prefix, implode( ' ', $result ), $sufix;
		}
	}

	public function size() {
		return (int) @mysql_num_rows( $this->query );
	}

	/** Vraca null Kad je kraj. */
	public function next() {
		$array = mysql_fetch_array( $this->query );
		if( $array ) {
			$cn = $this->className;
			$result = new $cn();
			/*
			 * Don't change this line to:
			 * $result->setSqlProperties( $array );
			 * ..because the default object is Object not AppObject (Object doesn't
			 * contain the setSqlProperties() method)
			 */
			if( method_exists( $result, 'addSqlProperties' ) ) {
				$result->addSqlProperties( $array );
			}
			else {
				$result->addProperties( DbNames::dbToFramework( $array ) );
			}
			return $result;
		}
		return null;
	}

	public function nextArray() {
		$array = mysql_fetch_array( $this->query );
		if( $array ) {
			return $array;
		}
		return null;
	}

	public function jump( $i ) {
		$size = @mysql_num_rows( $this->query );

		if( $size == 0 ) {
			return false;
		}

		if( $i >= $size ) {
			$i = $size - 1;
		}

		if( ! @mysql_data_seek( $this->query, $i ) ) {
			return false;
		}
		else {
			return true;
		}
	}

	/** Skače opet na prvi redak. */
	public function reset() {
		$this->jump( 0 );
	}

	/** All objects in one array. */
	public function all() {
		$result = array();
		while( $o = $this->next() ) {
			$result[] = $o;
		}
		return $result;
	}

}
