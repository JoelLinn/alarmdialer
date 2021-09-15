<?php
namespace FreePBX\modules;
/*
 * Class stub for BMO Module class
 * In _Construct you may remove the database line if you don't use it
 * In getActionbar change "modulename" to the display value for the page
 * In getActionbar change extdisplay to align with whatever variable you use to decide if the page is in edit mode.
 *
 */
class Alarmdialer implements \BMO {
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}
		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
	}
	public function install() {}
	public function uninstall() {}
	public function backup() {}
	public function restore($backup) {}
	public function doConfigPageInit($page) {}

	public function getActionBar($request) {
		$buttons = array();
		switch($request['display']) {
			case 'alarmdialer':
				$buttons = array(
					'reset' => array(
						'name' => 'reset',
						'id' => 'reset',
						'value' => _('Reset'),
						'class' => 'hidden'
					),
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _('Submit'),
						'class' => 'hidden'
					)
				);
			break;
		}
		return $buttons;
	}

	public function ajaxRequest($req, &$setting) {
		switch($req) {
			case "savecall":
			case "getable":
				return true;
			break;
		}
		return false;
	}

	public function ajaxHandler() {
		switch($_REQUEST['command']) {
			case "savecall":
				if(empty($_POST['language'])) {
					$lang = 'en'; //default to English if empty
				} else {
					$lang = $_POST['language']; //otherwise set to the language code provided
				}
				if(empty($_POST['day']) || empty($_POST['time'])) {
					return array("status" => false, "message" => _("Cannot schedule the call, due to insufficient data"));
				}
				$time_wakeup = strtotime($_POST['day']." ".$_POST['time']);
				$time_now = time();
				$badtime = false;
				if ( $time_wakeup === false || $time_wakeup <= $time_now )  {
					$badtime = true;
				}

				// check for insufficient data
				if ($badtime)  {
					// abandon .call file creation and pop up a js alert to the user
					return array("status" => false, "message" => sprintf(_("Cannot schedule the call the scheduled time is in the past. [Time now: %s] [Alarm Time: %s]"),date(DATE_RFC2822,$time_now),date(DATE_RFC2822,$time_wakeup)));
				} else {
					$this->addAlarm($_POST['destination'],$time_wakeup,$lang);
					return array("status" => true);
				}
			break;
			case "getable":
				return $this->getAllCalls();
			break;
		}
		return true;
	}

	public function addAlarm($destination, $time, $lang) {
		$date = $this->getConfig();  // module config provided by user
		return $this->generateCallFile(array(
			"time"  => $time,
			"date" => 'unused',
			"ext" => $destination,
			"language" => $lang,
			"maxretries" => $date['maxretries'],
			"retrytime" => $date['retrytime'],
			"waittime" => $date['waittime'],
			"callerid" => $date['cnam']." <".$date['cid'].">",
			"application" => 'AGI',
			"data" => 'alarmconfirm.php',
		));
	}

	public function showPage() {
		if(!empty($_REQUEST['action']) && $_REQUEST['action'] == "delete" && !empty($_REQUEST['id']) && !empty($_REQUEST['ext'])) {
			$file = $this->FreePBX->Config->get('ASTSPOOLDIR')."/outgoing/alarmdialer.".$_REQUEST['id'].".ext.".$_REQUEST['ext'].".call";
			if(file_exists($file)) {
				unlink($file);
			}
		}
		$action = !empty($_POST['action']) ? $_POST['action'] : '';
		$fcc = new \featurecode('alarmdialer', 'alarmdialer');
		$code = $fcc->getCode();
		switch($action) {
			case "settings":
				preg_match('/"(.*)" <(.*)>/',$_POST['callerid'],$matches);
				$this->saveConfig(array(
					"extensionlength" => $_POST['extensionlength'],
					"waittime" => $_POST['waittime'],
					"retrytime" => $_POST['retrytime'],
					"maxretries" => $_POST['maxretries'],
					"cid" => !empty($matches[2]) ? $matches[2] : $code,
					"cnam" => !empty($matches[1]) ? $matches[1] : _("Alarm Dialer"),
					"destination" => $_POST['destination']
				));
			break;
		}
		$content = load_view(__DIR__."/views/grid.php", array("code" => $code, "config" => $this->getConfig()));
		return load_view(__DIR__."/views/main.php",array("pageContent" => $content));
	}

	public function getConfig() {
		$sql = "SELECT * FROM alarmdialer LIMIT 1";
		$sth = $this->db->prepare($sql);
		$sth->execute();
		$fa = $sth->fetch(\PDO::FETCH_ASSOC);
		$fa['callerid'] = '"'.$fa['cnam'].'" <'.$fa['cid'].'>';
		return $fa;
	}

	public function saveConfig($options) {
		if(empty($options)) {
			return false;
		}
		$sql = "UPDATE `alarmdialer` SET `maxretries` = ?, `waittime` = ?, `retrytime` = ?, `extensionlength` = ?, `cnam` = ?, `cid` = ?, `destination`= ? LIMIT 1";
		$sth = $this->db->prepare($sql);
		return $sth->execute(array($options['maxretries'], $options['waittime'], $options['retrytime'], $options['extensionlength'], $options['cnam'], $options['cid'], $options['destination']));
	}

	public function CheckAlarmProp($file) {
		$myresult = '';
		$file = basename($file);
		$WakeUpTmp = explode(".", $file);
		$myresult = null;
		if(!empty($WakeUpTmp[1]) && !empty($WakeUpTmp[3])) {
			$myresult[0] = $WakeUpTmp[1];
			$myresult[1] = $WakeUpTmp[3];
		}
		return $myresult;
	}

	public function removeAlarm($file) {
		$file = basename($file);
		if(file_exists($this->FreePBX->Config->get('ASTSPOOLDIR')."/outgoing/".$file)) {
			unlink($this->FreePBX->Config->get('ASTSPOOLDIR')."/outgoing/".$file);
		}
		return true;
	}

	public function getAllCalls() {
		$calls = array();
		foreach(glob($this->FreePBX->Config->get('ASTSPOOLDIR')."/outgoing/alarmdialer*.call") as $file) {
			$res = $this->CheckAlarmProp($file);
			if(!empty($res)) {
				$filedate = date('M d Y',filemtime($file)); //create a date string to display from the file timestamp
				$filetime = date('H:i',filemtime($file));   //create a time string to display from the file timestamp
				$wucext = $res[1];
				$calls[] = array(
					"filename" => basename($file),
					"timestamp" => filemtime($file),
					"time" => $filetime,
					"date" => $filedate,
					"destination" => $wucext,
					"actions" => '<a href="?display=alarmdialer&amp;action=delete&amp;id='.$res[0].'&amp;ext='.$res[1].'"><i class="fa fa-times"></i></a>'
				);
			}
		}
		return $calls;
	}

	public function generateCallFile($foo) {
		if (empty($foo['tempdir'])) {
			$ast_tmp_path = $this->FreePBX->Config->get('ASTSPOOLDIR')."/tmp/";
			if(!file_exists($ast_tmp_path)) {
							mkdir($ast_tmp_path,0777,true);
			}
			$foo['tempdir'] = $ast_tmp_path;
		}
		if (empty($foo['outdir'])) {
			$foo['outdir'] = $this->FreePBX->Config->get('ASTSPOOLDIR')."/outgoing/";
		}

		$foo['ext'] = preg_replace("/[^\d@\+\#]/","",$foo['ext']);
		if (empty($foo['filename'])) {
			$foo['filename'] = "alarmdialer.".$foo['time'].".ext.".$foo['ext'].".call";
		}

		$foo['filename'] = basename($foo['filename']);

		$tempfile = $foo['tempdir'].$foo['filename'];
		$outfile = $foo['outdir'].$foo['filename'];

		// Delete any old alarmdialer .call file
		foreach(glob($this->FreePBX->Config->get('ASTSPOOLDIR')."/outgoing/alarmdialer*.call") as $file) {
			unlink($file);
		}

		// Create up a .call file, write and close
		$wuc = fopen($tempfile, 'w');
		fputs( $wuc, "channel: Local/".$foo['ext']."@originate-skipvm\n" );
		fputs( $wuc, "maxretries: ".$foo['maxretries']."\n");
		fputs( $wuc, "retrytime: ".$foo['retrytime']."\n");
		fputs( $wuc, "waittime: ".$foo['waittime']."\n");
		fputs( $wuc, "callerid: ".$foo['callerid']."\n");
		fputs( $wuc, 'set: CHANNEL(language)='.$foo['language']."\n");
		fputs( $wuc, "application: ".$foo['application']."\n");
		fputs( $wuc, "data: ".$foo['data']."\n");
		fputs( $wuc, "archive: yes\n");
		fclose( $wuc );

		// set time of temp file and move to outgoing
		touch( $tempfile, $foo['time'], $foo['time'] );
		rename( $tempfile, $outfile );

		return $outfile;
	}
}
