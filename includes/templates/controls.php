<?php

namespace VeutifyApp\Controls;

use VeutifyApp\Core as Core;
use VeutifyApp\Layout as Layout;
use VeutifyApp\HTML as HTML;

class Basic extends Core\SimpleTemplate {	
	
	protected $options;

	public function __construct( $type, $content, $args = [] ) {

		parent::__construct();

		$this->name = $type;
		
		$this->child = $content;			

		$this->options = $args;

	}	

	public function getTemplate ( $args ) {

		$template = '';						

		switch ($this->name) {
			case 'button':
				$template = '<v-btn %s>%s</v-btn>';
			break;
			case 'button-group':
				$template = '<v-btn-toggle %s>%s</v-btn-toggle>';
			break;
			case 'input':
				$template = '<v-text-field %s>%s</v-text-field>';
			break;
			case 'textarea':
				$template = '<v-textarea %s>%s</v-textarea>';
			break;
			case 'radio':
				$template = '<v-radio %s>%s</v-radio>';				
			break;
			case 'radio-group':
				$template = '<v-radio-group %s>%s</v-radio-group>';				
			break;
			case 'select':
				$template = '<v-select %s>%s</v-select>';				
			break;
			case 'slider':
				$template = '<v-slider %s>%s</v-slider>';				
			break;
			case 'checkbox':
				$template = '<v-checkbox %s>%s</v-checkbox>';				
			break;
		}

		return sprintf( $template, $args['atts'], $args['content'] );

	}


}

class BasicTrigger extends Basic {
	protected $helperTitle = '';
	protected $helperContent = '';	
	protected $iconClass = '';	

	public function helperTrigger ( $icon = 'mdi-help-circle-outline', $position = 'append' ) { 


		$icon = new Layout\Icon( $icon );
		$icon->addClass('helper-icon');		
		$icon->attrs( [
			'slot' => $position,
			'@click' => implode(';', [ "dialogOptions.title = '".$this->helperTitle."'", "dialogOptions.content = '".$this->helperContent."'", 'globalDialog = true' ] ) 
		] );

		if ( !empty($this->iconClass)) {
			$icon->addClass( $this->iconClass );		
		}		

		$this->child = $icon;
		
	}

}

class TextField extends BasicTrigger {	

	public function __construct ( $args = [], $model = '' ) {

		parent::__construct('input', '', $args );

		$label = '';
		$helper = false;
		$options = [];
		$iconClass = '';

		extract($args);

		$this->iconClass = $iconClass;

		$this->attrs ([
			'label' => $label,
			'outlined',
			'dense'
			]);

		if ( $helper == true ) { //build in toggle
			
			$helperTitle = '';
			$helperContent = '';

			if ( !empty($options) ) {
				extract($options);
			}
			
			$this->helperTitle = $helperTitle;
			$this->helperContent = $helperContent;
			$this->helperTrigger();

		}		

		if ( !empty($model) ) {
			$this->attr('v-model', $model );
		}


	}	

}

class TextArea extends TextField {
	public function __construct( $args = [], $model = '' ) {
		parent::__construct( $args, $model );
		$this->name = 'textarea';
	}
}

class CommonOption extends Basic {
	public function __construct( $type, $label, $value ) {
		parent::__construct( $type, '', [] );		
		
		if ( is_string ($label) ) {
			$this->attr('label', $label);
		} else {
			$template = new Layout\Template( new HTML\Div( $label ) );
			$template->attr('v-slot:label');
		}

		$this->attr('value', $value);
	}
}

class Checkbox extends CommonOption {
	public function __construct( $label, $value ) {
		parent::__construct( 'checkbox', $label, $value );
	}
}

class Radio extends CommonOption {
	public function __construct ( $label, $value ) {
		parent::__construct( 'radio', $label, $value );
	}
}

class RadioGroup extends Basic {
	
	public function __construct ( $options = [], $model = '' ) {

		parent::__construct( 'radio-group', '', $options );

		$radios = [];

		foreach ($options as $value => $label ) {
			$radios [] = new Radio( $label, $value );
		}

		$this->child = implode('', $radios );

		if ( !empty($model) ) {
			$this->attr('v-model', $model );
		}

	}

}

class Select extends BasicTrigger {

	public $itemsModel;

	public function __construct ( $args = [], $model = '' ) {

		parent::__construct('select', '');

		$items = [];
		$label = '';
		$helper = false;
		$choices = [];
		$options = [];
		
		extract($args);

		$this->attrs ( [
			'label' => $label,
			'outlined',
			'dense'
		] );

		if ( $helper == true ) { //build in toggle
			
			$helperTitle = '';
			$helperContent = '';

			if ( !empty($options) ) {
				extract($options);
			}
			
			$this->helperTitle = $helperTitle;
			$this->helperContent = $helperContent;
			$this->helperTrigger();

		}

		$this->itemsModel = new Core\Model("selectOptions{$this->refID}", $choices);
		
		$this->attr(':items', "{$this->itemsModel}" );

		if ( !empty($model) ) {
			$this->attr('v-model', $model );
		}

	}

}

class Slider extends Basic {

	public function __construct ( $args, $model = '' ) {

		parent::__construct('slider', '');

		$label = '';
		$max = 100;
		$min = 0;

		extract($args);

		$this->attrs ( [
			'label' => $label,
			'max' => $max,			
			'min' => $min,
			'dense'
		] );

		if ( !empty($model) ) {
			$this->attr('v-model', $model );
		}

	}

}

class DatePicker extends Core\SimpleTemplate {

	public function getTemplate ( $args ) {		

		return sprintf( '<v-date-picker %s>%s</v-date-picker>', $args['atts'], $args['content'] );

	}

}

class DateInput extends Core\SimpleTemplate {
	
	protected $model;
	protected $menuModel;
	public $picker;
	public $menu;
	public $input;
	public $buttonSave;
	public $buttonCancel;

	public function __construct ( $args = [], $model = '' ) {
		
		parent::__construct ();		
		$this->picker = new DatePicker();
		$this->menu = new Layout\Menu();
		$this->input = new TextField( $args );
		$this->buttonSave = new Basic('button', 'OK');
		$this->buttonCancel = new Basic('button', 'Cancel');
		$this->menuModel = new Core\Model( $this->ref() , false );

		if ( !empty($model) ) {
			$this->setModel ( $model );
		}

		$this->menu->attrs( [
			'ref' => $this->ref(),
			'v-model' => $this->menuModel,
			':close-on-content-click' => 'false',
			'transition' => 'scale-transition',
			'offset-y',
			'max-width' => '290px',			
			'min-width' => 'auto',
		] );

		$this->input->attrs( [
			'readonly',
			'v-bind' => 'attrs',
			'v-on' => 'on'
		] );

		$this->picker->attrs([
			'no-title',
			'scrollable',
			'@input' => sprintf( '$refs.%s.save(%s);', $this->ref(), $this->model )
		]);

		$this->buttonSave->attrs([
			'text',
			'color' => 'primary'			
		]);

		$this->buttonCancel->attrs([
			'text',
			'color' => 'primary'			
		]);

	}

	public function setModel( $model ) {
		$this->picker->attr('v-model', $model );
		$this->input->attr('v-model', $model );
		$this->menu->attr(':return-value.sync', $model );
		$this->model = $model;		
	}

	public function ref () {
		return 'menu'.$this->refID;
	}

	public function getTemplate ( $args ) {
		$this->buttonSave->attr('@click',sprintf( '$refs.%s.save(%s)', $this->ref(), $this->model ) );
		$this->buttonCancel->attr('@click',sprintf( $this->menuModel.' = false', $this->ref(), $this->model ) );
		$this->picker->child = new Layout\Spacer().$this->buttonCancel.$this->buttonSave;		
		$template = new Layout\Template( $this->input );
		$template->attr('v-slot:activator', '{ on, attrs }');		
		$this->menu->child = $template.$this->picker;
		return "{$this->menu}";
	}

}

class Calendar extends Core\SimpleTemplate {
	
	public $body;
	public $header;
	public $currentdatetime;

	public function __construct( $args = [], $model ) {
		
		parent::__construct();

		$type = 'week';		

		extract($args);

		$this->attrs( [
			'v-model' => $model,
			'ref' => $this->ref(),
			'type' => $type,			
		] );


	}

	public function ref() {
		return 'calendar'.$this->refID;
	}

	public function getTemplate ( $args ) {	
		return sprintf('<v-calendar %s>%s</v-calendar>', $args['atts'], "{$this->child}" );
	}

}

class Progress extends Core\SimpleTemplate {

	public function __construct( $type ) {

		parent::__construct();

		$this->name .= '-'.$type;
				
	}

	public function getTemplate ( $args ) {		

		return sprintf( '<v-%s %s>%s</v-%s>', $this->name, $args['atts'], $args['content'], $this->name );

	}
	
}

class Skeleton extends Progress {

	public function __construct () {
		parent::__construct('loader');		
	}

}
