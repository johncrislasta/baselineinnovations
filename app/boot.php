<?php

include 'config.php';

if ( DISPLAY_DEBUG ) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
} else {
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	error_reporting(0);
}

date_default_timezone_set ( APP_TIMEZONE );

include 'data/db.php';
include 'data/model.php';
include 'helpers.php';
include 'request.php';
include 'veutify.php';