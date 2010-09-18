<?php

class HtmlTag {

	const TYPE_OPEN = 'open'; // <p>
	const TYPE_CLOSE = 'close'; // </p>
	const TYPE_EMPTY = 'empty'; // <hr/>

	private $name;

	private $properties = array();

	private $type;

	public function __construct( $name, $properties = null, $type ) {
		$this->name = $name;
		if( is_array( $properties ) ) {
			$this->properties = $properties;
		}
		else {
			$this->properties = array();
		}
		$this->type = $type;
	}

	public function getName() {
		return $this->name;
	}

	public function getProperties() {
		return $this->properties;
	}

	public function getProperty( $property ) {
		return $this->properties[ $property ];
	}

	public function getType() {
		return $this->type;
	}

	public function isOpen() {
		return $this->type == self::TYPE_OPEN;
	}

	public function isClose() {
		return $this->type == self::TYPE_CLOSE;
	}

	public function isEmpty() {
		return $this->type == self::TYPE_EMPTY;
	}

	public function __toString() {
		return $this->getName() . '[' . var_export( $this->getProperties(), true ) . ']{' . $this->getType() . '}';
	}

}