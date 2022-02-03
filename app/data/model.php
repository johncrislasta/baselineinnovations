<?php

class ModelCollections extends DB implements Iterator {

	protected $primary_key;
  protected $position = 0;

  public $class;
	public $table;
		
	protected $results = [];
	
  public function fetch ( $query , $params = [], $primary = 'ID' ) {

		$this->primary_key = trim($primary);

		$entries = $this->query( sprintf( $query, $this->table ), $params );

		if ( !empty($entries) ) {
			
			foreach ($entries as $v) {
        if ( isset($v[$primary]) ) {
  				$model = new $this->class();
  				$model->apply( $v, $primary );				
  				$this->results[] = $model;
        }			
			}

		}

	}

  public function count() {
    return count($this->results);
  }

	public function select ( $key, $value ) {
		$this->fetch( sprintf( "SELECT * FROM %s WHERE %s = ?", $this->table, $key ), [ $value ] );    
	}

  /* Array Iterator Methods */

  public function __construct() {
       $this->position = 0;
  }

  public function current() {  
      return $this->results[$this->position];
  }

  public function key() {
      return $this->position;
  }

  public function next() {      
      ++$this->position;
  }

  public function valid() {
      return isset($this->results[$this->position]);
  }

  public function rewind() {  
        $this->position = 0;
  }


}

abstract class Model extends DB {
  
  public $table = '';
  protected $primary_key = '';
  public $ID = false;
  protected $cols = [];

  final public function __get ( $var ) {
  	if ( isset($this->cols[$var]) ){
  		return $this->cols[$var];
  	}
  	return false;
  }

  final public function __set ( $var, $val ) {
  	$this->cols[ $var ] = $val;
  }

  final public function __isset ( $var ) {
    return isset( $this->cols[ $var ] ) && !empty( $this->cols[ $var ] );
  }

  final public function setPrimary ( $key = 'ID' ) {
  	$this->primary_key = trim($key);
  }

  final public function save( $insert = false ) {
  	
      $fields = [];
    	$values = [];
      $placeholder = [];
    	$pairs = [];
    	
    	foreach ($this->cols as $k => $v) {
    		$fields[] = $k;
        $placeholder[] = '?';
    		$values[] = trim($v);
    		$pairs[] = $k."=?";
    	}

    	if ( $insert ) {		
  	  	$this->ID = $this->query(sprintf("INSERT INTO %s ( %s ) VALUES ( %s );", $this->table, implode(', ', $fields), implode(', ', $placeholder ) ), (array) $values );
  	  	return $this->ID;
    	} else {
        $values[] = $this->ID;        
    		return $this->query( sprintf("UPDATE %s SET %s WHERE %s = ?;", $this->table, implode(', ', $pairs), $this->primary_key ), (array) $values );
    	}

  }

  final public function delete() {
  	 return $this->query( sprintf( "DELETE FROM %s WHERE %s = ?;", $this->table, $this->primary_key ), [$this->ID] );
  }

  final public function apply ( $result, $primary = 'ID' ) {
  	$this->setPrimary( $primary );
  	$this->ID = $result[$primary];
  	$this->cols = $result;
  	unset( $this->cols[$primary] );
  	return $this->ID;
  }

  final public function fetch( $sql, $params = [], $primary = 'ID' ) {
    $result = $this->query( $sql, $params );    
  	if ( isset( $result[0] ) ) {      
    	$this->cols = $result[0];
    	$this->setPrimary( $primary );
    	$this->ID = $this->cols[$primary];
    	unset( $this->cols[$primary] );
    }
  	return $this->ID;
  }

  final public function select ( $key, $value ) {   
    return $this->fetch ( sprintf( "SELECT * FROM %s WHERE %s = ?", $this->table, $key ), [ $value ] );
  }

}

class User extends Model {
  public function __construct() {
      $this->table = 'users';
  }	
}

class Reminder extends Model {
  public function __construct () {
      $this->table = 'reminders';
  }
}

class Schedule extends Model {
  public function __construct () {
      $this->table = 'schedules';
  }
}

class Users extends ModelCollections {

	public function __construct() {
    parent::__construct();
		$this->primary_key = 'ID';
		$this->table = 'users';
    $this->class = 'User';
	}	

}

class Reminders extends ModelCollections {

  public function __construct() {
    parent::__construct();
    $this->primary_key = 'ID';
    $this->table = 'reminders';
    $this->class = 'Reminder';
  } 

}

class Schedules extends ModelCollections {

  public function __construct() {
    parent::__construct();
    $this->primary_key = 'ID';
    $this->table = 'schedules';
    $this->class = 'Schedule';
  } 

}
