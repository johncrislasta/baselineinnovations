<?php

namespace VeutifyApp\Core;

use VeutifyApp\Helper as Helper;

abstract class SimpleTemplate implements TemplateBase {
	
	public static $count = 0; //Global Instance Counter

	protected $name;
	protected $attributes = [];	
	protected $classes = [];	
	public $child = false;
	protected $refID = 0;

	public function __construct( $child = '' ) {				
		$name = explode('\\', get_class($this) );
		$this->child = $child;
		$this->name = strtolower($name[count($name)-1]);
		self::$count++;
		$this->refID = self::$count;
	}

	final public function __toString() {
		if ( !empty($this->name) ) {					

			$this->addClass( 'vq-el vq-el-'.$this->refID);
			$this->addClass( 'vq-el-'.$this->name );

			if ( !empty($this->classes) ) {
				$this->attr('class', $this->classes);
			}

			$args = [
			    'idx' => $this->refID,				
				'atts' => new Helper\Attributes( $this->attributes ),
				'name' => $this->name,
				'content' => "{$this->child}"
			];

			$template = $this->getTemplate( $args );

			return empty($template) ? $this->loadfile( $args ) : $template;
		} else {
			throw new \ErrorException( sprintf ('The %s::__construct() has no name, call parent::__construct() within class construct instead.', get_class($this) ) , 0,  E_ERROR );
		}
	}
	
	final public function attr( $name, $value = '' ) {
		$this->attributes[$name] = $value;
	}

	final public function attrs ( $values = [] ) {
		$this->attributes = array_merge( $this->attributes, $values );
	}

	final public function removeAttr ( $name ) {
		if ( isset($this->attributes[$name])) {
			unset($this->attributes[$name]);
		}
	}

	protected function loadfile ( $args ) {
		
		ob_start();
		
		extract( $args );

		include "templates/partials/{$this->name}.php";

		return ob_get_clean();

	}

	public function addClass ( $class ) {
		$this->classes[] = $class;
	}

	public function removeClass ( $class ) {
		if ( ($key = array_search($class, $this->classes) ) !== false) {
		    unset($this->classes[$key]);
		}
	}

}



