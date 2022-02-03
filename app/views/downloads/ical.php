<?php

$start = date("Ymd\THis", $model->timestamp );
$end = date("Ymd\THis", ( $model->timestamp ) + 60*60 );

$ics_data = "BEGIN:VCALENDAR\n";
$ics_data .= "VERSION:2.0\n";
$ics_data .= "METHOD:PUBLISH\n";

/* TIMEZONE FIX -- START */

$type = $savingtime == 'PDT' ? 'DAYLIGHT' : 'STANDARD';
$ics_data .= "BEGIN:VTIMEZONE\n";
$ics_data .= "TZID:$timezone\n";
$ics_data .= "X-LIC-LOCATION:$timezone\n";
$ics_data .= "BEGIN:$type\n";
$ics_data .= "TZOFFSETFROM:$offset\n";
$ics_data .= "TZOFFSETTO:$offset\n";
$ics_data .= "TZNAME:$savingtime\n";
$ics_data .= "DTSTART:$start\n";
$ics_data .= "DTEND:$end\n";

if ( !empty($freq) ) {
	$ics_data .= "RRULE:FREQ=$freq;WKST=SU\n";
}

$ics_data .= "END:$type\n";
$ics_data .= "END:VTIMEZONE\n";

/* TIMEZONE FIX -- END */

$ics_data .= "BEGIN:VEVENT\n";
$ics_data .= "DTSTART;TZID=$timezone:$start\n";
$ics_data .= "DTEND;TZID=$timezone:$end\n";
$ics_data .= "TRANSP: OPAQUE\n";
$ics_data .= "SEQUENCE:0\n";

if ( !empty($freq) ) {
	$ics_data .= "RRULE:FREQ=$freq;WKST=SU\n";
}

$ics_data .= "UID:pray-event@".$_SERVER['SERVER_NAME']."\n";
$ics_data .= "DTSTAMP:".date("Ymd\THis\Z")."\n";
$ics_data .= "SUMMARY:Prayer Event for ".$model->date."\n";
$ics_data .= "TZID:$timezone\n";
$ics_data .= "DESCRIPTION:".str_replace(array("\r", "\n"), '', $reminder->reminder )."\n";
$ics_data .= "PRIORITY:1\n";
$ics_data .= "CLASS:PUBLIC\n";
$ics_data .= "BEGIN:VALARM\n";
$ics_data .= "TRIGGER:-PT15M\n";
$ics_data .= "ACTION:DISPLAY\n";
$ics_data .= "END:VALARM\n";
$ics_data .= "END:VEVENT\n";
$ics_data .= "END:VCALENDAR\n";

echo $ics_data;
