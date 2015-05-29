<?
	
	# Include direct script access authorization variable and other variables
	#-------------

		$auth = "true";
		$ip = $_SERVER['REMOTE_ADDR'];
		$surpress = $_REQUEST['surpress'];
		die('This site has been frozen for maintenance.');
		
	# Set up environment
	#-------------

	# Security
	#-------------
	
		# Prevent spam POST submissions from external domains
		
		if($_POST){
			if(!strpos($_SERVER['HTTP_REFERER'],'bnetdocs.org')){
				exit();
			}
		}

		# Stripslashes as shown on http://php.net/get_magic_quotes_gpc
		# Used to prevent remote code injection via post, get, cookie. Also contributes to prevent remote code injection via SQL since input is cleaned prior to inserting to SQL DB.
		if (get_magic_quotes_gpc()) {	
		   function stripslashes_deep($value) {
		       $value = is_array($value) ?
		                   array_map('stripslashes_deep', $value) :
		                   stripslashes($value);

		       return $value;
		   }

		   $_POST = array_map('stripslashes_deep', $_POST);			# Clean POSTed data
		   $_GET = array_map('stripslashes_deep', $_GET);					# Clean browser variables
		   $_COOKIE = array_map('stripslashes_deep', $_COOKIE);	# Clean cookie variables
		}
		
	# Include essential files
	#-------------

		require 'db.php';
		require 'func.php';
		
	# Block out unwelcome visitors
	#-------------

		$userid = GetUserIDFromSession($ip);
		if(isblizzard($ip)){
			#IP identified as potentially belonging to blizzard. Note it in log.
			logthis(-1, 'Blizzard accessed website. Access attempt allowed.', 'vip');
		}

	# SECURITY: Prevent remote inclusion injection
	#-------------
		
		$op = $_REQUEST['op'];

		# Prevent infinite loop bug, bug found by Jailout2000
		if($op == 'index') $op = '';
	
		$legal_inc=getdir('.');

		$i=0;
		$allowfile='false';
		while($i <= count($legal_inc)){
			if($op.'.php'==$legal_inc[$i]) $allowfile='true';
			$i++;
		}
		
		if(!$op || trim($op) == '') $allowfile='true';
		
		if($allowfile=='false') exit();


	# Surpress code is in place to prevent a HEADER ALREADY SENT error.
	#-------------

		
		if($surpress != "true"){
			if($op == "") $op = "news";
			include "bdif/header.dm";
			if($userid){
				# Update member last login
				mysql_query("UPDATE sessions SET dtstamp=NOW() WHERE id='$userid'") or die("UpdateSession Function Error: ".mysql_error());
				$msg = GetData($userid, 'msg');
			} else {
				$msg = $_REQUEST['msg'];
				if($msg == '1'){
					$msg = "You have been logged out.";
				} elseif($msg == ''){
					#disregard
				} else {
					$msg = "Nice try buddy!";
				}
			}
			
			if($msg){
				$msg = '<center><font color=lime><b>'.$msg.'</b></font></center>';
				WriteData($userid, 'msg', '');
				echo "<br>";
				DisplayDialogBox($msg);
				echo "<br>";
			}
			$op=$op.".php";
			include $op;
			include "bdif/footer.dm";
		} else {
			if($op != ""){
				$op=$op.".php";
				include $op;
			} else {
				echo 'op empty.';
			}
		}
		echo 'and here!';
		mysql_close();
		exit();

	#-------------
	# End of main script.
	#-------------
?>