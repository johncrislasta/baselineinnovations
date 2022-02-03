<?php

namespace VeutifyApp\Layout;

use VeutifyApp\Core as Core;
use VeutifyApp\Controls as Controls;

class Wrapper extends Core\SimpleTemplate {

	public function getTemplate ( $args ) {		

		return sprintf( '<v-%s %s>%s</v-%s>', $this->name, $args['atts'], $args['content'], $this->name );

	}

}

class Template extends Core\SimpleTemplate {	

	public function getTemplate ( $args ) {		

		return sprintf( '<template %s>%s</template>', $args['atts'], $args['content'] );

	}

}

class Icon extends Wrapper {

}

class Container extends Wrapper {	
	
}

class Main extends Wrapper {	

}

class Col extends Wrapper {

}

class Menu extends Wrapper {

}

class Spacer extends Wrapper {

}


class Row extends Wrapper {

	public $columns = [];

	public function __construct ( $items = [] ) {
		parent::__construct();
		foreach ( $items as $item ) {			
			$this->columns [] = new Col( $item );
		}
	}	

	public function getTemplate ( $args ) {		

		return sprintf( '<v-row %s>%s</v-row>', $args['atts'], implode('', $this->columns )  );

	}

} 

class App extends Wrapper {	
	
	public $globalDialog = '';

	public function __construct ( $child = '', $dialog = true, $actions = '' ) {
		parent::__construct ( $child );
		if ( $dialog ) {
			$this->globalDialog = new GlobalDialog( ['actions' => $actions] );	
			$this->globalDialog->child->attr('width', 400 );
		}
		$this->attr('v-cloak');		
	}

	public function getTemplate ( $args ) {		

		return sprintf( '<v-app %s>%s%s</v-app>', $args['atts'], $args['content'], $this->globalDialog  );

	}

	public function models () {
		return Core\ModelCollection::jsData();
	}

}

class Card extends Wrapper {	
	
	public function __construct ( $name = '', $child = '') {

		parent::__construct( $child );

		if ( !empty($name) ) {
			$this->name = 'card-'.$name;
		}		

	}

}


class Dialog extends Wrapper {
	
	public $closeIcon;
	public $cardTitle;
	public $cardContent;
	public $cardActions;

	public function __construct ( $toggle, $actions, $model, $child, $title = '', $titleclass = '' ) {

		parent::__construct();
		
		$this->closeIcon = new Icon('mdi-close');
		$this->closeIcon->addClass('dialog-close-icon');

		$close = new Controls\Basic('button', $this->closeIcon );
		
		$close->attrs( [
			'icon',
			'absolute',
			'top',
			'right',
			'@click' => $model.' = false'
		] );

		$this->cardTitle = new Card( 'title', $title.$close );

		if (!empty( $titleclass ) ) {

			$this->cardTitle->addClass($titleclass);

		}

		$this->cardContent = new Card( 'text', $child );
		$this->cardActions = new Card( 'actions', $actions );

		if ( $toggle ) {
			$toggle->attr('v-bind','attrs');
			$toggle->attr('v-on','on');
		}

		$this->attr('v-model', $model);

		$template = new Template( $toggle );
		$template->attr('v-slot:activator', '{ on, attrs }');

		$this->child = $template . new Card('', implode('',[ $this->cardTitle, $this->cardContent, $this->cardActions ] ) );

	}

}

class GlobalDialog extends Core\SimpleTemplate {	

	public function __construct( $args ) {
		
		parent::__construct();

		$title_class = 'text-h5';				
		$actions = '';

		extract($args);

		$this->child = new Dialog( '', $actions, 'globalDialog', '{{ dialogOptions.content }}'  , '{{ dialogOptions.title }}', $title_class);
		$this->child->attr('content-class', 'global-dialog-modal');
	}

	public function getTemplate ( $args ) {		

		return (string) new Template( $args['content'] );

	}	

}


class HelperTrigger extends Template {

	public function __construct ( $args = [] ) {
		
		$position = 'absolute top right';
		$trigger = false;
		$helperTitle = '';
		$helperContent = '';
		$type = 'icon';
		
		extract($args);

		if ( !$trigger ) {
			$trigger = new Icon( 'mdi-help-circle-outline' );			
		}

		$button = new Controls\Basic('button', $trigger );
		$button->addClass('helper-icon');
		$button->attrs( [
			$type,			
			$position,
			'style' => 'top:0px;',			
			'@click' => implode(';', [ "dialogOptions.title = '".$helperTitle."'", "dialogOptions.content = '".$helperContent."'", 'globalDialog = true' ] )
		] );		
		
		parent::__construct( $button );		

	}

}

class ConditionalView extends Wrapper {

	public function __construct ( $content, $viewModel ) {
		parent::__construct( $content );
		$this->name = 'container';
		$this->bindView( $viewModel );
	}

	public function bindView ( $viewModel = '') {
		if ( !empty( $viewModel ) ) {
			$this->attr('v-show', "currentView == '{$viewModel}'");
		}
	}

}

class Toolbar extends Wrapper {
	
}

class Snackbar extends Core\SimpleTemplate {

	public $close = '';
	public $model = '';

	public function __construct( $content ) {
		parent::__construct( $content );
		
		$this->model = new Core\Model( '__states.'.$this->ref() );

		$this->close = new Template( new Controls\Basic('button', 'Close') );
		$this->close->attr('v-slot:action', '{ attrs }');
		$this->close->child->attrs([
			'v-bind' => 'attrs',
			'text',
			'@click' => $this->model.' = false'
		]);
		
		$this->attr('v-model', $this->model );

	}

	public function ref() {
		return 'snackbar'.$this->refID;
	}

	public function getTemplate ( $args ) {		

		return sprintf( '<v-%s %s>%s%s</v-%s>', $this->name, $args['atts'], $args['content'], $this->close, $this->name );

	}

}