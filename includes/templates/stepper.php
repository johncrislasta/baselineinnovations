<?php

namespace VeutifyApp\Stepper;

use VeutifyApp\Core as Core;

class Step {

	public $tab;
	public $content;	
	public $step = 0;

	public function __construct ( $label, $content ) {
		
		$this->tab = new Tab ( $label );
		$this->content = new Content( $content );

	}

}

class Stepper extends Core\SimpleTemplate {	

	protected $steps = [];
	protected $divider = '<v-divider></v-divider>';
	protected $header = true;
	protected $body = true;

	public $model = '';


	public function getTemplate ( $args ) {				
		
		$tabs = [];
		$contents = [];

		$i = 0;

		foreach ( $this->steps as $step ) {			
			
			$i++;
			$step->tab->attr('step', $i);
			$step->content->attr('step', $i);

			$tabs[] = $step->tab;
			$contents[] = $step->content;

		}

		return sprintf( '<v-stepper %s>%s</v-stepper>', $args['atts'], implode('', [ $this->header ? new Header( implode( $this->divider, $tabs ) ) : '', $this->body ? new Items( implode('', $contents ) ) : '' ] ) );

	}

	public function addStep ( Step $step ) {
		$this->steps[] = $step;
	}

	public function setDivider ( $divider ) {
		$this->divider = $divider;
	}

	public function toggleHeader () {
		$this->header = !$this->header;
	}
	public function toggleBody () {
		$this->body = !$this->body;
	}
}

class Wrapper extends Core\SimpleTemplate {

	public function getTemplate ( $args ) {		

		return sprintf( '<v-stepper-%s %s>%s</v-stepper-%s>', $this->name, $args['atts'], $args['content'], $this->name );

	}

}

class Tab extends Core\SimpleTemplate {

	public function getTemplate ( $args ) {		

		return sprintf( '<v-stepper-step %s>%s</v-stepper-step>', $args['atts'], $args['content'] );

	}

	public function condition ( $condition ) {
		$this->attr( ':complete', $condition );
	}

}


class Header extends Wrapper {

}

class Items extends Wrapper {

}

class Content extends Wrapper {

}

