<?php

namespace VeutifyApp\Helper;

use VeutifyApp\Core as Core;

class Attribute implements Core\TemplateAttribute {
	
	protected $name = '';
	protected $value = '';
	
	final public function __construct ( $name , $value ) {
		$this->name = $name;
		$this->value = implode(' ', is_array($value) ? $value : (array) "{$value}" );
	}

	final public function __toString () {
		return is_int($this->name) ? $this->value : ( empty($this->value) && !is_numeric($this->value) ? $this->name : $this->name.'="'.$this->value.'"' );		
	}

}

class Attributes {

	protected $attributes = [];

	final public function __construct ( $args ) {
		foreach ( $args as $name => $value ) {
			$this->attributes[] = new Attribute( $name, $value );
		}
	}

	final public function __toString () {		
		return implode(' ', $this->attributes );
	}

}

