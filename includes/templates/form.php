<?php

namespace VeutifyApp\Form;

use VeutifyApp\Core as Core;

class Create extends Core\SimpleTemplate {

	public $action = '';

	public function __construct( $content, $action = '' ) {

		parent::__construct($content);

		$this->attrs( [
				'lazy-validation',
				'ref' => $this->ref()				
			] );
		
		$this->action = $action;

	}

	public function ref () {
		return 'form'.$this->refID;
	}

	public function getTemplate ( $args ) {

		return sprintf( '<v-form %s>%s%s</v-form>', $args['atts'], $args['content'], $this->action );

	}

	public function bindView ( $view = '') {
		$view = empty($view) ? $this->ref() : $view;
		$this->attr('v-show', "currentView == '{$view}'");
	}
	
}

class Validation extends Core\SimpleTemplate {

	protected $options = [];

	public function __construct( $name, $options = [], $inherit = [] ) {
		parent::__construct();
		$this->name = $name;
		$this->options = (array) $options;

		Core\ModelCollection::set ( $name, $this->factory( $inherit ), true );
	}

	protected function factory ( $inherit ) {
				
		$name = $this->name;
		$rules = [];
		$regex = '';

		extract($this->options);

		$inherit = (array) $inherit;
		$ruleSet = [];

		foreach ( $inherit as $rule ) {
			switch ( $rule ) {
				case 'required':				
					$ruleSet[] = sprintf( "v => !!v || '%s is required'", $name );								
				break;
				case 'email':				
					$ruleSet[] = sprintf( "v => /.+@.+\..+/.test(v) || '%s must be valid'", $name );								
				break;
				case 'phone':
					if ( !empty($regex) ) $ruleSet[] = sprintf( "v => /%s/.test(v) || '%s must be valid phone number'", $regex, $name );								
				break;
			}			 
		}

		return array_merge($ruleSet, $rules);

	}

	public function getTemplate ( $args ) {
		return "{$this->name}";
	}

}
