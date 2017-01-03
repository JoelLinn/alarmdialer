<?php
print "Alarm Dialer is being uninstalled.<br>";

// drop the alarmdialer table
$sql = "DROP TABLE IF EXISTS alarmdialer";

$check = $db->query($sql);
if (DB::IsError($check)) {
        die_freepbx( "Can not delete `alarmdialer` table: " . $check->getMessage() .  "\n");
}

// drop the alarmdialer_calls table
$sql = "DROP TABLE IF EXISTS alarmdialer_calls";

$check = $db->query($sql);
if (DB::IsError($check)) {
        die_freepbx( "Can not delete `alarmdialer_calls` table: " . $check->getMessage() .  "\n");
}

// Consider adding code here to scan thru the spool/asterisk/outgoing directory and removing 
// already wakeup calls that have been scheduled

?>