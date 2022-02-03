<?php

define('MYSQL_DB', 'themeco_baselineinnovation');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', '');
define('MYSQL_HOST', 'localhost');


define('APP_URL', trim( $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'] . dirname( $_SERVER['PHP_SELF'] ), '/') );
define('APP_DIRECTORY', getcwd().'/app' );

define('PASS_LENGTH', 10 );
//define('APP_TIMEZONE', 'America/Chicago' );
define('APP_TIMEZONE', 'Asia/Hongkong' );

define('DISPLAY_DEBUG', false );
