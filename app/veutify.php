<?php

namespace VeutifyApp;

/* Common Models And Data */

$navwidth = new Core\Model('toolbarwidth', 150 );
$snackbarcolor = new Core\Model('snackbarcolor', 'success' );
$months = new Core\Model('months', ["Jan.", "Feb.", "Mar.", "Apr.", "May.", "Jun.", "Jul.", "Aug.", "Sep.", "Oct.", "Nov.", "Dec."] );
$dialogModel = new Core\Model('modalActive', false);
$rangemodel = new Core\Model('rangeDate', '');
$inProgress = new Core\Model('inProgress', false);
$isSigningIn = new Core\Model('isSigningIn', true);
$isVerification = new Core\Model('isVerification', false);
$verificationRef = new Core\Model('verificationRef', '');
$selectedDate = new Core\Model('selectedDate', '');
$reminderSourceDate = new Core\Model('__datePicker');
$currentReminderDate = new Core\Model('currentReminderDate');
$currentReminderMessage = new Core\Model('currentReminderMessage');
$repDaily = new Core\Model('repeatDaily');
$repWeekly = new Core\Model('repeatWeekly');
$tmSlot = new Core\Model('timeSlot');
$downloadURL = new Core\Model('downloadSchedule');

$inProgressCalendar = new Core\Model('inProgressCalendar', false);
$progresscalendar = new Controls\Progress('circular');
$progresscalendar->attrs(['indeterminate', 'dense', ':width' => 2, ':size' => 10 ]);

$snackbarMessage = new Core\Model('progressNotice','');
$snackbar = new Layout\Snackbar('{{ '.$snackbarMessage.' }}');
$snackbar->attrs([	
	'v-bind:color' => $snackbarcolor,
	'outlined'
]);

$useremail = '';
$userphone = 'N/A';
$userrole = 'user';

if ( \Session::isValid() ) {
	$user = \Session::user();
	$useremail = $user->email;
	$userphone = $user->phone;
	$userrole = $user->role;
}

/*
$hourdayweek = date('w');
$hour_week_start = date('d-m-Y 00:00', strtotime('-'.$hourdayweek.' days'));
$hour_week_end = date('d-m-Y 23:59', strtotime('+'.(6-$hourdayweek).' days'));

$hours = new \Schedules();
$hours->fetch(sprintf( "SELECT * FROM %s WHERE %s >= ? AND %s <= ?;", $hours->table, 'timestamp', 'timestamp' ), [ strtotime($hour_week_start) * 1000, strtotime($hour_week_end) * 1000  ] ); //JS timestamp is ahead of 1000, let's convert PHP timestamp to js timestamp
*/

$hoursprayed = new Core\Model('hoursprayed', 0 );

/* Grouped Models */

__vModel('APP_CONFIG', ['URL' => APP_URL, 'ENDPOINTS' => [ 
	'save_user' => 'user/save',
	'get_user' => 'user',
	'login' => 'user/login',
	'pass' => 'user/pass',
	'email' => 'user/email',
	'phone' => 'user/phone',
	'logoff' => 'user/logoff',
	'passwordreset' => 'user/passwordreset',
	'verifyreset' => 'user/verifyreset',
	'add_reminder' => 'reminder/add',
	'get_reminder' => 'reminder/get',
	'schedules' => 'schedule',
	'add_schedule' => 'schedule/add',
	'get_schedule' => 'schedule/get',
	'cancel_schedule' => 'schedule/delete',
	'download' => 'schedule/download'
] ], false );

__vModel('signin', ['email' => '', 'password' => '', 'role' => 'user', 'verification' => '', 'signed' => \Session::isValid() ], false );
__vModel('signup', ['firstName' => '', 'lastName' => '', 'email' => '', 'password' => ''], false );
__vModel('account', [ 'email' => $useremail, 'phone' => $userphone, 'role' => $userrole, 'currentPass' => '', 'newPass' => '', 'repeatPass' => '', 'newEmail' => '', 'confirmEmail' => '', 'newPhone' => '', 'editEmail' => false, 'editPhone' => false ], false );

/* Validations here */
$codeVerifyVal = new Form\Validation('codeVerifyRequire', ['name' => 'Verification code'], 'required' );
$firstNameVal = new Form\Validation('firstnameRequire', ['name' => 'First Name'], 'required' );
$lastNameVal = new Form\Validation('lastnameRequire', ['name' => 'Last Name'], 'required' );
$messageVal = new Form\Validation('messageRequire', ['name' => 'Reminder Message'], 'required' );

$compareEmail = explode('.', __vModel('account.newEmail') );
array_shift ( $compareEmail );
$emailVal = new Form\Validation('emailRequire', ['name' => 'Email'], ['required', 'email'] );
$emailConfirm = new Form\Validation('emailConfirm', ['name' => 'Email', 'rules' => ["v  => ( v && v == vueData.".implode('.', $compareEmail )." ) || 'Email does not match' "] ], ['required', 'email'] );

$passNotice = 'Password must contain the following: lowercase and uppercase letter, number, special character.';

$passRules = [
	"v  => ( v && /(?=.*[a-z])/.test(v) ) || '".$passNotice ."' ",
	"v  => ( v && /(?=.*[A-Z])/.test(v) ) || '".$passNotice ."' ",
	"v  => ( v && /(?=.*[0-9])/.test(v) ) || '".$passNotice ."' ",
	"v  => ( v && /(?=.*[!@#$%^&*])/.test(v) ) || '".$passNotice ."' ",
	"v  => ( v && /(?=.{".PASS_LENGTH.",})/.test(v) ) || 'Password must be ".PASS_LENGTH." characters or longer.' ",
];

$comparePass = explode('.', __vModel('account.newPass') );
array_shift ( $comparePass );

$passVal = new Form\Validation('passRequire', ['name' => 'Password', 'rules' =>  $passRules ], 'required' );
$passConfirm = new Form\Validation('passConfirmRequire', ['name' => 'Password', 'rules' => array_merge( $passRules, ["v  => ( v && v == vueData.".implode('.', $comparePass )." ) || 'New password does not match' "] ) ], 'required' );
$loginPassVal = new Form\Validation('passlogRequire', ['name' => 'Password'], ['required'] );

/* The common dialogs - Start */

/*sign in*/
$signinheadline = new HTML\Text('Sign In', 'h2');
$signemail = new Controls\TextField(['label' => 'Email'], __vModel('signin.email') );
$signpass = new Controls\TextField(['label' => 'Password'], __vModel('signin.password') );
$signemail->attr(':rules', $emailVal);
$signpass->attrs([':rules' => $loginPassVal, 'type' => 'password', 'autocomplete' => 'new-password']);

$forgotpass = new Controls\Basic('button', 'Forgot Your Password?' );
$forgotpass->attrs(['text', '@click' => $isSigningIn.'= false; '.$isVerification.' = false']);
$forgotpass->addClass('forgotpass');

$signinbutton = new Controls\Basic('button', 'Sign In' );
$signinbutton->addClass('signin-button');
$signinform = new Form\Create( $signemail.$signpass.$forgotpass, $signinbutton );
$signinform->action->attr('@click', sprintf( "callAny(function(instance){ if ( instance.\$refs.%s.validate() ) { 

	".$inProgress."=true; ".$snackbar->model." = false;

	request (
	".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.login').",
	'POST',
	function ( instance, request, data ) {				
		
		".$snackbarcolor." = 'error';
		".$snackbar->model." = true;

		if ( data.success == false ) {			
			".$snackbarMessage."= data.message ;					
		}

		".$inProgress."=false;

		if ( data.success == true ) {
			".$snackbarcolor." = 'success';
			".$snackbar->model." = false;
			".__vModel('signin.email')." = '';		
			".__vModel('signin.password')." = '';			
			".__vModel('account.email')." = data.data.email;
			".__vModel('account.phone')." = data.data.phone;
			".__vModel('account.role')." = data.data.role;
			instance.\$refs.%s.resetValidation();
			%s =false; %s = true;
		}
	},
	{
		email : ".__vModel('signin.email').",
		password : ".__vModel('signin.password')."		
	}
	);

}});", $signinform->ref(), $signinform->ref(), $dialogModel, __vModel('signin.signed')  ) );

$signin = new Layout\Container( $signinheadline.$signinform );
$signin->attr('v-show', $isSigningIn .' == true');

/* forgot pass - part 1*/
$forgotheadline = new HTML\Text('Account Recovery', 'h2');
$forgotemail = new Controls\TextField(['label' => 'Email'], __vModel('signin.email') );
$forgotemail->attr(':rules', $emailVal);
$forgotrequestbutton = new Controls\Basic('button', 'Request password reset' );
//$forgotrequestbutton->attr('@click', $isVerification.'=true');

/* forgot pass - part 2*/
$forgotcodetext = new HTML\Text('A verification code is sent to <strong>{{ '.__vModel('signin.email').'}}</strong>. REF:<strong>{{ '.$verificationRef.' }}</strong>', 'subtitle-1');
$forgotcode = new Controls\TextField(['label' => 'Verification Code'], __vModel('signin.verification') );
$forgotnpass = new Controls\TextField(['label' => 'New Password'], __vModel('account.newPass') );
$forgotrpass = new Controls\TextField(['label' => 'Repeat New Password'], __vModel('account.repeatPass') );
$forgotcode->attrs([':rules' => $codeVerifyVal]);
$forgotnpass->attrs([':rules' => $passVal, 'type' => 'password', 'autocomplete' => 'new-password']);
$forgotrpass->attrs([':rules' => $passConfirm, 'type' => 'password', 'autocomplete' => 'new-password']);
$forgotchangebutton = new Controls\Basic('button', 'Change Password' );

$forgotform = new Form\Create( $forgotemail, '' );

$forgotrequestbutton->attr('@click', sprintf( "callAny(function(instance){ if ( instance.\$refs.%s.validate() ) { 

	".$inProgress."=true; ".$snackbar->model." = false;
	".__vModel('signin.verification')." = '';
	".__vModel('account.newPass')." = '';
	".__vModel('account.repeatPass')." = '';

	request (
	".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.passwordreset').",
	'POST',
	function ( instance, request, data ) {				
		
		".$snackbarcolor." = 'error';
		".$snackbar->model." = true;
		".$snackbarMessage."= data.message ;					

		".$inProgress."=false;

		if ( data.success == true ) {
			".$snackbarcolor." = 'success';
			".$isVerification." = true;
			".$verificationRef." = data.ref;					
			instance.\$refs.%s.resetValidation();			
		}
	},
	{
		email : ".__vModel('signin.email')."		
	}
	);

}});", $forgotform->ref(), $forgotform->ref()  ) );

$forgotform->action .= $forgotrequestbutton;

$forgotrequestbutton->child = 'Resend Verification';
$forgotrequestbutton->attr('text');

$forgotformpass = new Form\Create( $forgotnpass.$forgotrpass.$forgotcode.$forgotcodetext.$forgotrequestbutton, $forgotchangebutton );
$forgotformpass->action->attr('@click', sprintf( "callAny(function(instance){ if ( instance.\$refs.%s.validate() ) { 

	".$inProgress."=true; ".$snackbar->model." = false;

	request (
	".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.verifyreset').",
	'POST',
	function ( instance, request, data ) {				
		
		".$snackbarcolor." = 'error';
		".$snackbar->model." = true;
		".$snackbarMessage."= data.message ;					

		".$inProgress."=false;

		if ( data.success == true ) {
			".$snackbarcolor." = 'success';
			".$isSigningIn ." = true;
			".__vModel('account.newPass')." = '';
			".__vModel('account.repeatPass')." = '';
			instance.\$refs.%s.resetValidation();			
		}
	},
	{	
		email : ".__vModel('signin.email').",
		code : ".__vModel('signin.verification').",
		newpassword : ".__vModel('account.newPass').",
		confirmpassword : ".__vModel('account.repeatPass')."		
	}
	);

}});", $forgotformpass->ref(), $forgotformpass->ref()  ) );


$forgotform->attr('v-show', $isVerification.'== false');
$forgotformpass->attr('v-show', $isVerification.'== true');

$backtosignin = new Controls\Basic('button', 'Back' );
$backtosignin->attrs(['@click' => $isSigningIn .' = true', 'absolute', 'bottom', 'right']);
$accountrecovery = new Layout\Container( $forgotheadline.$forgotform.$forgotformpass.$backtosignin );
$accountrecovery->attr('v-show', $isSigningIn .' == false');



/*sign up*/
$signupheadline = new HTML\Text('Need an Account?', 'h2');
$signupfirst = new Controls\TextField(['label' => 'First'], __vModel('signup.firstName') );
$signupfirst->attrs( ['color' => 'white', ':rules' => $firstNameVal ] );
$signuplast = new Controls\TextField(['label' => 'Last'], __vModel('signup.lastName') );
$signuplast->attrs( ['color' => 'white', ':rules' => $lastNameVal ] );
$signupemail = new Controls\TextField(['label' => 'Email'], __vModel('signup.email') );
$signupemail->attrs( ['color' => 'white', ':rules' => $emailVal ] );
$signuppass = new Controls\TextField(['label' => 'Password'], __vModel('signup.password') );
$signuppass->attrs( ['color' => 'white', ':rules' => $passVal, 'type' => 'password', 'autocomplete' => 'new-password' ] );
$signupbutton = new Controls\Basic('button', 'Sign Up' );
$signupbutton->addClass('signup-button v-btn--inverse');

$signupform = new Form\Create( $signupfirst.$signuplast.$signupemail.$signuppass, $signupbutton);
$signupform->action->attr('@click', sprintf( "callAny(function(instance){ if ( instance.\$refs.%s.validate() ) {

".$inProgress."=true; ".$snackbar->model." = false;

request (
	".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.save_user').",
	'POST',
	function ( instance, request, data ) {
		".$snackbarcolor." = 'error';				
		".$snackbarMessage."= data.message ;		
		".$inProgress."=false; ".$snackbar->model." = true;
		if ( data.ID > 0 ) {
			".$snackbarcolor." = 'success';
			".__vModel('signup.firstName')." = '';		
			".__vModel('signup.lastName')." = '';
			".__vModel('signup.email')." = '';
			".__vModel('signup.password')." = '';
			instance.\$refs.%s.resetValidation();
		}
	},
	{
		firstName : ".__vModel('signup.firstName').",
		lastName : ".__vModel('signup.lastName').",
		email : ".__vModel('signup.email').",
		password : ".__vModel('signup.password')."		
	}
);


}})", $signupform->ref(), $signupform->ref() ) );

$signup = new Layout\Container( $signupheadline.$signupform );
$signup->addClass('signup-section');

$signinup = new Layout\Row([ $signin.$accountrecovery, $signup ]);
$signinup->addClass('ma-0 signinup-row');
$signinup->columns[1]->addClass('signup-bg');
$signinup->columns[0]->addClass('v-relative');
$signinup->attr('v-show', sprintf("%s !== true ", __vModel('signin.signed') ) );

/* Time slot */
$timeslotform = new Form\Create( '', '' );
$timeslotform->addClass('timeslot-form');
$timeslotheadline = new HTML\Text('Confirm Your Timeslot', 'h2');
$timeslotconfirm = new HTML\Text('You\'re signing up to pray on <strong>{{ '.$selectedDate.' }}.</strong>', 'subtitle-1');
$repeatdaily = new Controls\Checkbox('Repeat Daily', 'Yes' );
$repeatdaily->attrs(['v-model' => $repDaily, '@click' =>  $repWeekly."='No'" ]);
$repeatweekly = new Controls\Checkbox('Repeat Weekly', 'Yes' );
$repeatweekly->attrs(['v-model' => $repWeekly, '@click' => $repDaily."='No'"]);
$confirmsignup = new Controls\Basic('button', 'Sign Up' );
$confirmsignup->addClass('ml-0');
$cancelsignup = new Controls\Basic('button', 'Cancel' );
$cancelsignup->addClass('v-btn--inverse mr-0');

$cancelsignup->attr('@click', $dialogModel." = false;");

/*
$cancelsignup->attr('@click', sprintf("callAny(function(instance){ if ( instance.\$refs.%s.validate() ) {

	".$inProgress."=true; ".$snackbar->model." = false;

	request (
		".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.cancel_schedule').",
		'POST',
		function ( instance, request, data ) {
			".$snackbarcolor." = 'error';
			".$snackbarMessage."= data.message ;
			".$inProgress."=false; ".$snackbar->model." = true;
			if ( data.success == true ) {
				".$snackbarcolor." = 'success';
				".$dialogModel." = false;
				instance.changeDateRange( instance );
				instance.\$refs.%s.resetValidation();
			} else if ( data.ended == true ) {
				".__vModel('signin.signed')." = false;
			}
		},
		{
			time : new Date(".$tmSlot.").getTime()
		}
	);

}}); ", $timeslotform->ref(), $timeslotform->ref() ) ); */

$confirmsignup->attr('@click', sprintf("callAny(function(instance){ if ( instance.\$refs.%s.validate() ) {

	".$inProgress."=true; ".$snackbar->model." = false;

	request (
		".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.add_schedule').",
		'POST',
		function ( instance, request, data ) {
			".$snackbarcolor." = 'error';				
			".$snackbarMessage."= data.message ;		
			".$inProgress."=false; ".$snackbar->model." = true;
			if ( data.success == true ) {
				".$snackbarcolor." = 'success';
				".$dialogModel." = false;
				currentView = 'thankyou';
				".$downloadURL." = ".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.download')." + '?time=' + formatDate( ".$tmSlot.", 'dd-mm-yyyy HH:MM' );
				instance.delay ( () => { ".$dialogModel." = true; }, 750 );
				instance.changeDateRange( instance );
				instance.\$refs.%s.resetValidation();				
			} else if ( data.ended == true ) {
				".__vModel('signin.signed')." = false;				
			}
		},
		{
			repeatDaily : ".$repDaily.",
			repeatWeekly : ".$repWeekly.",
			schedule : ".$selectedDate.",
			time : formatDate( ".$tmSlot.", 'dd-mm-yyyy HH:MM' )
		}
	);

}}); ", $timeslotform->ref(), $timeslotform->ref() ) );

$timeslotform->child = $repeatdaily.$repeatweekly.$cancelsignup.$confirmsignup;

$timeslot = new Layout\Container( $timeslotheadline.$timeslotconfirm.$timeslotform );
$timeslot->attr('v-show', sprintf("currentView=='schedule' && %s === true ", __vModel('signin.signed') ) );
$timeslot->addClass('timeslot-container');

/* Thank you */
$thankyouheadline = new HTML\Text('Thank You!', 'h2');
$thankyoubody = new HTML\Text('Thank you for signing up to pray, <strong>{{ '.$selectedDate.' }}.</strong> We will send you a reminder 15 minutes before on the day of.', 'subtitle-1');
$addtocalendar = new Controls\Basic('button', 'Add to Calendar' );
$addtocalendar->attr( 'v-bind:href',  $downloadURL );
$addtocalendar->addClass('mt-16 mb-10');
$thankyoufooter = new HTML\Text('Works with Google Calendar, Apple Calendar and Outlook.', 'subtitle-2');
$thankyou = new Layout\Container ( $thankyouheadline.$thankyoubody.$addtocalendar.$thankyoufooter );
$thankyou->attr('v-show', sprintf("currentView=='thankyou' && %s === true ", __vModel('signin.signed') ) );

/* Account Setting */
$accountheadline = new HTML\Text('User Account Setting', 'h2');
$accountcpass = new Controls\TextField(['label' => 'Current Password'], __vModel('account.currentPass') );
$accountnpass = new Controls\TextField(['label' => 'New Password'], __vModel('account.newPass') );
$accountrpass = new Controls\TextField(['label' => 'Repeat New Password'], __vModel('account.repeatPass') );
$savepassword = new Controls\Basic('button', 'Save Changes');
$accountcpass->attrs([':rules' => $loginPassVal, 'type' => 'password']);
$accountnpass->attrs([':rules' => $passVal, 'type' => 'password']);
$accountrpass->attrs([':rules' => $passConfirm, 'type' => 'password']);

$youremail = new HTML\Text('Your email is <strong>{{ '.__vModel('account.email').' }}</strong>','subtitle-2');

$emailfield = new Controls\TextField(['label' => 'New Email' ], __vModel('account.newEmail') );
$emailfield->attrs(['v-show' => __vModel('account.editEmail') .'==true', ':rules' => $emailVal]);
$confirmemailfield = new Controls\TextField(['label' => 'Repeat New Email' ], __vModel('account.confirmEmail'));
$confirmemailfield->attrs(['v-show' => __vModel('account.editEmail') .'==true', ':rules' => $emailConfirm ]);

$saveemail = new Controls\Basic('button', 'Save Email');
$saveemail->attrs([ 'v-show' => __vModel('account.editEmail') .'==true' ] );
$changeemail = new Controls\Basic('button', 'Change Email');
$changeemail->attrs([ 'v-show' => __vModel('account.editEmail') .'==false', '@click' => __vModel('account.editEmail').'=true' ]);

$yourphone = new HTML\Text('Your phone number is <strong>{{ '.__vModel('account.phone').' }}</strong>','subtitle-2');

$phonefield = new Controls\TextField(['label' => 'New Phone Number' ], __vModel('account.newPhone') );
$phonefield->attr('v-show', __vModel('account.editPhone') .'==true' );

$savephone = new Controls\Basic('button', 'Save Phone');
$savephone->attrs([ 'v-show' => __vModel('account.editPhone') .'==true' ] );
$changephone = new Controls\Basic('button', 'Change Phone');
$changephone->attrs([ 'v-show' => __vModel('account.editPhone') .'==false', '@click' => __vModel('account.editPhone').'=true' ]);

$logoff = new Controls\Basic('button', 'Sign Out ' . new Layout\Icon('mdi-exit-to-app') );
$logoff->attrs( ['text', 'absolute', 'right', 'bottom', '@click' => "callAny(function(instance){ 
	".$inProgress."=true;
	request (
		".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.logoff').",
		'POST',
		function ( instance, request, data ) {			
			".$inProgress."=false;
			".__vModel('signin.signed')." = false;
			".__vModel('account.role')." = 'user';
		},
		{
			terminate_session : true,
		}
	);

});" ]);

$passwordform = new Form\Create( $accountcpass.$accountnpass.$accountrpass, $savepassword);
$passwordform->action->attr('@click', sprintf("callAny(function(instance){ if ( instance.\$refs.%s.validate() ) { 

	".$inProgress."=true; ".$snackbar->model." = false;

	request (
		".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.pass').",
		'POST',
		function ( instance, request, data ) {
			".$snackbarcolor." = 'error';				
			".$snackbarMessage."= data.message ;		
			".$inProgress."=false; ".$snackbar->model." = true;
			if ( data.success == true ) {
				".$snackbarcolor." = 'success';
				".__vModel('account.currentPass')." = '';				
				".__vModel('account.newPass')." = '';
				".__vModel('account.repeatPass')." = '';
				instance.\$refs.%s.resetValidation();
			} else if ( data.ended == true ) {				
				".__vModel('signin.signed')." = false;				
			}
		},
		{
			password : ".__vModel('account.currentPass').",
			newpassword : ".__vModel('account.newPass').",
			confirmpassword : ".__vModel('account.repeatPass').",			
		}
	);

}});", $passwordform->ref(), $passwordform->ref() ) );

$emailchangeform = new Form\Create($youremail.$emailfield.$confirmemailfield, $saveemail );
$emailchangeform->action->attr('@click', sprintf("callAny(function(instance){ if ( instance.\$refs.%s.validate() ) { 

	".$inProgress."=true; ".$snackbar->model." = false;

	request (
		".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.email').",
		'POST',
		function ( instance, request, data ) {
			".$snackbarcolor." = 'error';				
			".$snackbarMessage."= data.message ;		
			".$inProgress."=false; ".$snackbar->model." = true;
			if ( data.success == true ) {
				".$snackbarcolor." = 'success';
				".__vModel('account.email')." = ".__vModel('account.newEmail').";
				".__vModel('account.newEmail')." = '';				
				".__vModel('account.confirmEmail')." = '';				
				instance.\$refs.%s.resetValidation();
				".__vModel('account.editEmail')." = false;
			} else if ( data.ended == true ) {
				".__vModel('signin.signed')." = false;				
			}
		},
		{
			newEmail : ".__vModel('account.newEmail').",
			confirmEmail : ".__vModel('account.confirmEmail').",			
		}
	);

}}); ", $emailchangeform->ref(), $emailchangeform->ref() ) );

$phonechangeform = new Form\Create($yourphone.$phonefield, $savephone );
$phonechangeform->action->attr('@click', sprintf("callAny(function(instance){ if ( instance.\$refs.%s.validate() ) {

	".$inProgress."=true; ".$snackbar->model." = false;

	request (
		".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.phone').",
		'POST',
		function ( instance, request, data ) {
			".$snackbarcolor." = 'error';				
			".$snackbarMessage."= data.message ;		
			".$inProgress."=false; ".$snackbar->model." = true;
			if ( data.success == true ) {
				".$snackbarcolor." = 'success';
				".__vModel('account.phone')." = ".__vModel('account.newPhone').";
				".__vModel('account.newPhone')." = '';								
				instance.\$refs.%s.resetValidation();
				".__vModel('account.editPhone')."=false;
			} else if ( data.ended == true ) {
				".__vModel('signin.signed')." = false;				
			}
		},
		{
			newPhone : ".__vModel('account.newPhone').",			
		}
	);

}}); ", $phonechangeform->ref(), $phonechangeform->ref() ) );

$accountrow = new Layout\Row([
	new HTML\Text('Change Password', 'h5').$passwordform,
	new HTML\Text('Email', 'h5').$emailchangeform.$changeemail.
	new HTML\Text('Phone', 'h5').$phonechangeform.$changephone.$logoff
]);


$account = new Layout\Container( $accountheadline.$accountrow  );
$account->attr('v-show', sprintf("currentView=='setting' && %s === true ", __vModel('signin.signed') ) );
$account->addClass('user-account-setting-container');

/* Reminder */
$reminderform = new Form\Create('', new Controls\Basic('button', 'Save') );
$reminderform->addClass('reminder-form');
$reminderheadline = new HTML\Text('Add Message to Reminders', 'h2');
$remindermessage = new Controls\TextArea(['label' => 'Message'], $currentReminderMessage);
$remindermessage->attr(':rules', $messageVal );
$reminderDate = new Controls\DateInput ( [ 'label' => 'Reminder Date' ] , $reminderSourceDate );
$reminderDate->input->attr('v-model', $currentReminderDate);
$reminderDate->input->child = new Layout\Icon('mdi-chevron-down');
$reminderDate->input->child->attrs(['slot' => 'append', 'v-bind' => 'attrs', 'v-on' => 'on' ]);

$reminderDate->picker->attrs( [
 '@change' => sprintf( "callAny(function(instance){

	if ( ".__vModel('account.role')." == 'admin' ) { 

		".$inProgress."=true;
		".$currentReminderDate." = formatDate( ".$reminderSourceDate." , 'dddd, mmmm d, yyyy')
		".$dialogModel."=true; currentView='reminder';		

		request (
			".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.get_reminder').",
			'POST',
			function ( instance, request, data ) {
				".$snackbarcolor." = 'error';				
				".$snackbarMessage."= data.message ;		
				".$inProgress."=false; ".$snackbar->model." = true;
				if ( data.success == true ) {
					".$snackbar->model." = false;
					".$currentReminderMessage." = data.data.message;
					instance.\$refs.%s.resetValidation();					
				} else if ( data.ended == true ) {
					".__vModel('signin.signed')." = false;				
				}
			},
			{
				reminderDate : ".$reminderSourceDate."				
			}
		);
	
	}		

});", $reminderform->ref() )

] );

$reminderform->child = $reminderDate.$remindermessage;
$reminderform->action->attr('@click', sprintf("callAny(function(instance){ if ( instance.\$refs.%s.validate() ) {

	".$inProgress."=true; ".$snackbar->model." = false;

	request (
		".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.add_reminder').",
		'POST',
		function ( instance, request, data ) {
			".$snackbarcolor." = 'error';				
			".$snackbarMessage."= data.message ;		
			".$inProgress."=false; ".$snackbar->model." = true;
			if ( data.success == true ) {
				".$snackbarcolor." = 'success';
				instance.\$refs.%s.resetValidation();				
			} else if ( data.ended == true ) {
				".__vModel('signin.signed')." = false;				
			}
		},
		{
			reminderDate : ".$reminderSourceDate.",
			reminderMessage : ".$currentReminderMessage."
		}
	);

}}); ", $reminderform->ref(), $reminderform->ref() ) );



$reminder = new Layout\Container($reminderheadline.$reminderform );
$reminder->attr('v-show', sprintf("currentView=='reminder' && %s === true", __vModel('signin.signed') ) );

$progress = new Controls\Progress('linear');
$progress->attrs([
	'indeterminate',
	'color' => 'red',
	'absolute',
	'top',
	'v-show' => $inProgress.' == true '
]);


$dialog = new Layout\Dialog('','', $dialogModel, $progress.$signinup.$timeslot.$thankyou.$account.$reminder );
$dialog->attr('content-class','calendar-dialog');


/* The common dialogs - End */

/* Cog icon setting */
$setting = new Controls\Basic('button', new Layout\Icon('mdi-cog-outline') );
$setting->attrs(['text', 'icon', '@click' => $dialogModel."=true; currentView='setting'" ]);
$toolbarsetting = new Layout\Toolbar( new Layout\Spacer() . $setting );
$toolbarsetting->attr('flat');

/* The Calendar */
$calendar = new Controls\Calendar(['type' => 'week'], new Core\Model('calendarDate') );

$calendar->attrs( [
	'v-bind:interval-width' => $navwidth,
	//':first-interval' => -1, //12 am,
	//':interval-count' => 22,
]);

$sign = new Controls\Basic('button', 'Sign Up');

$sign->attrs(['@click' => sprintf( "callAny(function(instance){

	".$dialogModel."=true; currentView='schedule';

	if ( ".__vModel('signin.signed')." == true ) {
		".$inProgress."=true;
		".$repDaily." = 'No';
		".$repWeekly." = 'No';	
		".$tmSlot." = date + ' ' + time;
		".$selectedDate." = formatDate( ".$tmSlot.", 'dddd, mmmm dS, yyyy, h:MMTT' );

		request (
			".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.get_schedule').",
			'POST',
			function ( instance, request, data ) {
				".$snackbarcolor." = 'error';				
				".$snackbarMessage."= data.message ;		
				".$inProgress."=false; ".$snackbar->model." = true;
				if ( data.success == true ) {
					".$snackbar->model." = false;
					".$selectedDate." = data.data.schedule =='' || data.data.schedule == null ? ".$selectedDate." : data.data.schedule;
					".$repDaily." = data.data.daily;
					".$repWeekly." = data.data.weekly;
					".$tmSlot." = data.data.timeslot == 0 ? ".$tmSlot." : data.data.timeslot;
					instance.\$refs.%s.resetValidation();					
				} else if ( data.ended == true ) {
					".__vModel('signin.signed')." = false;				
				}
			},
			{
				time : formatDate( ".$tmSlot.", 'dd-mm-yyyy HH:MM' )
			}
		);
	}
});", $timeslotform->ref() ) ]);


$signloader = new Controls\Basic('button', $progresscalendar );
$signloader->attr('v-show', $inProgressCalendar.' == true ');

$signcount = new Controls\Basic('button', '0' );
$signcount->attrs( ['v-bind:timeslot' => "time.indexOf('-') === 0 ? null : formatDate( date +' '+ time, 'dd-mm-yyyy HH:MM' )", 'v-show' => $inProgressCalendar.' == false ']);
$signcount->addClass('sign-count-timeslot');
$signgroup = new Controls\Basic('button-group', $sign . $signcount . $signloader );
$signgroup->attrs(['round', 'dense']);
$signgroup->addClass('calendar-sign-group');

$calendar->child = new Layout\Template( $signgroup );
$calendar->child->attr('v-slot:interval', "{ hour, day, date, time }");
$calendar->addClass('prayer-calendar');
$calendar->attr('@click:date', sprintf( "( v ) => { 

callAny(function(instance){

	if ( ".__vModel('account.role')." == 'admin' ) { 

		".$inProgress."=true;
		".$reminderSourceDate." = formatDate( v.date , 'yyyy-mm-dd' );
		".$currentReminderDate." = formatDate( v.date , 'dddd, mmmm d, yyyy');
		".$dialogModel."=true; currentView='reminder';		

		request (
			".__vModel('APP_CONFIG.URL')."+'/'+".__vModel('APP_CONFIG.ENDPOINTS.get_reminder').",
			'POST',
			function ( instance, request, data ) {
				".$snackbarcolor." = 'error';				
				".$snackbarMessage."= data.message ;		
				".$inProgress."=false; ".$snackbar->model." = true;
				if ( data.success == true ) {
					".$snackbar->model." = false;
					".$currentReminderMessage." = data.data.message;
					instance.\$refs.%s.resetValidation();					
				} else if ( data.ended == true ) {
					".__vModel('signin.signed')." = false;				
				}
			},
			{
				reminderDate : ".$reminderSourceDate."				
			}
		);
	
	}		

});

}", $reminderform->ref() ) );

/* Date Navigation - top-left */

$datenav = 'date-nav';

$text = new HTML\Text('{{ '.$rangemodel.' }}', 'button');

$buttonprev = new Controls\Basic('button', new Layout\Icon('mdi-chevron-left') );
$buttonprev->attrs(['fab', 'text', 'small', '@click' => sprintf( "callAny(function(instance){ instance.\$refs.%s.prev(); instance.changeDateRange( instance ); });", $calendar->ref() ) ]);


$buttonnext = new Controls\Basic('button', new Layout\Icon('mdi-chevron-right') );
$buttonnext->attrs(['fab', 'text', 'small', '@click' => sprintf( "callAny(function(instance){ instance.\$refs.%s.next(); instance.changeDateRange( instance ); })", $calendar->ref() )]);

$nav = new Layout\Toolbar( $buttonprev . $text . $buttonnext );
$nav->attrs([
'absolute',
'flat',
':height' => 80
]);

$nav->addClass($datenav);


/* Calendar JS Events and Functions */
Core\ModelCollection::set('resizeSync', ['(e, vm) => { 
	var width = parseFloat(getComputedStyle(document.querySelector(".'.$datenav.'"), null).width.replace("px", ""));
	vm.$data.'.$navwidth.' = width;
}'], true );

Core\ModelCollection::set('changeDateRange', sprintf(" ( instance ) => { instance.".$inProgressCalendar." = true;
	instance.delay( () => { 

		var weekdates = instance.querySelector( 'document', '.prayer-calendar .v-calendar-daily__head .v-calendar-daily_head-day' );
		var startDate = instance.querySelector( weekdates[0], '.v-btn .v-btn__content', true ).innerHTML;
		var endDate = instance.querySelector( weekdates[weekdates.length-1], '.v-btn .v-btn__content', true ).innerHTML;

		var rtitle = instance.\$refs.%s.title;
		if ( rtitle.indexOf('-') > 0 ) {
			var rdates = rtitle.split('-');
			var rdate2 = new Date( rdates[1] );
			var rdate1 = new Date( rdates[0] + ( rdates[0].length <= 4 ? rdate2.getFullYear() : '' ) );
		} else {
			var rdate1 = new Date( rtitle );
			var rdate2 = rdate1;
		}
		var m1 = instance.".$months."[rdate1.getMonth()];
		var m2 = instance.".$months."[rdate2.getMonth()];
		instance.".$rangemodel." = m1 == undefined || m2 == undefined ? rtitle : m1 + ' ' + startDate + ' - ' + m2 + ' ' + endDate;	

		instance.delay( () => { instance.updateScheduleButtons( instance ); instance.resizeUpdate(false); }, 10 );
		
		
	}, 2 );
}", $calendar->ref() ), true );

Core\ModelCollection::set('updateScheduleButtons', sprintf(" ( instance ) => {
	instance.delay( () => { 

		var timeslots = instance.querySelector( 'document', '.prayer-calendar .sign-count-timeslot' );

		instance.clearDelay( instance.globalTimeout );
		instance.globalTimeout = instance.delay ( () => {
						
			var payload = [];
			
			for ( var i = 0; i < timeslots.length; i++ ) {				
				payload.push ( timeslots[i].getAttribute('timeslot') );				
			}

			instance.request (
				instance.".__vModel('APP_CONFIG.URL')."+'/'+ instance.".__vModel('APP_CONFIG.ENDPOINTS.schedules').",
				'POST',
				function ( instance, request, data ) {
					
					instance.".$hoursprayed." = 0;

					for (const [key, value] of Object.entries(data)) {
					  	try {
					  	instance.querySelector( 'document', '.prayer-calendar .sign-count-timeslot[timeslot=\'' + key + '\']', true ).querySelector('.v-btn__content').innerHTML = value;
					  	instance.".$hoursprayed." = instance.".$hoursprayed." + value;
					  	} catch (er) {	}

					}

					instance.".$inProgressCalendar." = false;
				},
				{
					allSchedules : JSON.stringify(payload)
				}
			);			

		}, 750 );	
		
	}, 2 );
}", $calendar->ref() ), true );

Core\ModelCollection::set('mountFunc', ["(vm) => { 
	vm.changeDateRange( vm );		
}"], true );

$mainheadline = new HTML\Text('{{ '.$hoursprayed.' }} Hours', 'h1');
$mainsubheadline = new HTML\Text('Are Being Prayed This Week', 'h2');
$mainsubtitle = new HTML\Text('Sign Up Below to Join Us!', 'subtitle-1');

$mainsubheadline->addClass('mainsubheadline');
$mainsubtitle->addClass('mainsubtitle');

/* The app containers */
$headlinescontainer = new Layout\Container( $mainheadline.$mainsubheadline.$mainsubtitle );
$container = new Layout\Container( $nav.$calendar.$snackbar );
$container->addClass('v-relative pa-1');
$toolbarcontainer = new Layout\Container( $toolbarsetting );
$toolbarcontainer->addClass('pa-1');

$appInstance = new Layout\App( new Layout\Main( $headlinescontainer.$toolbarcontainer.$container.$dialog ), true ); 
