<?php

namespace VeutifyApp\HTML;

use VeutifyApp\Core as Core;

class Common extends Core\SimpleTemplate {

	public function getTemplate ( $args ) {		
		return sprintf( '<%s %s>%s</%s>', $this->name, $args['atts'], $args['content'], $this->name );
	}

}

class Div extends Core\SimpleTemplate {
	public function getTemplate ( $args ) {
		return sprintf( '<div %s>%s</div>', $args['atts'], $args['content'] );
	}
}

class Text extends Div {

	public function __construct ( $content, $type = 'h1' ) {
		parent::__construct( $content );
		$this->addClass('text-'.$type);
		/*
		h1
		h2
		h3
		h4
		h5
		h6
		subtitle-1
		subtitle-2
		body-1
		body-2
		button
		caption
		overline
		*/
	}

}

class Image extends Common {

	public function __construct( $src = '' ) {
		parent::__construct();
		$this->attr('src', $src );
	}

	public function getTemplate ( $args ) {		
		return sprintf( '<img %s>', $args['atts'] );
	}

}

class Link extends Common {

	public function __construct( $label = '', $href = '' ) {
		parent::__construct( $label );
		$this->name = 'a';
		$this->attr('href', $href );
	}

}
