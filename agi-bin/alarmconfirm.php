#!/usr/bin/env php
<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

// Bootstrap FreePBX but don't include any modules (so you won't get anything
// from the functions.inc.php files of all the modules.)
//
$restrict_mods = true;
include '/etc/freepbx.conf';
set_time_limit(0);
error_reporting(0);

// Connect to AGI:
//
require_once "phpagi.php";
$AGI = new AGI();

$lang = $AGI->request['agi_language'];
$number = $AGI->request['agi_extension'];
$nextcallfile = FreePBX::Alarmdialer()->addAlarm($number,time() + 60,$lang); // shedule now to be save

$AGI->answer();
if($lang == 'ja') {
	$digit = sim_background($AGI, "this-is-yr-wakeup-call&wakeup-menu","1",1);
} else { // Default back to English if channel doesn't match other languages
	$digit = sim_background($AGI, "hello&this-is-yr-wakeup-call&to-cancel-wakeup&vm-press&digits/1","1",1);
}
switch($digit) {
	case 1:
		FreePBX::Alarmdialer()->removeAlarm($nextcallfile);
		if($lang == 'ja') {
			sim_playback($AGI, "wakeup-call-cancelled");
		} else {
			sim_playback($AGI, "wakeup-call-cancelled");
		}
	break;
}
sim_playback($AGI, "goodbye");
$AGI->hangup();

/**
 * Simulate playback functionality like the dialplan
 * @param  object $AGI  The AGI Object
 * @param  string $file Audio files combined by/with '&'
 */
function sim_playback($AGI, $file) {
	$files = explode('&',$file);
	foreach($files as $f) {
		$AGI->stream_file($f);
	}
}

/**
 * Simulate background playback with added functionality
 * @param  object  $AGI      The AGI Object
 * @param  string  $file     Audio files combined by/with '&'
 * @param  string  $digits   Allowed digits (if we are prompting for them)
 * @param  string  $length   Length of allowed digits (if we are prompting for them)
 * @param  string  $escape   Escape character to exit
 * @param  integer $timeout  Timeout
 * @param  integer $maxLoops Max timeout loops
 * @param  integer $loops    Total loops
 */
function sim_background($AGI, $file,$digits='',$length='1',$escape='#',$timeout=10000, $maxLoops=1, $loops=0) {
	$files = explode('&',$file);
	$number = '';
	$lang = $AGI->request['agi_language'];
	foreach($files as $f) {
		$ret = $AGI->stream_file($f,$digits);
		if($ret['code'] == 200 && $ret['result'] != 0) {
			$number .= chr($ret['result']);
		}
		if(strlen($number) >= $length) {
			break;
		}
	}
	if(trim($digits) != '' && strlen($number) < $length) {
		while(strlen($number) < $length && $loops < $maxLoops) {
			$ret = $AGI->wait_for_digit($timeout);
			if($loops > 0) {
				sim_playback($AGI, "please-try-again");
			}
			if($ret['code'] == 200 && $ret['result'] == 0) {
				if($lang == 'ja') {
					sim_playback($AGI, "you-entered-bad-digits");
				} else {
					sim_playback($AGI, "you-entered&bad&digits");
				}
			} elseif($ret['code'] == 200) {
				$digit = chr($ret['result']);
				if($digit == $escape) {
					break;
				}
				if(strpos($digits,$digit) !== false) {
					$number .= $digit;
					continue; //dont count loops as we are good
				} else {
					if($lang == 'ja') {
						sim_playback($AGI,"you-entered-bad-digits");
					} else {
						sim_playback($AGI,"you-entered&bad&digits");
					}
				}
			} else {
				sim_playback($AGI,"an-error-has-occurred");
			}
			$loops++;
		}
	}
	$number = trim($number);
	return $number;
}
