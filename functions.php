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
		function DisplayDialogBox($title='', $text='', $file=false, $footer='', $mini=false, $iconpath=false, $misc=''){
			include 'if/dialogbox.dm';
		}
	#-------------
	# End of DisplayDialogBox Function
	#-------------
	
	function GetUserIDFromSession($ip){
		if(FieldVerify("ipaddress", "sessions", $ip)){
			$ret = GetInfo("sessions", "ipaddress", $ip, "id");
		} else {
			return false;
		}
		
		return $ret;
	}
	
	function BlockHack(){
		die('<center><br><br><b><font color=red>Unauthorized access blocked. Nice try, buddy.</font></b><br><br></center>');
	}
	
	# Redirect Function
	# --------------------
	#
	# Function Name: Redirect
	# Purpose: Redirects user to target page, without using php header redirect (avoids php 'header already sent' error), and doesn't use javascript (in case of users having js disabled).
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
	
	function VerifyLogin($username, $password){
		$password = md5($password);
		$sql = "SELECT COUNT(*) as count FROM `users` WHERE `username`=\"$username\" AND `password`=\"$password\"";	# Construct SQL Query
		$count = mysql_query($sql) or die("mysql error in $sql: ".mysql_error());
		$count = mysql_fetch_object($count);
		$count = $count->count;
		
		if($count > 0){
			return true;
		}
		return false;
	}
	
	function javascriptok($userid){
		if(GetOption($userid, 'jsenabled') == 1){
			return true;
		} else {
			return false;
		}
	}
	
	function HelpLink($linkto, $tooltipcontent) {
		# Provides easy way to generate a tooltip link
		?><font size=1 style="z-index: 50;"> [ <a class="info" href="/?op=help&topic=<?=$linkto;?>">?<span><?=$tooltipcontent;?></span></a> ]</font><?
	}
	
	function ShortenText($text, $chars) {
		# Change to the number of characters you want to display

		$text = $text." ";
		$text = substr($text,0,$chars);
		$text = substr($text,0,strrpos($text,' '));
		$text = $text."...";
		return $text;
    }
	
	function logthis($userid, $event, $type){
		$datetime = date("l, F d Y");
		$ip = $_SERVER['REMOTE_ADDR'];
		mysql_query("INSERT INTO logs (user,event,eventtype, datetime,ip) VALUES ('$userid','$event','$type','$datetime','$ip')") or die("Logthis Function Error: ".mysql_error());
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
	
	function DeleteUser($id){
		
		# Copy username info to terminated db for stability
		
		$username = GetData($id, 'username');
		mysql_query("INSERT INTO `terminated` (`id`, `username`) VALUES ($id, '$username')") or die("DeleteUser Function Error: ".mysql_error());
		
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
	
	function KillSession($id){
		if(FieldVerify('id', 'sessions', $id)){
			$sql = "DELETE FROM sessions WHERE id=\"$id\"";
			$sqlresults = mysql_query($sql) or die (mysql_error());
		}
		KillCookie();
	}
	
	function FindIdentityHash($identityhash){
		if(FieldVerify('identityhash', 'sessions', $identityhash)){
			return true;
		}
		return false;
	}
	
	function GetIDFromIdentityHash($identityhash){
		$ret = GetInfo('sessions', 'identityhash', $identityhash, 'id');	# Get user id of the matching hash
		return $ret;
	}
	
	function UpdateIdentityHash($userid){
	
	}
	
	function GetIDFromUsername($username){
		$ret = GetInfo('users', 'username', $username, 'id');	# Get user id from the users database using username as reference
		return $ret;
	}
	
	function TimeLimited($userid){
		$ret = GetInfo('sessions', 'id', $userid, 'notimelimit');	# Get NoTimeLimit value
		if($ret == 0){
			return true;
		}
		return false;
	}
	
	function GetSettingValue($setting){
		$ret = GetInfo('settings', 'option', $setting, 'value');	# Get Public Site Setting value (Applies globally to all visitors)
		return $ret;
	}
	
	function SessionExists($userid){
		return FieldVerify('id', 'sessions', $userid);
	}
	
	function ResumeSession($userid, $notimelimit, $ip){
		if(!$notimelimit) {	
			$notimelimit = 0;
			KillCookie();
		} else {
			$notimelimit = 1;
			
			# Create cookie for fallback in case user's ip address changes
		
			$username = whoisid($userid);
			$randomizedstring = RandomizedString();
			$time=mktime(0,0,0,date("n",time()),date("j",time()),date("Y",time())+1,0);		# 1 year from now
			$identityhash = md5($userid.$username.$randomizedstring.$time);
			
			setcookie('identityhash', $identityhash, $time);
		}
		
		# Update IP-Based session
		
		if(SessionExists($userid) ){
			# Update without time limit, also update hash for security
			mysql_query("UPDATE sessions SET ipaddress=\"$ip\", notimelimit='$notimelimit', identityhash='$identityhash' WHERE id='$userid'") or die("ResumeSession Function->SQL Update Error: ".mysql_error());
		} else {
			 # Insert new session without time limit, also update hash for security
			mysql_query("INSERT INTO sessions (id,ipaddress,notimelimit, identityhash) VALUES ('$userid','$ip','$notimelimit', '$identityhash')") or die("ResumeSession Function->SQL Insert Error: ".mysql_error()."GetUserIDFromSession($ip) = ".GetUserIDFromSession($ip));
		}
		
		# If cookie doesn't exist, no need to create/update cookie since IP-based session will suffice. Cookie will be marked by index.php for termination.
		
	}
	
	function RandomizedString($numofchars=7) {
	    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
	    srand((double)microtime()*1000000);
	    $i = 0;
	    $newstring = '' ;
	    while ($i <= $numofchars) {
	        $num = rand() % 33;
	        $tmp = substr($chars, $num, 1);
	        $newstring = $newstring . $tmp;
	        $i++;
	    }
	    return $newstring;
	}
	
	function KillCookie() {
		# Since we can't delete the cookie, we basically erase the identity hash from the cookie and make the cookie expire.
		setcookie('identityhash', '', time()-3600);
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
	
	function CheckBanned($ip, $type){
		$sql = 'SELECT COUNT(*) AS count FROM banned WHERE ip="'.$ip.'" AND bantype="'.$type.'"';
		$sqlquery = mysql_query($sql) or die("mysql error in $sql: ".mysql_error());
		$sqlmatchcount = mysql_fetch_object($sqlquery);
		$matches = $sqlmatchcount->count;
		if($matches > 0){
			return true;
		}
		return false;
	}
	
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
			$ret = GetInfo('users', 'id', $whoid, $what);
			return $ret;
		}
	#-------------
	# End of GetData Function
	#-------------
	
	# GetOption Function
	# --------------------
	#
	# Function Name: GetOption
	# Purpose: Makes it easier to obtain a member's settings.
	# Variable Uses:
	#
	# $whoid = target member for setting request
	# $what = which field data to pull up
	#
	# --------------------
		function GetOption($whoid, $what){
			$ret = GetInfo('options', 'id', $whoid, $what);
			return $ret;
		}
	#-------------
	# End of GetOption Function
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
	
	# WriteSettings Function
	# --------------------
	#
	# Function Name: WriteSettings
	# Purpose: Makes it easier to update a member's settings.
	# Variable Uses:
	#
	# $whoid = target member for setting update
	# $what = which setting to modify (field *MUST* exist)
	#$data = the data to write
	#
	# --------------------
		function WriteSettings($whoid, $what, $data){
			$ret = true;
			$sql = "UPDATE options SET `$what`=\"$data\" WHERE id=$whoid"; 
			$sqlresults = mysql_query($sql) or $ret = false;
			return $ret;
		}
	#-------------
	# End of WriteSettings Function
	#-------------
	
	function rank($id){
	   	$rank = GetData($id,'levelofaccess');
	   	return $rank;
	}

	function rankname($rank){
		if($rank == 0 || !$rank || $rank == '') return '<font class="urgent">Error! Clearance is not set!</font>';
		$info = mysql_query("SELECT groupname FROM `membergroup` WHERE `id` ='".$rank."'")  or die("Rank Name Function Error: ".mysql_error());
		$rank = mysql_result($info,0,"groupname");
		return $rank;
	}
	
	function getrankimage($rank){
		if($rank == 0 || !$rank || $rank == '') return false;
		$info = mysql_query("SELECT `gfx` FROM `membergroup` WHERE `id` ='".$rank."'")  or die("GetRankImage Function Error: ".mysql_error());
		$rank = mysql_result($info,0,"gfx");
		return $rank;
	}
	
	function BBCode($Text)
       {
         // Replace any html brackets with HTML Entities to prevent executing HTML or script
            // Don't use strip_tags here because it breaks [url] search by replacing & with amp
            $Text = htmlspecialchars($Text);

            // Convert new line chars to html <br /> tags
            $Text = nl2brex($Text);

            // Perform URL Search
            $Text = preg_replace("|\[url\](.*?)\[\/url\]|s", '<a href="$1" target="_blank">$1</a>', $Text);
            $Text = preg_replace("|\[url\=(.*?)\](.*?)\[/url\]|s", '<a href="$1" target="_blank">$2</a>', $Text);
			
            // Check for bold text
            $Text = preg_replace("(\[b\](.+?)\[\/b])is",'<span class="bold">$1</span>',$Text);
			
			// Check for centered text
            $Text = preg_replace("(\[center\](.+?)\[\/center])is",'<center>$1</center>',$Text);

            // Check for Italics text
            $Text = preg_replace("(\[i\](.+?)\[\/i\])is",'<span class="italics">$1</span>',$Text);

            // Check for Underline text
            $Text = preg_replace("(\[u\](.+?)\[\/u\])is",'<span class="underline">$1</span>',$Text);

            // Check for strike-through text
            $Text = preg_replace("(\[s\](.+?)\[\/s\])is",'<span class="strikethrough">$1</span>',$Text);

            // Check for over-line text
            $Text = preg_replace("(\[o\](.+?)\[\/o\])is",'<span class="overline">$1</span>',$Text);

            // Check for colored text
            $Text = preg_replace("(\[color=(.+?)\](.+?)\[\/color\])is","<span style=\"color: $1\">$2</span>",$Text);

            // Check for sized text
            $Text = preg_replace("(\[size=(.+?)\](.+?)\[\/size\])is","<span style=\"font-size: $1px\">$2</span>",$Text);

            // Check for list text
            $Text = preg_replace("/\[list\](.+?)\[\/list\]/is", '<ul class="listbullet">$1</ul>' ,$Text);
            $Text = preg_replace("/\[list=1\](.+?)\[\/list\]/is", '<ul class="listdecimal">$1</ul>' ,$Text);
            $Text = preg_replace("/\[list=i\](.+?)\[\/list\]/s", '<ul class="listlowerroman">$1</ul>' ,$Text);
            $Text = preg_replace("/\[list=I\](.+?)\[\/list\]/s", '<ul class="listupperroman">$1</ul>' ,$Text);
            $Text = preg_replace("/\[list=a\](.+?)\[\/list\]/s", '<ul class="listloweralpha">$1</ul>' ,$Text);
            $Text = preg_replace("/\[list=A\](.+?)\[\/list\]/s", '<ul class="listupperalpha">$1</ul>' ,$Text);
            $Text = str_replace("[*]", "<li>", $Text);

            // Check for font change text
            $Text = preg_replace("(\[font=(.+?)\](.+?)\[\/font\])","<span style=\"font-family: $1;\">$2</span>",$Text);

            // Declare the format for [code] layout
            $CodeLayout = '<table class="codetable" width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="quotecodeheader"></td>
                                </tr>
                                <tr>
                                    <td class="codebody">$1</td>
                                </tr>
                           </table>';
            // Check for [code] text
            $Text = preg_replace("/\[code\](.+?)\[\/code\]/is","$CodeLayout", $Text);

            // Declare the format for [quote] layout
            $QuoteLayout = '<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="quotecodeheader"></td>
                                </tr>
                                <tr>
                                    <td class="quotebody">$1</td>
                                </tr>
                           </table>';
                     
            // Check for [code] text
            $Text = preg_replace("/\[quote\](.+?)\[\/quote\]/is","$QuoteLayout", $Text);
         
            // Images
            // [img]pathtoimage[/img]
            $Text = preg_replace("/\[img\](.+?)\[\/img\]/", '<img src="$1">', $Text);
         
            // [img=widthxheight]image source[/img]
            $Text = preg_replace("/\[img\=([0-9]*)x([0-9]*)\](.+?)\[\/img\]/", '<img src="$3" height="$2" width="$1">', $Text);
         
           return $Text;
      }
	  
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
	  
	function br2nl($text) {
	    $text = preg_replace('/<br\\\\s*?\\/??>/i', "\\n", $text);
	    return str_replace("<br />","\n",$text);
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
?>