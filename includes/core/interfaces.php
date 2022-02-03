<?php

namespace VeutifyApp\Core;

interface TemplateBase {

	public function getTemplate( $args );	

}

interface TemplateAttribute {

}

interface ModelBase {

	public function val();

}
