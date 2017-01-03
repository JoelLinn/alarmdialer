<?php
out("Installing Alarm Dialer");
// list of the columns that need to be included in the alarmdialer table.  Add/subract values to this list and trigger a reinstall to alter the table
// this table is used to store module config info
$cols['maxretries'] = "INT NOT NULL";
$cols['waittime'] = "INT NOT NULL";
$cols['retrytime'] = "INT NOT NULL";
$cols['extensionlength'] = "INT NOT NULL";
$cols['cid'] = "VARCHAR(30)";
$cols['cnam'] = "VARCHAR(30)";
$cols['destination'] = "VARCHAR(30)";
//new config table columns
$cols['application'] = "VARCHAR(30)";
$cols['data'] = "VARCHAR(30)";

// list of columns that need to be in the alarmdialer_calls table.  Add/subract values to this list and trigger a reinstall to alter the table
// this table is used to store scheduled calls info
$sc_cols['time'] = "INT NOT NULL";
$sc_cols['ext'] = "INT NOT NULL";
$sc_cols['maxretries'] = "INT NOT NULL";
$sc_cols['retrytime'] = "INT NOT NULL";
$sc_cols['waittime'] = "INT NOT NULL";
$sc_cols['cid'] = "VARCHAR(30)";
$sc_cols['cnam'] = "VARCHAR(30)";
$sc_cols['application'] = "VARCHAR(30)";
$sc_cols['data'] = "VARCHAR(30)";
$sc_cols['tempdir'] = "VARCHAR(100)";
$sc_cols['outdir'] = "VARCHAR(100)";
$sc_cols['filename'] = "VARCHAR(100)";
$sc_cols['frequency'] = "INT NOT NULL";


// create the alarmdialer table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS alarmdialer (";
foreach($cols as $key=>$val)
{
	$sql .= $key.' '.$val.', ';
}
$sql .= "PRIMARY KEY (maxretries))";
$check = $db->query($sql);
if (DB::IsError($check))
{
	die_freepbx( "Can not create alarmdialer table: ".$sql." - ".$check->getMessage() .  "<br>");
}

// create the alarmdialer_calls table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS alarmdialer_calls (";
foreach($sc_cols as $key=>$val)
{
	$sql .= $key.' '.$val.', ';
}
$sql .= "PRIMARY KEY (time))";
$check = $db->query($sql);
if (DB::IsError($check))
{
	die_freepbx( "Can not create alarmdialer_calls table: ".$sql." - ".$check->getMessage() .  "<br>");
}

//check status of exist columns in the hotelwakup table and change/drop as required
$curret_cols = array();
$sql = "DESC alarmdialer";
$res = $db->query($sql);
while($row = $res->fetchRow())
{
	if(array_key_exists($row[0],$cols))
	{
		$curret_cols[] = $row[0];
		//make sure it has the latest definition
		$sql = "ALTER TABLE alarmdialer MODIFY ".$row[0]." ".$cols[$row[0]];
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_freepbx( "Can not update column ".$row[0].": " . $check->getMessage());
		}
	}
	else
	{
		//remove the column
		$sql = "ALTER TABLE alarmdialer DROP COLUMN ".$row[0];
		$check = $db->query($sql);
		if(DB::IsError($check))
		{
			die_freepbx( "Can not remove column ".$row[0].": " . $check->getMessage());
		}
		else
		{
			out('Removed no longer needed column '.$row[0].' from alarmdialer table.');
		}
	}
}
//add missing columns to the alarmdialer table
foreach($cols as $key=>$val)
{
	if(!in_array($key,$curret_cols))
	{
		$sql = "ALTER TABLE alarmdialer ADD ".$key." ".$val;
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_freepbx( "Can not add column ".$key.": " . $check->getMessage());
		}
		else
		{
			out('Added column '.$key.' to alarmdialer table');
		}
	}
}

//check status of exist columns in the alarmdialer_calls table and change/drop as required
$sc_curret_cols = array();
$sql = "DESC alarmdialer_calls";
$res = $db->query($sql);
while($row = $res->fetchRow())
{
	if(array_key_exists($row[0],$sc_cols))
	{
		$sc_curret_cols[] = $row[0];
		//make sure it has the latest definition
		$sql = "ALTER TABLE alarmdialer_calls MODIFY ".$row[0]." ".$sc_cols[$row[0]];
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_freepbx( "Can not update column ".$row[0].": " . $check->getMessage() .  "<br>");
		}
	}
	else
	{
		//remove the column
		$sql = "ALTER TABLE alarmdialer_calls DROP COLUMN ".$row[0];
		$check = $db->query($sql);
		if(DB::IsError($check))
		{
			die_freepbx( "Can not remove column ".$row[0].": " . $check->getMessage() .  "<br>");
		}
		else
		{
			out('Removed no longer needed column '.$row[0].' from alarmdialer_calls table');
		}
	}
}
//add missing columns to the alarmdialer_calls table
foreach($sc_cols as $key=>$val)
{
	if(!in_array($key,$sc_curret_cols))
	{
		$sql = "ALTER TABLE alarmdialer_calls ADD ".$key." ".$val;
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_freepbx( "Can not add column ".$key.": " . $check->getMessage() .  "<br>");
		}
		else
		{
			out('Added column '.$key.' to alarmdialer_calls table');
		}
	}
}

//  Set default values - need mechanism to prevent overwriting existing values 
out("Installing Default Values");
$sql ="INSERT INTO alarmdialer (maxretries, waittime, retrytime, cnam,             cid,    destination, extensionlength, application, data) ";
$sql .= "               VALUES ('1000',     '20',     '5',       'Alarm Dialer',   '*67',  '7777',      '4',             'AGI',       'alarmconfirm.php')";

$check = $db->query($sql);

//  Removed the following check because it prevents install if the query above fails to overwrite existing values.
//if (DB::IsError($check)) {
//        die_freepbx( "Can not create default values in `alarmdialer` table: " . $check->getMessage() .  "\n");
//}

// Register FeatureCode - Alarm Dialer;
$fcc = new featurecode('alarmdialer', 'alarmdialer');
$fcc->setDescription('Alarm Dialer');
$fcc->setDefault('*67');
$fcc->update();
unset($fcc);
