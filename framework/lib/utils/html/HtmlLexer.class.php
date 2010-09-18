<?php

class HtmlLexer {

	const STATUS_TEXT = 'text'; // ...
	const STATUS_TAG_START = 'tag_start'; // <
	const STATUS_TAG_NAME = 'tag_name'; // ...
	const STATUS_TAG_SPACE = 'tag_space'; // ...
	const STATUS_TAG_QUOTE_START = 'tag_quote_start'; // ' or "
	const STATUS_TAG_QUOTE_END = 'tag_quote_end'; // ' or "
	const STATUS_TAG_EQUALS = 'tag_equals'; // =
	const STATUS_TAG_PROPERTY = 'tag_property'; // ...
	const STATUS_TAG_PROPERTY_VALUE = 'tag_property_value'; // ...
	const STATUS_TAG_END = 'tag_end'; // >
	const STATUS_TAG_SLASH_START = 'tag_slash_start'; // /
	const STATUS_TAG_SLASH_END = 'tag_slash_end'; // /

	const STATUS_TAG_ERROR = 'tag_error';

	const LETTERS = 'qwertzuiopasdfghjklyxcvbnm';

	private $elements = array();

	private $content;

	private $currentTag;

	private $currentStatus;

	private $currentTagName;

	private $currentProperty;

	private $currentPropertyValue;

	private $currentProperties;

	private $currentTagType;

	private $currentText = '';

	public function __construct( $string ) {
		$this->content = $string;
		$this->currentStatus = self::STATUS_TEXT;
	}

	public function tokenize() {
		$c = ''; // Sljedeci znak

		$openedQuote = '';

		$l = strlen( $this->content );
		for( $i = 0; $i < $l; $i++ ) {
			$c = $this->content[ $i ];
			$next = isset( $this->content[ $i + 1 ] ) ? $this->content[ $i + 1 ] : '';

			if( $this->currentStatus == self::STATUS_TEXT ) {
				// Ako je tekst dopusteno je bilo sta, ako je < onda je tag:
				if( $c == '<' && ctype_alpha( $next ) ) {
					$this->currentStatus = self::STATUS_TAG_START;
					$this->startTag();
				}
			}
			else if( $this->currentStatus == self::STATUS_TAG_START ) {
				// Ako je tag start, onda je dopusteno ime ili / (zatvoreni tag)
				if( ctype_alnum( $c ) ) {
					$this->endText();
					$this->currentStatus = self::STATUS_TAG_NAME;
					$this->addTagNameChar( $c );
				}
				else if( $c == '/' ) {
					$this->currentStatus = self::STATUS_TAG_SLASH_START;
					$this->setTagType( HtmlTag::TYPE_CLOSE );
				}
				else {
					$this->debug( $i );
					$this->currentStatus = self::STATUS_TAG_ERROR;
				}
			}
			else if ( $this->currentStatus == self::STATUS_TAG_NAME ) {
				if( ctype_alnum( $c ) || $c == ':' || $c == '-' ) {
					// Moze biti ili alfanumeric
					$this->currentStatus = self::STATUS_TAG_NAME;
					$this->addTagNameChar( $c );
				}
				else if( $c == ' ' || $c == '	' ) {
					// Razmak ili tab
					$this->currentStatus = self::STATUS_TAG_SPACE;
				}
				else if ( $c == '/' ) {
					$this->currentStatus = self::STATUS_TAG_SLASH_END;
					$this->setTagType( HtmlTag::TYPE_EMPTY );
				}
				else if( $c == '>' ) {
					$this->currentStatus = self::STATUS_TAG_END;
					$this->endTag();
				}
				else {
					$this->debug( $i );
					$this->currentStatus = self::STATUS_TAG_ERROR;
				}
			}
			else if( $this->currentStatus == self::STATUS_TAG_SPACE ) {
				if( $c == ' ' || $c == '	' ) {
					$this->currentStatus = self::STATUS_TAG_SPACE;
				}
				else if( ctype_alnum( $c ) ) {
					$this->currentStatus = self::STATUS_TAG_PROPERTY;
					$this->startProperty();
					$this->addPropertyChar( $c );
				}
				else if( $c == '/' ) {
					$this->currentStatus = self::STATUS_TAG_SLASH_END;
					$this->setTagType( HtmlTag::TYPE_EMPTY );
				}
				else if( $c == '>' ) {
					$this->currentStatus = self::STATUS_TAG_END;
					$this->endTag();
				}
				else {
					$this->debug( $i );
					$this->currentStatus = self::STATUS_TAG_ERROR;
				}
			}
			else if( $this->currentStatus == self::STATUS_TAG_QUOTE_START ) {
				if( $c == $openedQuote ) {
					$this->currentStatus = self::STATUS_TAG_QUOTE_END;
					$this->endProperty();
				}
				else {
					$this->currentStatus = self::STATUS_TAG_PROPERTY_VALUE;
					$this->addPropertyValueChar( $c );
				}
			}
			else if( $this->currentStatus == self::STATUS_TAG_QUOTE_END ) {
				if( $c == ' ' || $c == '	' ) {
					$this->currentStatus = self::STATUS_TAG_SPACE;
				}
				else if( $c == '/' ) {
					$this->currentStatus = self::STATUS_TAG_SLASH_END;
					$this->setTagType( HtmlTag::TYPE_EMPTY );
				}
				else if( $c == '>' ) {
					$this->currentStatus = self::STATUS_TAG_END;
					$this->endTag();
				}
				else {
					$this->debug( $i );
					$this->currentStatus = self::STATUS_TAG_ERROR;
				}
			}
			else if( $this->currentStatus == self::STATUS_TAG_EQUALS ) {
				if( $c == '"' || $c == "'" ) {
					$openedQuote = $c;
					$this->currentStatus = self::STATUS_TAG_QUOTE_START;
				}
				else {
					$this->debug( $i );
					$this->currentStatus = self::STATUS_TAG_ERROR;
				}
			}
			else if( $this->currentStatus == self::STATUS_TAG_PROPERTY ) {
				if( ctype_alnum( $c ) || $c == ':' || $c == '-' ) {
					$this->currentStatus = self::STATUS_TAG_PROPERTY;
					$this->addPropertyChar( $c );
				}
				else if( $c == '=' ) {
					$this->currentStatus = self::STATUS_TAG_EQUALS;
				}
				else {
					$this->debug( $i );
					$this->currentStatus = self::STATUS_TAG_ERROR;
				}
			}
			else if( $this->currentStatus == self::STATUS_TAG_PROPERTY_VALUE ) {
				if( $c == $openedQuote ) {
					$this->currentStatus = self::STATUS_TAG_QUOTE_END;
					$this->endProperty();
				}
				else {
					$this->addPropertyValueChar( $c );
				}
			}
			else if( $this->currentStatus == self::STATUS_TAG_END ) {
				if( $c == '<' ) {
					$this->currentStatus = self::STATUS_TAG_START;
					$this->startTag();
				}
				else {
					$this->currentStatus = self::STATUS_TEXT;
				}
			}
			else if( $this->currentStatus == self::STATUS_TAG_SLASH_START ) {
				if( $c == ' ' || $c == '	' ) {
					$this->currentStatus = self::STATUS_TAG_SPACE;
				}
				else if( ctype_alnum( $c ) ) {
					$this->currentStatus = self::STATUS_TAG_NAME;
					$this->addTagNameChar( $c );
				}
				else {
					$this->debug( $i );
					$this->currentStatus = self::STATUS_TAG_ERROR;
				}
			}
			else if( $this->currentStatus == self::STATUS_TAG_SLASH_END ) {
				if( $c == '>' ) {
					$this->currentStatus = self::STATUS_TAG_END;
					$this->endTag();
				}
				else {
					$this->debug( $i );
					$this->currentStatus = self::STATUS_TAG_ERROR;
				}
			}
			else if( $this->currentStatus == self::STATUS_TAG_ERROR ) {
				if( $c == '>' ) {
					$this->currentStatus = self::STATUS_TAG_END;
					$this->endTag();
				}
			}

//			echo $this->currentStatus . ':' . $c . '<br/>';

			if( $this->currentStatus == self::STATUS_TEXT ) {
				$this->currentText .= $c;
			}

//			if( $this->currentText ) {
//				echo $this->currentText . '<br/>';
//			}
		}

		if( $this->currentStatus != self::STATUS_TEXT && $this->currentStatus != self::STATUS_TAG_END ) {
			$this->debug( $i );
		}

		if( $this->currentText ) {
			$this->endText();
		}

		return $this->elements;
	}

	private function debug( $position ) {
		$c = @$this->content[ $position - 1 ];
		$c1 = @$this->content[ $position ];
		$before = @substr( $this->content, $position - 10, 10 );
		$after = @substr( $this->content, $position, 10 );
		echo 'Unexpected ', $c1, ' after ', $c, ': ', $before, '<b>', $c, '</b>', $after, ' [', $this->currentStatus, ']';
	}

	private function startTag() {
		$this->currentTagName = '';
		$this->currentProperties = array();
		$this->currentTagType = HtmlTag::TYPE_OPEN;
	}

	private function endTag() {
//		echo 'TAG: ', $this->currentTagName, ': ';
//		print_r( $this->currentProperties );
//		echo '<br/>';
		$tag = new HtmlTag( $this->currentTagName, $this->currentProperties, $this->currentTagType );
		$this->elements[] = $tag;
		$this->currentProperties = null;
		$this->currentTagName = null;
	}

	private function startProperty() {
		$this->currentProperty = '';
		$this->currentPropertyValue = '';
	}

	private function addPropertyChar( $c ) {
		$this->currentProperty .= $c;
	}

	private function addPropertyValueChar( $c ) {
		$this->currentPropertyValue .= $c;
	}

	private function endProperty() {
		$this->currentProperties[ $this->currentProperty ] = $this->currentPropertyValue;
		$this->currentProperty = null;
		$this->currentPropertyValue = null;
	}

	private function addTagNameChar( $c ) {
		$this->currentTagName .= $c;
	}

	private function endText() {
		if( $this->currentText ) {
			$this->elements[] = $this->currentText;
		}
		$this->currentText = '';
	}

	private function setTagType( $t ) {
		$this->currentTagType = $t;
	}

}