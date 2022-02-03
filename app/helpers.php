<?php

$__vModelKeys = [];

function __vModel ( $name, $value = '', $skip = true ) {
	global $__vModelKeys;

	if ( !isset($__vModelKeys[$name]) ) {
		$__vModelKeys[$name] = sprintf('model.%s', $name );
	}		

	if ( !$skip ) {
		VeutifyApp\Core\ModelCollection::$data[$name] = $value;	
	}

	return $__vModelKeys[$name];
}

function __validateInput ( $input, $type ) { //In case someone bypasses javascript validations
	
	if ( empty($input) ) return false;

	switch ($type) {
		case 'email':
			return filter_var($input, FILTER_VALIDATE_EMAIL);
		break;
		case 'password':
			return preg_match('/^\S*(?=\S{'.PASS_LENGTH.',})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/', $input);
		break;
	}

}


function __RandKeys ( $count, $keys = "AZDEB0246813579XLI" ) {    
    $result = "";
    for ($i = 1; $i <= $count; $i++) {
        $result .= substr($keys, (rand()%(strlen($keys))), 1);
    }    
    return $result;
}

function __sendMail ( $to, $from, $subject, $message, $html = false ) {

	$headers = '';
	
	if ( $html ) {
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";	 	
		$headers .= "From: $from\r\n".
		    "Reply-To: $from\r\n" .
		    "X-Mailer: PHP/" . phpversion();
	}

	return mail( $to, $subject, "$message", $headers );

}