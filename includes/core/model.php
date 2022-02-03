<?php

namespace VeutifyApp\Core;

class Model implements ModelBase{	
	
	protected $parent = 'model';
	protected $name;
	public $child;

	public function __construct( $name, $value = '' ) {
		
		$this->name = $name;
		$this->child = $value;
		if ( self::same( $value ) ) {
			$this->child->parent = $name;			
		}

		ModelCollection::register ( $this );

	}
	
	public function val() {
		if ( self::same( $this->child ) ) {
			return $this->child->val();
		}
		return $this->child;
	}

	public static function same ( $object ) {
		return is_a( $object, get_called_class() );
	}

	public function __toString() {
				
		if ( self::same( $this->child ) ) {
			$path = [ $this->parent, $this->child ];
		} else {
			$path = [ $this->parent, $this->name ];
		}

		return implode( '.', $path );

	}


}

class ModelCollection {

	public static $models = [];
	public static $data = [];
	public static $rules = [];	

	public static function register ( Model $model ) {
		self::$models[] = $model;
	}

	public static function set ( $name, $data, $rule = false ) {
		if ( $rule ) self::$rules[ $name ] = $data;
		else self::$data[$name] = $data;
	}

	public static function toArray() {
				
		foreach (self::$models as $model) {
			if ( strpos( $model, 'model.' ) === 0 ) {
				
				$vars = explode( '.', $model );
				array_shift($vars); //Remove model. key

				//Convert to array
				$name = array_shift($vars);

				if ( count($vars) == 0 ) {
					self::set( $name, $model->val() );					
				} else {
					self::set( $name, self::arr($vars, $model) );					
				}

			}
			
		}

		return self::$data;

	}	

	private static function arr ( $arr, $model ) {
		$name = array_shift( $arr );
		return count($arr) == 0 ? [ $name => $model->val() ] : [ $name => self::arr($arr, $model) ];		
	}

	public static function jsData () {
				
		$models = json_encode(self::toArray());
		$entries = [];

		foreach (self::$rules as $name => $value) {
			if ( is_array($value) )	{
				$rentries = [];
				foreach ($value as $cb) {
					$rentries[] = $cb;
				}
				$entries[] = "\"{$name}\": [".implode(', ', $rentries )."]";
			} else if ( !empty($value) ) $entries[] = "\"{$name}\":".$value;			
		}

		$data = sprintf ( '{%s}', implode( ', ', $entries ) );

		return sprintf('{...%s, "__rules" : %s}', $models, $data );

	}

}
