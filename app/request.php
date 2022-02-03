<?php

/* Interfaces */

interface ViewBase {

	public function __construct ( $params );

}

interface ControllerBase {	

	public function default ( $request );

	public function not_permitted ( $message );

}

/* Abstracts */

abstract class Controller implements ControllerBase {
	
	public $require_payload = true;

	final public function call ( $request ) {		//Public methods
		$method = empty( $request->method ) ? 'default' : $request->method;
		if ( method_exists( $this, $method ) ) {
			$this->{$method}( $request );
			die();
		}
	}

	final public function callSecure ( $request, $user ) { //Secured methods (require's active session)
		$method = $request->method;
		if ( method_exists( $this, $method ) ) {
			if ( Session::isValid() ) {
				$this->{$method}( $request, $user );
			} else {
				session_destroy();
				$this->not_permitted('Action is not permitted or session ended.');
			}
			die();
		}
	}
}

abstract class View implements ViewBase {

}

abstract class Session {
	
	static public $vars = [ 'vars' => ['nonce'], 'methods' => [] ];
	static public $usr = false;	

	protected $sessionID = '';
	protected $nonce = '';
	protected $data = [];	

	static public function registerVars ( $vars = [], $method = false ) {
		if ( $method ) {
			self::$vars['methods'] = array_merge(self::$vars['methods'], $vars);
		}
		else {
			self::$vars['vars'] = array_merge(self::$vars['vars'], $vars);
		}
	}

	final public function is_allowed ( $key, $method = false ) {
		return in_array($key, self::$vars[ $method ? 'methods' : 'vars' ]);
	}

	final public function __get( $key ) {
		if ( isset($this->data[$key])) {
			return $this->data[$key];
		} 
		return false;
	}

	final public function __set ( $key, $var ) {
		$this->data[$key] = $var;		
	}

	final public function __isset ( $key ) {
		return isset($this->data[$key]);
	}

	static public function isValid () {		
		$model = self::user();		
		return !$model || $model->ID == 0 ? false : password_verify( "{$model->email}:{$model->ID}:".session_id() , $_SESSION['session'] );
	}

	static public function init( $key1, $key2 ) {
		session_regenerate_id();						
		$_SESSION['session'] = password_hash("{$key1}:{$key2}:".session_id(), PASSWORD_DEFAULT );
		$_SESSION['_vmID'] = $key2;
	}

	static public function user () {
		if ( empty($_SESSION['_vmID']) ) return false;
		if ( self::$usr ) return self::$usr;
		self::$usr = new User();		
		self::$usr->select('ID', $_SESSION['_vmID'] );
		return self::$usr;
	}

}

/* Main Classes */

class Request extends Session {

	public function __construct() {
		$path = explode('/', trim( str_replace(dirname( $_SERVER['PHP_SELF'] ), '/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) , '/') );
		$this->controller = array_shift($path);
		$this->method = array_shift($path);
		$this->paths = $path;
		foreach ( $_REQUEST as $k => $v ) {
			if ( $this->is_allowed( $k ) ) {
				$this->data[$k] = trim($v);
			}
		}
	}

	public function hasPayload() {		
		return !empty($_REQUEST);
	}

}

class ControllerCollection {

	public static $routes = [];	

	public static function addRoute ( $slug, $controller ) {
		self::$routes[$slug] = $controller;
	}

	public static function execute () {
				
		session_start();

		$request = new Request();			
		if ( isset(self::$routes[$request->controller]) ) {			
			if ( $request->hasPayload() || !self::$routes[$request->controller]->require_payload ) {					
				if ( $request->is_allowed( $request->method , true ) ) {
					self::$routes[$request->controller]->callSecure( $request, Session::user() );
				}
				else {
					self::$routes[$request->controller]->call( $request );
				}
			} else die('This action is not permitted.');
		}

	}

}

class UserController extends Controller {

	public function __construct() {
		
		Session::registerVars( [ //Specific GET and POST, prevent injection of unwanted variables/parameter
			'ID', 'firstName', 'lastName', 'email', 'password', 'phone', 'newpassword', 'confirmpassword', 'newEmail', 'confirmEmail', 'newPhone', 'code'
		] );	
		
		Session::registerVars( [ //Secured methods
			'pass', 'email', 'phone'
		], true );	

	}

	public function default ( $request ) {
		//$view = new UserView();
		//$view->render( $request );
	}

	public function save ( $request ) {

		$message = 'Incorrect email format or the password does not contain the following: lowercase and uppercase letter, number, special character.';
		$refID = 0;

		if ( __validateInput ($request->email, 'email') && __validateInput( $request->password, 'password' )  )	{

			$modelRef = new User();
			$modelRef->select('email',$request->email);

			if ( $modelRef->ID > 0 ) {
				$message = 'Unable to create account, or it might be already taken.';
			} else {

				$model = new User();
				$model->apply( [
					'ID' => 0,
					'firstName' => $request->firstName,
					'lastName' => $request->lastName,
					'email' => $request->email,
					'role' => 'user',
					'phone' => '',
					'code' => '',
					'code_expire' => 0,
					'password' => password_hash($request->password, PASSWORD_DEFAULT),
				] );

				$id = $model->save( true );		

				if ( $id > 0 ) {
					$message = 'Your account is successfully registered, you may now log in.';
					$refID = $id;					
				} else {
					$message = 'Unable to create the account.';					
				}

			}
		} 

		new JSONView( ['message' => $message, 'ID' => $refID ] );
	}

	public function pass ( $request, $user ) {
		
		$message = 'Current password is incorrect.';
		$status = false;
				
		if ( !__validateInput( $request->newpassword, 'password' ) || !__validateInput( $request->confirmpassword, 'password' ) ) {
			$message = 'The password does not contain the following: lowercase and uppercase letter, number, special character.';			
		} else if ( $request->newpassword !== $request->confirmpassword ) {
			$message = 'Password confirmation is incorrect.';			
		} else {
			if ( password_verify( $request->password, $user->password ) ) {
				$user->password = password_hash($request->newpassword, PASSWORD_DEFAULT);										
				$user->save();
				$message = 'Account password is successfully updated.';
				$status = true;
			}
		}

		new JSONView ( ['message' => $message, 'success' => $status ] );

	}

	public function email ( $request, $user ) {
		
		$message = '';
		$status = false;

		$modelRef = new User();
		$modelRef->select('email', $request->newEmail );

		if ( !__validateInput( $request->newEmail, 'email' ) || !__validateInput( $request->confirmEmail, 'email' ) ) {
			$message = 'Incorrect email format.';			
		} else if ( $request->newEmail !== $request->confirmEmail) {
			$message = 'Email confirmation is incorrect.';			
		} else if ( $request->newEmail == $user->email || $modelRef->ID > 0) {
			$message = 'New email is the same as the current one or already taken.';			
		} else {								
			$user->email = $request->newEmail;
			$user->save();					
			$message = 'Email has been changed successfully.';			
			$status = true;
		}			

		new JSONView ( ['message' => $message, 'success' => $status ] );

	}

	public function phone ( $request, $user ) {			
		$user->phone = $request->newPhone;
		$user->save();
		new JSONView ( ['message' => 'Phone has been changed successfully.', 'success' => true ] );
	}

	public function login ( $request ) {

		$model = new User();
		$model->select( 'email', $request->email );
		
		$message = 'Incorrect email or password.';
		$status = false;
		$data = [];

		if ( password_verify( $request->password, $model->password ) ) {
			Session::init( $model->email, $model->ID );
			$message = '';
			$status = true;
			$data = ['email' => $model->email, 'phone' => $model->phone, 'role' => $model->role ];			
		} 

		new JSONView ( ['message' => $message, 'success' => $status, 'data' => $data ] );
	}

	public function logoff ( $request ) {
		session_destroy();
		new JSONView ( ['message' => '', 'success' => true ] );
	}

	public function passwordreset ( $request ) {
		
		$model = new User();
		$model->select( 'email', $request->email );		
		$ref = __RandKeys(10, '3451267980');

		$response = ['message' =>  sprintf('The verification code for password reset is sent to %s. REF:%s', $request->email, $ref ), 'success' => true, 'ref' => $ref ];
		//We don't display user error to prevent Email guessing

		if ( $model->ID > 0 ) { 
			$code = 'OTP-'.__RandKeys(8);						
			$model->code = password_hash($code, PASSWORD_DEFAULT); //We don't want anyone, even admin to spy on user requested OTP :)
			$model->code_expire = time() + ( 60 * 5 );
			$model->save();						
			if ( !__sendMail( $model->email, "no-reply@".$_SERVER['SERVER_NAME'], "You have requested a password reset.", new TemplateView ( ['folder' => 'emails', 'name' => 'verificationcode', 'vars' => ['code' => $code, 'ref' => $ref] ], true ), true ) ) {
				$response['mail-error'] = true;
			}
		}


		new JSONView (  $response );		

	}

	public function verifyreset( $request ) {

		$model = new User();
		$model->select( 'email', $request->email );	

		$message = 'Incorrect verification code or already expired. Please try sending another request.';
		$status = false;

		if ( $model->ID > 0 ) {
			if ( password_verify( $request->code, $model->code ) && $model->code_expire >= time() ) {
				if ( !__validateInput( $request->newpassword, 'password' ) || !__validateInput( $request->confirmpassword, 'password' ) ) {
					$message = 'The password does not contain the following: lowercase and uppercase letter, number, special character.';			
				} else if ( $request->newpassword !== $request->confirmpassword ) {
					$message = 'Password confirmation is incorrect.';			
				} else {
					$model->password = password_hash($request->newpassword, PASSWORD_DEFAULT);
					$model->code = '';
					$model->code_expire = 0;
					$model->save();
					$message = 'Password is successfully changed. You may now sign-in.';
					$status = true;
				}

			}
		}

		new JSONView ( ['message' =>  $message, 'success' => $status ] );
	}

	public function not_permitted ( $message ) {		
		new JSONView ( ['message' => $message, 'ended' => true, 'success' => false ] );		
	}

}

class ScheduleController extends Controller {

	public function __construct () {

		Session::registerVars( [ //Specific GET and POST, prevent injection of unwanted variables/parameter
			'repeatDaily', 'repeatWeekly', 'schedule', 'time', 'allSchedules'
		] );

		Session::registerVars( [ //Secured methods
			'add', 'delete', 'get', 'download'
		], true );

	}

	public function default( $request ) {		
		$schedules = json_decode($request->allSchedules, true);			
		$result = [];
		foreach ($schedules as $time) {
			if ( !empty($time) ) {				
				$model = new Schedules();
				$model->select('timestamp', strtotime($time) );
				$result[$time] = $model->count();
			}
		}
		new JSONView (  $result );
	}

	public function add( $request, $user ) {

		$time = strtotime($request->time);

		$model = new Schedule();
		$model->fetch( sprintf( "SELECT * FROM %s WHERE %s = ? AND %s = ?", $model->table, 'timestamp', 'user' ), [ $time, $user->ID ] );
		$model->user = $user->ID;
		$model->timestamp = $time;
		$model->date = $request->schedule;
		$model->daily = $request->repeatDaily == 'Yes' ? 1 : 0;
		$model->weekly = $request->repeatWeekly == 'Yes' ? 1 : 0;
		$model->weekday = date( 'D', $time);
		$model->hour = intval( date( 'H', $time) );
		$model->save( $model->ID == 0 );

		new JSONView (  ['message' => 'Pray timeslot is booked successfully on '.$request->schedule, 'success' => true ] );

	}

	public function get ( $request, $user ) {
		$model = new Schedule();
		$model->fetch( sprintf( "SELECT * FROM %s WHERE %s = ? AND %s = ?", $model->table, 'timestamp', 'user' ), [ strtotime($request->time), $user->ID ] );
		new JSONView (  ['message' => '', 'success' => true, 'data' => [
			'schedule' => $model->ID > 0 ? $model->date : '',
			'daily' =>  $model->ID > 0 && $model->daily == 1 ? 'Yes' : 'No',
			'weekly' =>  $model->ID > 0 && $model->weekly == 1 ? 'Yes' : 'No',
			'timeslot' => $model->ID > 0 ? date('Y-m-d H:i', $model->timestamp) : 0
		] ] );
	}

	public function delete( $request, $user ) {

		$message = 'Can\'t cancel non-existing booking.';
		$status = false;

		/*
		$model = new Schedule();
		$model->fetch( sprintf( "SELECT * FROM %s WHERE %s = ? AND %s = ?", $model->table, 'timestamp', 'user' ), [ strtotime($request->time), $user->ID ] );		
		if ( $model->ID > 0 ) {
			$model->delete();
			$message = 'Selected booking is canceled';
			$status = true;
		} 
		*/

		new JSONView (  ['message' => $message, 'success' => $status ] );

	}

	public function download ( $request, $user ) {
				
		$model = new Schedule();
		$model->fetch( sprintf( "SELECT * FROM %s WHERE %s = ? AND %s = ?", $model->table, 'timestamp', 'user' ), [ strtotime($request->time), $user->ID ] );
		$reminder = new Reminder();
		$reminder->select('date', date('Y-m-d', $model->timestamp) );		

		$filename = "event_calendar.ics";

		header("Content-type:text/calendar");
		header("Content-Disposition: attachment; filename=$filename");

		new TemplateView ( ['folder' => 'downloads', 'name' => 'ical', 'vars' => [
			'timezone' => date('e'),
			'model' => $model,
			'reminder' => $reminder,
			'freq' => $model->weekly == 1 ? 'WEEKLY' : ( $model->daily == 1 ? 'DAILY' : '' ),
			'offset' => date('O'),
			'savingtime' => intval( date('I') ) == 1 ? 'PDT' : 'PST',		
		] ] );

	}

	public function not_permitted ( $message ) {		
		new JSONView ( ['message' => $message, 'ended' => true, 'success' => false ] );		
	}

}

class ReminderController extends Controller {

	public function __construct () {

		Session::registerVars( [ //Specific GET and POST, prevent injection of unwanted variables/parameter
			'reminderDate', 'reminderMessage'
		] );

		Session::registerVars( [ //Secured methods
			'add', 'get'
		], true );

	}

	public function default( $request ) {

	}

	public function add( $request, $user ) {

		$message = 'Your account has no admin capability.';
		$status = false;

		if ( $user->role == 'admin' ) {
			$model = new Reminder();
			$model->select('date', $request->reminderDate);
			$model->date = $request->reminderDate;
			$model->reminder = $request->reminderMessage;
			$model->save( $model->ID == 0 );
			$status = true;			
			$message= 'Reminder message is applied for '.$request->reminderDate;
		}

		new JSONView (  ['message' => $message, 'success' => $status ] );

	}

	public function get( $request, $user ) {

		$message = 'Your account has no admin capability.';
		$status = false;
		$data = ['message' => '', 'date' => ''];

		if ( $user->role == 'admin' ) {
			
			$model = new Reminder();
			$model->select('date', $request->reminderDate);			
			$status = true;			
			$message= '';

			if ( $model->ID > 0 ) {
				$data = ['message' => $model->reminder, 'date' => $model->date];
			}

		}

		new JSONView (  ['message' => $message, 'success' => $status, 'data' => $data ] );

	}

	public function not_permitted ( $message ) {		
		new JSONView ( ['message' => $message, 'ended' => true, 'success' => false ] );		
	}

}

class CronController extends Controller {	

	public function __construct() {
		$this->require_payload = false;		
	}

	public function default ( $request ) {
		
		$currentTime = time();

		$nextTime = $currentTime + 3600; //Next hour

		$targetHour = intval( date('i', $currentTime ) ) >= 45 ? intval( date('H', $nextTime ) ) : false; //Select next hour within 15minutes notice
		$targetDay = date('D', $currentTime );		

		if ( $targetHour ) {

			/* Weekly Schedules */
			/*$schedules1 = new Schedules();
			$schedules1->fetch( sprintf( 'SELECT * FROM %s WHERE %s = ? AND %s = ? AND %s = ? AND %s = ?', $schedules->table, 'hour', 'weekday', 'weekly', 'daily' ), [ $targetHour == 24 ? 0 : $targetHour, $targetDay, 1, 0 ] );
			
			if ( $schedules1->count() > 0 ) {
				$this->unique ( $schedules1 );
			}*/	

			/* Daily Schedules */
			/*$schedules2 = new Schedules();
			$schedules2->fetch( sprintf( 'SELECT * FROM %s WHERE %s = ? AND %s = ? AND %s = ?', $schedules2->table, 'hour', 'daily', 'weekly' ), [ $targetHour == 24 ? 0 : $targetHour, 1, 0 ] );
			
			if ( $schedules2->count() > 0 ) {
				$this->unique ( $schedules2 );
			}*/

			/* Single Schedules */
			/*$schedules3 = new Schedules();
			$schedules3->fetch( sprintf( 'SELECT * FROM %s WHERE %s = ? AND %s = ? AND %s = ?', $schedules3->table, 'timestamp', 'daily', 'weekly' ), [ strtotime( date('d-m-Y H:00', $nextTime) ), 0, 0 ] );
			
			if ( $schedules3->count() > 0 ) {
				$this->unique ( $schedules3 );
			}*/

			/* Combined Query - Optimized */

			$schedules = new Schedules();
			$schedules->fetch( sprintf(
				'SELECT * FROM %s WHERE ( %s = ? AND %s = ? AND %s = ? AND %s = ? ) OR ( %s = ? AND %s = ? AND %s = ? ) OR ( %s = ? AND %s = ? AND %s = ? );',
				$schedules->table, 
				'hour', 'weekday', 'weekly', 'daily', //Weekly Schedules
				'hour', 'daily', 'weekly', //Daily Schedules
				'timestamp', 'daily', 'weekly' //Single Schedules
			), [
				$targetHour == 24 ? 0 : $targetHour, $targetDay, 1, 0, //Weekly Schedules
				$targetHour == 24 ? 0 : $targetHour, 1, 0, //Daily Schedules
				strtotime( date('d-m-Y H:00', $nextTime) ), 0, 0 //Single Schedules
			] );			

			if ( $schedules->count() > 0 ) {
				//Let's send notifications
				$reminderMessage = '';
				$reminderDate = date('Y-m-d', $currentTime);
				$reminder = new Reminder();
				$reminder->select('date', $reminderDate );

				if ( $reminder->ID > 0 && !empty($reminder->reminder) ) {
					$reminderMessage = $reminder->reminder;
				}
								
				foreach ( $schedules as $schedule ) {
					$user = new User();
					$user->select('ID',  $schedule->user );
					if ( $user->ID > 0 ) {
						__sendMail( $user->email, "no-reply@".$_SERVER['SERVER_NAME'], "Your pray event notification.", new TemplateView ( ['folder' => 'emails', 'name' => 'notification', 'vars' => ['name' => $user->firstName, 'time' => date( 'g A', $nextTime ), 'date' => date( 'l, F j Y', $currentTime), 'reminder' => $reminderMessage] ], true ), true );
					}
				}				
			}				

		}

		echo 'Job done!';
	}

	public function not_permitted ( $message ) {
		new JSONView ( ['message' => $message, 'ended' => true, 'success' => false ] );	
	}


}


class TestController extends Controller { // This is used for debugging and testing only, new method can be added depending on what you need to test, eg /debug/ or /debug/method/

	public function __construct() {
		$this->require_payload = false;		
	}

	public function default ( $request ) {		
		/*
	__sendMail( 'testaccount@localhost.com', "no-reply@".$_SERVER['SERVER_NAME'], "Your pray event notification.", new TemplateView ( ['folder' => 'emails', 'name' => 'notification', 'vars' => ['name' => 'James', 'time' => '4 PM', 'date' => 'Monday, November 29 2021', 'reminder' => 'Today, we ask everyone to pray for those working to help heal others. Keep them safe and give them the courage to continue their important work.'] ], true ), true );*/			
	}		

	public function not_permitted ( $message ) {
		new JSONView ( ['message' => $message, 'ended' => true, 'success' => false ] );	
	}
}

class JSONView extends View {

	public function __construct ( $params ) {
		echo json_encode( $params );
	}

}

class TemplateView extends View {

	protected $content = '';

	public function __construct ( $params, $capture = false ) {
		
		$file = APP_DIRECTORY . '/views/' . trim( $params['folder'].'/'.$params['name'], '/' ).'.php' ;		
		
		if  ( file_exists($file) ) {
			
			if ( !empty( $params['vars'] ) ) {
			 extract($params['vars']);
			}

			if ( $capture ) ob_start();

			include_once $file;

			if ( $capture ) $this->content = ob_get_clean();
		}

	}

	public function __toString() {
		return $this->content;
	}

}


ControllerCollection::addRoute('user', new UserController() );
ControllerCollection::addRoute('schedule', new ScheduleController() );
ControllerCollection::addRoute('reminder', new ReminderController() );
ControllerCollection::addRoute('cron', new CronController() );
ControllerCollection::addRoute('debug', new TestController() );
ControllerCollection::execute();

//session_destroy();

/*
$day = date('w');
$week_start = date('d-m-Y 00:00', strtotime('-'.$day.' days'));
$week_end = date('d-m-Y 23:59', strtotime('+'.(6-$day).' days'));

var_dump($week_start, $week_end, date( 'd-m-Y H:i', strtotime($week_start) ), date( 'd-m-Y H:i', strtotime($week_end) ) );*/