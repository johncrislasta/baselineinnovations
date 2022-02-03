<?php

class DB {
         
   protected $connection;

   private function dbconnect() {
    $this->connection = new mysqli( MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB ) or die ("Could not connect or select database");    
  }
  
  public function query( $sql, $params = [] ){
 
    $this->dbconnect();
     	  
    $statement = $this->connection->prepare( $sql ); //Use prepare instead of query for SQL injection protection
        
    if ( !empty($params) ) {      
      
      $type = '';      
      
      foreach ($params as $v) {
          $type.= is_string($v) ? 's' : ( is_int($v) ? 'i' : ( is_float($v) ? 'd' : '' ) ); //Bind params to make sure values has correct format, prevent SQL injection
      }

      $statement->bind_param( $type, ...$params );      
      
    }

    $statement->execute();    

    $results = [];

   	if ( $statement->error <= 0  ) {            
  	 	  if ( strpos($sql,'INSERT') === 0 ){
  	       $results = $statement->insert_id;           
  	    } else if (  strpos($sql,'SELECT') === 0 ) {
          $res = $statement->get_result();
          while ($row = $res->fetch_assoc()) {
              $results[] = $row;
         }
        } else {
  	       $results = true;
  	    }
   	} else {
      //echo("Error description: " . $statement->error );
      $results = false;    
    }

    $statement->free_result();
    $statement->close();
    $this->connection->close();      
    return $results;
 
  }

}
