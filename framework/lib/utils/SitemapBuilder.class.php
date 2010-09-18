<?

class SitemapBuilder {

	const HOURLY = 'hourly';
	const DAILY = 'daily';
	const WEEKLY = 'weekly';
	const MONTHLY = 'monthly';
	const YEARLY = 'yearly';
	const NEVER = 'never';

	private $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

	public function __construct() {
		$this->tagStart( 'urlset', array( 'xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9' ) );
	}

	public function getResult() {
		$this->tagEnd( 'urlset' );
		return $this->result;
	}

	public function add( $location, $lastMod, $changeFreq = null, $priority = 0.1 ) {
		if( ! $changeFreq ) {
			$changeFreq = self::YEARLY;
		}
		$this->tagStart( 'url' );
		$this->tag( 'loc', $location );
		$this->tag( 'lastmod', $lastMod );
		$this->tag( 'changefreq', $changeFreq );
		$this->tag( 'priority', $priority );
		$this->tagEnd( 'url' );
	}

	private function tag( $tag, $content = '', $tagParams = array() ) {
		$params = $this->xmlParams( $tagParams );
		if( strlen( $content ) == 0 ) {
				$this->out( '<' . $tag . $params . '/>' );
		}
		else {
				$this->out( '<' . $tag . $params . '>' . $this->xmlEscape( $content ) . '</' . $tag . '>' );
		}
	}

	private function tagStart( $tag, $params = array() ) {
		$params = $this->xmlParams( $params );
		$this->out( '<' . $tag . $params . '>' );
	}

	private function tagEnd( $tag ) {
		$this->out( '</' . $tag . '>' );
	}

	private function xmlParams( $array ) {
		if( ! is_array( $array ) ) {
			return '';
		}
		$params = array();
		foreach( $array as $k => $v ) {
			$params[] = $k . '="' . $this->xmlEscape( $v ) . '"';
		}
		$result = trim( implode( ' ', $params ) );
		if( strlen( $result ) > 0 ) {
			return ' ' . $result;
		}
		return '';
	}

	private function xmlEscape( $string ) {
		$string = str_replace( '&', '&amp;', $string );
		$string = str_replace( "'", '&apos;', $string );
		$string = str_replace( '"', '&quot;', $string );
		$string = str_replace( '>', '&gt;', $string );
		$string = str_replace( '<', '&lt;', $string );
		return $string;
	}

	private function out( $string ) {
		$this->result .= $string;
	}

}

/*

$builder = new SitemapBuilder();

for( $i = 0; $i < 10; $i++ ) {
	$builder->add(
		'http://www.test.com/' . $i . '.html',
		'2007-12-31',
		SitemapBuilder::YEARLY,
		0.3
	);
}

echo $builder->getResult();

*/
