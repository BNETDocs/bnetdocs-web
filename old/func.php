<?
	########################
	# Core Functions Script
	########################

	# Block Direct Access Attempts
	# -------------------------------
	global $auth;
	if($auth != 'true') die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
	
	# DisplayDialogBox Function
	# --------------------
	#
	# Function Name: DisplayDialogBox
	# Purpose: Figure it out.
	# Variable Uses:
	#
	# $text = The text to display as contents of the dialog box; Required; String.
	# $poster = The poster of who posted dialog; Optional; String.
	# $title = The title of the dialog box; Optional; String.
	# $insdate = Inserts date automatically; Optional; Boolean.
	# $file = Determines whether $text is treated as an include file; Optional; Boolean.
	#
	# --------------------
		function DisplayDialogBox($text, $title='none', $file=false, $align='left'){
			include 'bdif/dialogbox.dm';
		}
	#-------------
	# End of DisplayDialogBox Function
	#-------------



	# DisplayNavBox Function
	# --------------------
	#
	# Function Name: DisplayNavBox
	# Purpose: Figure it out.
	# Variable Uses:
	#
	# $text = The text to display as contents of the nav box; Required; String.
	# $title = The title of the dialog box; Optional; String.
	# $file = Determines whether $text is treated as an include file; Optional; Boolean.
	#
	# --------------------
		function DisplayNavBox($text, $title="none", $file=false){
			$auth = true;
			$username = $_SESSION['username'];
			include 'dmti/navbox.dm';
		}
	#-------------
	# End of DisplayNavBox Function
	#-------------

	function getdir($dir, $no_dots=TRUE, $no_dirs=TRUE) {
		$files = array();
		$dh  = @opendir($dir);
		if ($dh!=FALSE) {
			while (false !== ($filename = readdir($dh))) {
				$files[] = $filename;
			}
		
			if ($no_dots) {
				while(($ix = array_search('.',$files)) > -1) unset($files[$ix]);
				while(($ix = array_search('..',$files)) > -1) unset($files[$ix]);
				$li=0;
				while($li <= count($files)){
					$first = $str{0};
					if($files[$li]{0}=='.') unset($files[$li]);
					if($files[$li]{0}.$files[$li]{1}=='..') unset($files[$li]);
					$li++;
				}

			}

			if ($no_dirs) {
				$li=0;
				while($li <= count($files)){
					if(is_dir($files[$li])) unset($files[$li]);
					$li++;
				}
			}
			$li=0;
			while($li <= count($files)){
				if(trim($files[$li])=='') unset($files[$li]);
				$li++;
			}

			sort($files);
		}
	
		return $files;
	}

	function MsgBox($message){
		if(!$message) $message='Error: $message variable is empty.';
		echo '
			<SCRIPT LANGUAGE="javascript">
			<!--
				function MYALERT() {
					alert("'.$message.'");
				} 
				MYALERT() 
			<!-- END -->
			</SCRIPT>';
	}

	function createRandomPassword() {
	
	    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
	    srand((double)microtime()*1000000);
	    $i = 0;
	    $pass = '' ;
	
	    while ($i <= 7) {
	        $num = rand() % 33;
	        $tmp = substr($chars, $num, 1);
	        $pass = $pass . $tmp;
	        $i++;
	    }
	
	    return $pass;
	
	}

	# Login Function
	# --------------------
	#
	# Function Name: login
	# Variable Uses:
	#
	# $username = member user name
	# $password = member's password
	# $tracked = determines if login is logged.
	#
	# --------------------
		function login($username, $password, $tracked=true, $ip) {

			# Set up pass variable
			#-------------
		
				$ret = "pass";
			
			# Make sure we have data for user name and pw, if not, error out.
			#-------------

				if((!$username) || (!$password)){
					$ret = "Missing login information.";
				}

			# Check database to verify login info
			#-------------

			if(FieldVerify("username", "users", $username)){
				$userid = GetInfo("users", "username", $username, "id");
				$pwtocheck = md5($password);
				$password = GetInfo("users", "id", $userid, "password");
			} else {
				$ret = "You're not a registered user of BNETDocs: Redux.";
			}

			if($ret != "pass"){
				return $ret;
			}
			
			if($pwtocheck == $password){

				# Login has been verified, get basic info from DB on member.
				#-------------

					$username = GetInfo("users", "id", $userid, "username");

				# If tracking is enabled, log IP and login time/date.
				#-------------

					$ip = $_SERVER["REMOTE_ADDR"];
					$sql = "UPDATE users SET ip=\"$ip\" WHERE username=\"$username\"";
					if($tracked == true) $sqlresults = mysql_query($sql) or die (mysql_error());
					if($tracked == true) mysql_query("UPDATE users SET last_login=now() WHERE username='$username'");

				# Set up session for member
				#-------------

					CreateSession($userid, $ip);

				#-------------
				# Done setting up session
				#-------------

			} else {

				# Invalid login info. Error out.
				#-------------

				$ret = "<br /><center>Invalid Login!</center>";
			}
			
			return $ret;
		}
	#-------------
	# End of Login Function
	#-------------

	function error($message){
		DisplayDialogBox('<br><br><br><br><br><br><br><center><font size=5>'.$message.'</font></center><br><br><br><br><br><br><br>', '<font size=3><center>Error</center></font>');
	}
	
	
	
	# FieldVerify Function
	# --------------------
	#
	# Function Name: FieldVerify
	# Purpose: Checks to see if a given field containing specified data exists
	# Variable Uses:
	#
	# $table = Target table to access
	# $field = Field to check in
	# $data = Data of whatever you're verifying
	#
	# --------------------
		function FieldVerify($field, $table, $data){
			
			$sql = "SELECT COUNT(*) AS count FROM `$table` WHERE `$field`=\"$data\"";

			$testercount = mysql_query($sql) or die("mysql error in $sql: ".mysql_error());
			$testercount = mysql_fetch_object($testercount);
			$testers = $testercount->count;
			
			if($testers > 0){
				return true;
			}
			return false;
		}
	#-------------
	# End of FieldVerify Function
	#-------------

	
	# Sector Security Function
	# --------------------
	#
	# Function Name: Secure
	# Variable Uses: None.
	#
	# --------------------
		function secure($ip) {

			# Check to see if session exists.
			#-------------
			
			if(!FieldVerify("ipaddress", "sessions", $ip)){
				Redirect("/?op=login");
			}
			
			# Update Activity
			#------------
			
			$userid = GetInfo("sessions", "ipaddress", $ip, "id");
			mysql_query("UPDATE users SET last_login=now() WHERE id='$userid'");
		}

	#-------------
	# End of Sector Security Function
	#-------------



	# GetData Function
	# --------------------
	#
	# Function Name: GetData
	# Purpose: Makes it easier to obtain a member's info.
	# Variable Uses:
	#
	# $whoid = target member for info request
	# $what = which field data to pull up
	#
	# --------------------
		function GetData($whoid, $what){
			$ret = GetInfo("users", "id", $whoid, $what);
			return $ret;
		}
	#-------------
	# End of GetData Function
	#-------------

	# WriteData Function
	# --------------------
	#
	# Function Name: WriteData
	# Purpose: Makes it easier to update a member's info.
	# Variable Uses:
	#
	# $whoid = target member for info update
	# $what = which field data to modify (field *MUST* exist)
	#$data = the data to write
	#
	# --------------------
		function WriteData($whoid, $what, $data){
			$ret = true;
			$sql = "UPDATE users SET `$what`=\"$data\" WHERE id=$whoid"; 
			$sqlresults = mysql_query($sql) or $ret = false;
			return $ret;
		}
	#-------------
	# End of WriteData Function
	#-------------
	
	
	
	function Discipline($target, $action, $ip=''){
		if($action == 'lp'){
			$sql = "UPDATE discipline SET profile=0 WHERE account=$target";
			$sqlresults = mysql_query($sql) or die(mysql_error());
		} else if($action == 'up'){
			$sql = "UPDATE discipline SET profile=1 WHERE account=$target";
			$sqlresults = mysql_query($sql) or die(mysql_error());
		} else if($action == 'fa'){
			$sql = "UPDATE discipline SET frozen=1 WHERE account=$target";
			$sqlresults = mysql_query($sql) or die(mysql_error());
		} else if($action == 'ufa'){
			$sql = "UPDATE discipline SET frozen=0 WHERE account=$target";
			$sqlresults = mysql_query($sql) or die(mysql_error());
		} else if($action == 'ipban'){
			$sql = "UPDATE discipline SET ipban=1 WHERE account=$target";
			$sqlresults = mysql_query($sql) or die(mysql_error());
			$currentip = GetData($target, 'ip');
			mysql_query("INSERT INTO `banned` (id, ip, bantype) VALUES ($target, '$ip', 'total')") or die("Discipline Function Error: ".mysql_error());
		} else if($action == 'unipban'){
			$sql = "UPDATE discipline SET ipban=0 WHERE account=$target";
			$sqlresults = mysql_query($sql) or die(mysql_error());
			$sql = "DELETE FROM banned WHERE id=\"$target\"";
			$sqlresults = mysql_query($sql) or die (mysql_error());
		}
	}
	
	function BlockHack(){
		die('<center><br><br><b><font color=red>Unauthorized access blocked. Nice try, buddy.</font></b><br><br></center>');
	}
	
	# GetInfo Function
	# --------------------
	#
	# Function Name: GetInfo
	# Purpose: Offers specialized information by specifying data request
	# Variable Uses:
	#
	# $db = Target database to access
	# $index = Field to correlate $id from.
	# $id = ID/ACCOUNT/USERNAME of whatever you're accessing
	# $data = The field where you want to obtain the data from.
	# --------------------
		function GetInfo($db, $index, $id, $data){
			$sqlquery = mysql_query("SELECT * FROM `$db` WHERE `$index`=\"$id\" LIMIT 1") or die('Attempted Query: '."SELECT * FROM `$db` WHERE `$index`='$id' LIMIT 1"."<br>GetInfo Function Error: ".mysql_error());;
			if(mysql_num_rows($sqlquery) > 0) {
				$ret = mysql_result($sqlquery,0,$data);
			} else {
				$ret = false;
			}
			return $ret;
		}
	#-------------
	# End of GetInfo Function
	#-------------

	# WriteInfo Function
	# --------------------
	#
	# Function Name: WriteInfo
	# Purpose: Writes specialized information by specifying data request to existing field
	# Variable Uses:
	#
	# $db = Target database to access
	# $index = Field to correlate $id from.
	# $id = ID/ACCOUNT/USERNAME of whatever you're accessing
	# $field = The field where you want to write the data to.
	#$data = the data you'd like to have written
	# Example: WriteInfo('messages', 'id', $id, 'opened', 1);
	# --------------------
		function WriteInfo($db, $index, $id, $field, $data){
			$ret = true;
			$sql = "UPDATE `$db` SET `$field`=\"$data\" WHERE $index=$id"; 
			$sqlresults = mysql_query($sql) or $ret = false;
			return $ret;
		}
	#-------------
	# End of GetInfo Function
	#-------------

	# URLButton Function
	# --------------------
	#
	# Function Name: URLButton
	# Purpose: A form button acts as a hyperlink
	# Variable Uses:
	#
	# $text = Text to display on button
	# $url = Target address to send to when clicked
	#
	# --------------------
		function URLButton($text,$url){
			?><form><input id="abutton" style="width:125;height:23" type=button value="<? echo $text ?>" onClick="gourl('<? echo $url ?>')"></form><?
		}
	#-------------
	# End of URLButton Function
	#-------------

	

	# Redirect Function
	# --------------------
	#
	# Function Name: Redirect
	# Purpose: Redirects user to target page
	# Variable Uses:
	#
	# $page = Target page to redirect to
	#
	# --------------------
		function Redirect($page){
			echo "<meta http-equiv=\"refresh\" content=\"0;url=$page\"><body bgcolor=black>";
			die();
		}
	#-------------
	# End of Redirect Function
	#-------------
	
	if (!function_exists("stripos")) {
	  function stripos($str,$needle,$offset=0)
	  {
	      return strpos(strtolower($str),strtolower($needle),$offset);
	  }
	}

	# Email Function
	# --------------------
	#
	# Function Name: Email
	# Purpose: Send emails
	# Variable Uses:
	#
	# $target = email address to send the email to
	# $fromwho = return address
	# $subject = obvious...
	# $body = obvious...
	#
	# --------------------
		function Email($target, $fromwho, $subject, $body){
			$headers = "From: BNETDocs<$fromwho>\r\n";
			mail($target, $subject, $body, $headers);
		}
	#-------------
	# End of Email Function
	#-------------
	
	#Applies nl2br to $text, but also makes sure to avoid applying the function to any content within pre tags
	function nl2brex($text) {
		$text = nl2br($text);
		$text = preg_replace('!(<pre.*?>)(.*?)</pre>!ise', " ('$1') .  (clean_pre('$2'))  . '</pre>' ", $text);
		$text = preg_replace('!(<ol.*?>)(.*?)</ol>!ise', " ('$1') .  (clean_pre('$2'))  . '</ol>' ", $text);
		$text = preg_replace('!(<ul.*?>)(.*?)</ul>!ise', " ('$1') .  (clean_pre('$2'))  . '</ul>' ", $text);
		$text = preg_replace('!(<dl.*?>)(.*?)</dl>!ise', " ('$1') .  (clean_pre('$2'))  . '</dl>' ", $text);
		$text = preg_replace('!(\[nobr.*?\])(.*?)\[/nobr\]!ise', " stripslashes('$1') .  stripslashes(clean_pre('$2'))  . '' ", $text);
		$text = brfix($text);
		$text = str_ireplace('[nobr]', '', $text);
		$text = str_ireplace('[/nobr]', '', $text);
		return $text;
	}
	
	#nl2brex2 for RSS feeds
	function nl2brex2($text) {
		$text = preg_replace('!(<pre.*?>)(.*?)</pre>!ise', " ('$1') .  (clean_pre('$2'))  . '</pre>' ", $text);
		$text = preg_replace('!(<ol.*?>)(.*?)</ol>!ise', " ('$1') .  (clean_pre('$2'))  . '</ol>' ", $text);
		$text = preg_replace('!(<ul.*?>)(.*?)</ul>!ise', " ('$1') .  (clean_pre('$2'))  . '</ul>' ", $text);
		$text = preg_replace('!(<dl.*?>)(.*?)</dl>!ise', " ('$1') .  (clean_pre('$2'))  . '</dl>' ", $text);
		$text = preg_replace('!(\[nobr.*?\])(.*?)\[/nobr\]!ise', " stripslashes('$1') .  stripslashes(clean_pre('$2'))  . '' ", $text);
		$text = brfix($text);
		$text = str_ireplace('[nobr]', '', $text);
		$text = str_ireplace('[/nobr]', '', $text);
		return $text;
	}
	
	# Workaround for the br tag after a closing tag fix
	function brfix($text){
		$text = str_ireplace('</dl><br />', '</dl>', $text);
		$text = str_ireplace('</dd><br />', '</dd>', $text);
		$text = str_ireplace('</dt><br />', '</dt>', $text);
		$text = str_ireplace('</ol><br />', '</ol>', $text);
		$text = str_ireplace('</ul><br />', '</ul>', $text);
		$text = str_ireplace('</li><br />', '</li>', $text);
		$text = str_ireplace('</p><br />', '</p>', $text);
		#$tagstoclean = array('dl', 'dd', 'dt', 'ol', 'ul', 'li', 'p');
		#for($i = 0; $i <= count($tagstoclean), $i++;){
		#	$text = str_ireplace('</'.$tagstoclean[$i].'><br />', '</'.$tagstoclean[$i].'>', $text);
		#}
		return $text;
	}
	
	// Remove paragraphs and breaks from within any <pre> tags.
	function clean_pre($text) {
		$text = str_replace(array("<br />\r\n", "<br />\r", "<br />\n"), "\n", $text);
		$text = str_replace('<p>', "\n", $text);
		$text = str_replace('</p>', '', $text);
		return $text;
	}
	
	
	function totalpurgehtml($source){
		$allowedTags='';
		$source = strip_tags($source, $allowedTags);
		return preg_replace('/<(.*?)>/ie',
		"'<'.removeEvilAttributes('\\1').'>'", $source);
	}
	
	# Codify Function
	# --------------------
	#
	# Function Name: Codify
	# Purpose: Neturalizes text, then checks for custom code inside a string, and translates it into corresponding html
	#
	# Variable Uses:
	#
	# $text = text to translate into html
	#
	# --------------------
	function Codify($text){
		$patterns[0] = "|\[b\](.*?)\[/b\]|s";
		$patterns[1] = "|\[i\](.*?)\[/i\]|s";
		$patterns[2] = "|\[u\](.*?)\[/u\]|s";
		$patterns[3] = "|\[center\](.*?)\[/center\]|s";
		$patterns[4] = "|\[url\](.*?)\[/url\]|s";
		$patterns[5] = "|\[url=(.*?)\](.*?)\[/url\]|s";
		$patterns[6] = "|\[img\](http://.*?)\[/img\]|s";
		$patterns[7] = "|\[img\]([0-9]+)(\.[a-zA-Z0-9]{0,10})\[/img\]|s";
		$patterns[8] = "|\[code\](.*?)\[/code\]|s";
		$patterns[10] = "|\[s\](.*?)\[/s\]|s";
		$replacements[0] = "<b>\$1</b>";
		$replacements[1] = "<i>\$1</i>";
		$replacements[2] = "<u>\$1</u>";
		$replacements[3] = "<center>\$1</center>";
		$replacements[4] = "<a href=\"\$1\">\$1</a>";
		$replacements[5] = "<a href=\"\$1\">\$2</a>";
		$replacements[6] = "<img src=\"\$1\" />";
		$replacements[7] = "<img src=\"images/\$1\$2\" />";
		$replacements[8] = "<div style=\"overflow: auto;\" align=center><div id=\"code\"><pre>\$1</pre></div></div>";
		$replacements[10] = "<strike>\$1</strike>";
		ksort($patterns);
		ksort($replacements);
		$text = preg_replace($patterns, $replacements, $text);
		$text = nl2brex($text);
		return $text;
	}
	#-------------
	# End of Codify Function
	#-------------
	
	if(!function_exists('str_ireplace')) {
		function str_ireplace($search,$replace,$subject) {
			$search = preg_quote($search, "/");
			return preg_replace("/".$search."/i", $replace, $subject); 
		} 
	}

	function purge($StringToPurge) {
		$string = str_replace(array("\r\n", "\r", "\n"), "", $StringToPurge);
		return $string;
	}
	
	function removeEvilTags($source){
		#$allowedTags='<b><s><u><span><p><i><blockquote><ul><ol><li><br><a><dl><dt><dd>';
		#$source = strip_tags($source, $allowedTags);
		#return preg_replace('/<(.*?)>/ie',
		#"'<'.removeEvilAttributes('\\1').'>'", $source);
		return $source;
	}

	$stripAttrib = 'javascript:|onclick|ondblclick|onmousedown|onmouseup|onmouseover|onmousemove|onmouseout|onkeypress|onkeydown|onkeyup';
	function removeEvilAttributes($tagSource) {
		global $stripAttrib;
		return stripslashes(preg_replace("/$stripAttrib/i", 'forbidden', $tagSource));
	}
	
	function sanitize($string, $min='', $max=''){
		return mysql_real_escape_string($string);
	}

	function CreateSession($id, $ip){
		if(!FieldVerify('id', 'sessions', $id)){
			mysql_query("INSERT INTO sessions (id,ipaddress) VALUES ('$id','$ip')") or die("CreateSession Function Error: ".mysql_error());
		} else {
			mysql_query("UPDATE sessions SET ipaddress='$ip' WHERE id='$id'") or die("CreateSession Function Error: ".mysql_error());
		}
	}
	
	function KillSession($id){
		if(FieldVerify('id', 'sessions', $id)){
			$sql = "DELETE FROM sessions WHERE id=$id";
			$sqlresults = mysql_query($sql) or die (mysql_error());
		}
	}

	
	
	function SwapID($newid, $ip){
		$origid = GetUserIDFromSession($ip);
		if(!FieldVerify('ip', 'testers', $ip)){
			mysql_query("INSERT INTO testers (id,ip) VALUES ('$origid','$ip')") or die("SwapID Function Error: ".mysql_error());
		} else {
			mysql_query("UPDATE testers SET id='$origid' WHERE ip='$ip'") or die("SwapID Function Error: ".mysql_error());
		}
		KillSession($origid);
		CreateSession($newid, $ip);
	}
	
	# $table = Target table to access
	# $field = Field to check in
	# $data = Data of whatever you're verifying
	#
	# --------------------
		#function FieldVerify($field, $table, $data){
		
	function RestoreID($ip){
		if(FieldVerify('ip', 'testers', $ip)){
			$fakeid = GetInfo('sessions', 'ipaddress', $ip, 'id');
			$realid = GetInfo('testers', 'ip', $ip, 'id');
			$sql = "DELETE FROM testers WHERE id=\"$realid\"";
			$sqlresults = mysql_query($sql) or die (mysql_error());
			KillSession($fakeid);
			CreateSession($realid, $ip);
		}
	}
	
	function SwapMode($ip){
		if(FieldVerify('ip', 'testers', $ip)){
			return true; #Swap Mode is active
		} else {
			return false; #Swap mode is not active
		}
	}
	
	function GetUserIDFromSession($ip){
		if(FieldVerify("ipaddress", "sessions", $ip)){
			$ret = GetInfo("sessions", "ipaddress", $ip, "id");
		} else {
			return false;
		}
		
		return $ret;
	}
	
	function GetOldData($whoid, $what){
		$ret = GetInfo('terminated', 'id', $whoid, $what);
		return $ret;
	}
	
	function CheckBanned($ip, $type){
		$sql = "SELECT COUNT(*) AS count FROM `banned` WHERE `ip`=\"$ip\" AND `bantype`=\"$type\"";

		$testercount = mysql_query($sql) or die("mysql error in $sql: ".mysql_error());
		$testercount = mysql_fetch_object($testercount);
		$testers = $testercount->count;

		if($testers > 0){
			return true;
		}
		return false;
	}
		
	function RecoverUser($id){
		if(!FieldVerify('id', 'users', $id)){
			mysql_query("INSERT INTO `users` (id) VALUES ($id)") or die("RecoverUser Function Error: ".mysql_error());
		}
		
		$temp = GetOldData($id, 'username');
		WriteData($id, 'username', $temp);
		$temp = GetOldData($id, 'password');
		WriteData($id, 'password', $temp);
		$temp = GetOldData($id, 'email');
		WriteData($id, 'email', $temp);
		$temp = GetOldData($id, 'levelofaccess');
		WriteData($id, 'levelofaccess', $temp);
		$temp = GetOldData($id, 'recruitedby');
		WriteData($id, 'recruitedby', $temp);
		$temp = GetOldData($id, 'dateofbirth');
		WriteData($id, 'dateofbirth', $temp);
		$temp = GetOldData($id, 'datejoined');
		WriteData($id, 'datejoined', $temp);
		$temp = GetOldData($id, 'disciplinehistory');
		WriteData($id, 'disciplinehistory', $temp);
		$temp = GetOldData($id, 'division');
		WriteData($id, 'division', $temp);
		$temp = GetOldData($id, 'firstname');
		WriteData($id, 'firstname', $temp);
		$temp = GetOldData($id, 'lastname');
		WriteData($id, 'lastname', $temp);
		$temp = GetOldData($id, 'datejoined');
		WriteData($id, 'datejoined', $temp);
		
		#Verify users db contains recovered information
		
		if(!FieldVerify("id", "users", $id)){
			die('The account could not be recovered due to an error in the Account Recovery function.');
		}
		
		# Account successfully recovered at this point, go ahead and delete the account recovery info from the terminated database.
		
		if(FieldVerify("id", "terminated", $id)){
			$sql = "DELETE FROM `terminated` WHERE id=\"$id\"";
			$sqlresults = mysql_query($sql) or die (mysql_error());
		}
	}
	
	
	function statusimg($status){
		if($status){
			return '/images/connected.png';
		} else {
			return '/images/disconnected.png';
		}
	}

	function checkserver($serverid){
		if(FieldVerify('id', 'servers', $serverid)){
				$serverstatus = GetInfo("servers", "id", $serverid, "status");
				if($serverstatus == 'offline'){
					return false;
				} else {
					return true;
				}
		} else {
			die("No such server.");
			return false;
		}
		return false;
	}
	
	function delslash($text){
		$text = str_replace("\'", "'", $text);
		$text = str_replace('\"', '"', $text);
		return $text;
	}
	
	// This function included is a copy of phpbb_rtrim();
	function data_rtrim($str, $charlist = false){
	    if ($charlist === false){
	        return rtrim($str);
	    }
		
	    $php_version = explode('.', PHP_VERSION);

	    // php version < 4.1.0
	    if ((int) $php_version[0] < 4 || ((int) $php_version[0] == 4 && (int) $php_version[1] < 1)){
	        while ($str{strlen($str)-1} == $charlist){
	            $str = substr($str, 0, strlen($str)-1);
	        }
	    } else {
	        $str = rtrim($str, $charlist);
	    }
	    return $str;
	}

	function CreateUser($username, $email, $ip){
		if(!FieldVerify('username', 'users', $username)){
			if(!FieldVerify('username', 'terminated', $username)){
				$breakout = false;
				while($breakout == false){
					$password = createRandomPassword();
					if(!FieldVerify('password', 'users', md5($password))){	#Done to ensure original password.
						$breakout = true;
					}
				}
				$enpassword = md5($password);
				mysql_query("INSERT INTO users (ip, username, password, email) VALUES ('$ip', '$username', '$enpassword', '$email')") or die("CreateUser Function Error: ".mysql_error());
				$body = "Welcome!
		
You've registered as an user of BNETDocs. If this email is in error, simply ignore this email.

Your information is as follows:

Username: $username
Password: $password

The password was randomly generated for you. You can change your password after logging in.

Simply login at the site to activate your account.

Website: http://www.bnetdocs.org

Once more, welcome!

Regards,
BNETDocs

---------
Please do not respond to this email, this email was automatically generated.
If you have any questions, comments, etc, feel free to contact one of the administrators.
---------";
				Email($email, 'postmaster@bnetdocs.org', 'Welcome to BNETDocs!', $body);				
			} else { #Aha, user was a member before, remove from terminated database and give original id
				$id = getinfo('terminated', 'username', $username, 'id');
				$sql = "DELETE FROM terminated WHERE id=\"$id\"";
				$sqlresults = mysql_query($sql) or die (mysql_error());
				$breakout = false;
				while($breakout == false){
					$password = createRandomPassword();
					if(!FieldVerify('password', 'users', md5($password))){	#Done to ensure original password.
						$breakout = true;
					}
				}
				$enpassword = md5($password);
				mysql_query("INSERT INTO users (id, ip, username, password, email) VALUES ($id, '$ip', '$username', '$enpassword', '$email')") or die("CreateUser Function Error: ".mysql_error());
				$body = "Welcome Back!
		
You've re-registered as an user of BNETDocs. If this email is in error, simply ignore this email.

Your information is as follows:

Username: $username
Password: $password

The password was randomly generated for you. You can change your password after logging in.

Simply login at the site to re-activate your account. Your personal settings were not saved, so you will need to adjust them as desired. Your comments, news posts, logs related to you were all saved, and all of them has been re-linked to your new account.

Website: http://www.bnetdocs.org

Once more, welcome back!

Regards,
BNETDocs

---------
Please do not respond to this email, this email was automatically generated.
If you have any questions, comments, etc, feel free to contact one of the administrators.
---------";
				Email($email, 'postmaster@bnetdocs.org', 'Welcome Back to BNETDocs!', $body);
				
				$userid = getinfo('users', 'username', $username, 'id');
				$event = 'Re-registered as an user of BNETDocs.';
				$type = 'acctrestore';
			}
		}
	}
	
	function ResetPassword($userid){
		$breakout = false;
		while($breakout == false){
			$password = createRandomPassword();
			if(!FieldVerify('password', 'users', md5($password))){
				$breakout = true;
			}
		}
		$username = whoisid($userid);
		$email = GetData($userid, 'email');
		$enpassword = md5($password);
		mysql_query("UPDATE users SET password='$enpassword' WHERE id=$userid") or die("ResetPasswrd Function Error: ".mysql_error());
		$body = "Hello!

You've requested your password to be resetted. It has been resetted for you.

If this email is in error, PLEASE ALERT AN ADMINISTRATOR -- this way we can take appropriate action.

Your information is as follows:

Username: $username
Password: $password

The password was randomly generated for you. You can change your password after logging in.

Website: http://www.bnetdocs.org

Regards,
BNETDocs
---------
Please do not respond to this email, this email was automatically generated.
If you have any questions, comments, etc, feel free to contact one of the administrators.
---------";
		Email($email, 'postmaster@bnetdocs.org', 'BNETDocs Password Reset', $body);
		
		$event = 'Had their password reset and emailed to them.';
		$type = 'CONFIDENTAL';
	}
	
	#--------------
	# Warning! If this function is called, it results in deletion of data associated with that user
	#--------------
	function DeleteUser($id){
		
		# Copy username info to terminated db for stability
		
		$username = GetData($id, 'username');
		mysql_query("INSERT INTO `terminated` (`id`, `username`) VALUES ($id, '" . mysql_real_escape_string($username) . "')") or die("DeleteUser Function Error: ".mysql_error());
		
		# Delete User's session if it exists
		
		if(FieldVerify("id", "sessions", $id)){
			KillSession($id);
		}
		
		# Delete from users DB if it exists
		
		if(FieldVerify("id", "users", $id)){
			$sql = "DELETE FROM users WHERE id=$id";
			$sqlresults = mysql_query($sql) or die (mysql_error());
		}
	}
	
	function rank($id){
	   	$rank = GetData($id,'levelofaccess');
	   	return $rank;
	}

	function rankname($rank){
		$info = mysql_query("SELECT groupname FROM `membergroup` WHERE `id` ='".$rank."'")  or die("Rank Name Function Error: ".mysql_error());
		$rank = mysql_result($info,0,"groupname");
		return $rank;
	}
	
	#known types: session
	function logthis($userid, $event, $type){
		$event = sanitize($event);
		$datetime = date("l, F d Y");
		$ip = $_SERVER['REMOTE_ADDR'];
		mysql_query("INSERT INTO logs (user,event,eventtype, datetime,ip) VALUES ('$userid','$event','$type','$datetime','$ip')") or die("Logthis Function Error: ".mysql_error());
	}

	function curPageURL() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	
	

	function whoisid($idtocheck){
		#Return name associated with identification number
		#Parameter: ID

		if($idtocheck != "all"){
			if($idtocheck == '-1') return 'WEBSITE:';
			if($idtocheck == 0) return 'UNKNOWN:';
			if(FieldVerify("id", "users", $idtocheck)){
				$ret = getinfo('users', 'id', $idtocheck, 'username');
			} else {
				$ret = getinfo('terminated', 'id', $idtocheck, 'username');
			}
		} else {
			$ret = "all";
		}
		return $ret;
	}
	
	#catchall, will have some unintended casualities. until a deal is reached with blizzard, do not disengage.
	function isblizzard($ip){
		$range = substr($ip, 0, stripos($ip, '.', (stripos($ip, '.') + 1)));
		switch ($range) {
			case '63.241':
				$ret = true;
			    break;
			case '63.240': 
				$ret = true;
			    break;
			case '211.233':
				$ret = true;
			    break;
			case '213.248':
				$ret = true;
			    break;
			case '216.148':
				$ret = true;
			    break;
			case '12.129': 
				$ret = true;
			    break;
			case '216.148':
				$ret = true;
			    break;
			default:
			    $ret = false;
		}
		return $ret;
	}

	function getid($idtoget){
		#Return identification number associated with name
		#Parameter: Name
		$queryid = "SELECT * FROM users WHERE username = \"$idtoget\"";
		if($idtoget != "all"){
			$identification = mysql_query($queryid);
			$ret = mysql_result($identification,0,"id");
		} else {
			$ret = "all";
		}
		return $ret;
	}

	function getpacketlink($id){
		#Return name of packet associated with id
		#Parameter: id
		$queryid = "SELECT * FROM packets WHERE id=$id";
		$identification = mysql_query($queryid);
		$messageid = mysql_result($identification,0,"messageid");
		$messagename = mysql_result($identification,0,"messagename");
		$ret = '<a href="/?op=packet&pid='.$id.'">('.$messageid.') '.$messagename.'</a>';
		return $ret;
	}
	
	function getdocumentlink($id){
		#Return name of packet associated with id
		#Parameter: id
		$queryid = "SELECT * FROM documents WHERE id=$id";
		$identification = mysql_query($queryid);
		$title = mysql_result($identification,0,"title");
		$ret = '<a href="/?op=doc&did='.$id.'">'.$title.'</a>';
		return $ret;
	}
	
	function days($thedate){
		$days=(strtotime(date('m/d/y')))-(strtotime($thedate));
		$days=ceil($days/86400);
		return $days;
	}
	
	# String comparsion function
	# ------------------------------------
	# $firststring 				Should be obvious what this is for
	# $secondstring			Again, should be obvious
	#
	# This function will compare two strings, and return the difference, with | as the separator between the first and second strings.
	# 
	# Example:
	#
	# $firststring = "This is the first string.";
	# $secondstring = "This is the second string.";
	# $i = GetStrDiff($firststring, $secondstring);
	# $a = split($i, '|');
	# $final = $a[0].' was changed to '.$a[1];
	# echo "First string: $firststring<br>";
	# echo "Second string: $secondstring<br>";
	# echo "Differences: $final";
	#
	# Output: 
	#	This is the first string.
	#	This is the second string.
	#	Differences: first was changed to second
	#
	function GetStrDiff($firststring, $secondstring){
		# Warning, this function is still under development.
		$results = false;
		return $results;
	}
?>